<?php

namespace App\Http\Controllers;

use App\Models\Comunidad;
use App\Models\Producto;
use App\Models\ProductoImagen;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Throwable;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ProductosController extends Controller
{
    /** Sirve las fotos del mercado aunque estén guardadas en un bucket privado. */
    public function verImagenPublica(Producto $producto): StreamedResponse
    {
        $rutaImagen = $producto->rutaImagenAlmacenada();
        abort_unless($rutaImagen && Storage::disk('public')->exists($rutaImagen), 404);

        return Storage::disk('public')->response(
            $rutaImagen,
            'producto-'.$producto->id,
            [
                'Cache-Control' => 'public, max-age=3600',
                'X-Content-Type-Options' => 'nosniff',
            ],
            'inline',
        );
    }

    /** Sirve las fotos adicionales del mercado, también cuando están en un bucket privado. */
    public function verImagenAdicional(ProductoImagen $imagen): StreamedResponse
    {
        abort_unless(Storage::disk('public')->exists($imagen->ruta), 404);

        return Storage::disk('public')->response(
            $imagen->ruta,
            'producto-'.$imagen->producto_id.'-'.$imagen->id,
            [
                'Cache-Control' => 'public, max-age=3600',
                'X-Content-Type-Options' => 'nosniff',
            ],
            'inline',
        );
    }

    private function verificarProductor(): void
    {
        abort_unless(Auth::user()->esProductor(), 403, 'Solo productores pueden administrar publicaciones.');
    }

    public function create(): View
    {
        $this->verificarProductor();

        return view('productos.formulario', [
            'producto' => new Producto,
            'comunidades' => $this->comunidadesDisponibles(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->verificarProductor();
        $datos = $this->validarDatos($request);
        unset($datos['imagen'], $datos['quitar_imagen'], $datos['imagenes'], $datos['quitar_imagenes']);

        $rutasNuevas = [];
        try {
            if ($request->hasFile('imagen')) {
                $rutaImagen = $request->file('imagen')->store('productos', 'public');
                if (! $rutaImagen) {
                    throw ValidationException::withMessages(['imagen' => 'No se pudo guardar la imagen. Inténtalo nuevamente.']);
                }
                $datos['imagen_url'] = $rutaImagen;
                $rutasNuevas[] = $rutaImagen;
            }
            $rutasAdicionales = $this->guardarArchivosAdicionales($request);
            $rutasNuevas = array_merge($rutasNuevas, $rutasAdicionales);

            $datos['usuario_id'] = Auth::id();
            $datos['comunidad_id'] = $datos['comunidad_id']
                ?? (Auth::user()->comunidad?->activo ? Auth::user()->comunidad_id : null);
            $datos['estado'] = 'disponible';

            DB::transaction(function () use ($datos, $rutasAdicionales): void {
                $producto = Producto::create($datos);
                $this->registrarImagenesAdicionales($producto, $rutasAdicionales);
            });
        } catch (Throwable $error) {
            // Si algo falla, no dejamos archivos huérfanos en el almacenamiento.
            foreach ($rutasNuevas as $ruta) {
                $this->eliminarImagenAlmacenada($ruta);
            }
            throw $error;
        }

        return redirect()->route('mercado.indice')->with('success', 'Publicaste un producto en el mercado.');
    }

    public function edit(Producto $producto): View
    {
        $this->verificarPropietario($producto);

        return view('productos.formulario', [
            'producto' => $producto,
            'comunidades' => $this->comunidadesDisponibles($producto),
        ]);
    }

    public function update(Request $request, Producto $producto): RedirectResponse
    {
        $this->verificarPropietario($producto);
        $datos = $this->validarDatos($request, $producto);
        unset($datos['imagen'], $datos['quitar_imagen'], $datos['imagenes'], $datos['quitar_imagenes']);

        // Solo se pueden quitar fotos adicionales que pertenecen a este producto.
        $imagenesAQuitar = $producto->imagenes()
            ->whereIn('id', array_map('intval', (array) $request->input('quitar_imagenes', [])))
            ->get();
        $this->verificarLimiteImagenes($request, $producto, $imagenesAQuitar->count());

        $imagenAnterior = $producto->imagen_url;
        $rutasNuevas = [];
        try {
            if ($request->hasFile('imagen')) {
                $rutaImagen = $request->file('imagen')->store('productos', 'public');
                if (! $rutaImagen) {
                    throw ValidationException::withMessages(['imagen' => 'No se pudo guardar la imagen. Inténtalo nuevamente.']);
                }
                $datos['imagen_url'] = $rutaImagen;
                $rutasNuevas[] = $rutaImagen;
            } elseif ($request->boolean('quitar_imagen')) {
                $datos['imagen_url'] = null;
            } elseif (! $request->filled('imagen_url')) {
                // Un campo URL vacío conserva la foto actual al editar.
                unset($datos['imagen_url']);
            }
            $rutasAdicionales = $this->guardarArchivosAdicionales($request);
            $rutasNuevas = array_merge($rutasNuevas, $rutasAdicionales);

            DB::transaction(function () use ($producto, $datos, $imagenesAQuitar, $rutasAdicionales): void {
                $producto->update($datos);
                ProductoImagen::whereIn('id', $imagenesAQuitar->pluck('id'))->delete();
                $this->registrarImagenesAdicionales($producto, $rutasAdicionales);
            });
        } catch (Throwable $error) {
            foreach ($rutasNuevas as $ruta) {
                $this->eliminarImagenAlmacenada($ruta);
            }
            throw $error;
        }

        if ($imagenAnterior !== $producto->imagen_url) {
            $this->eliminarImagenAlmacenada($imagenAnterior);
        }
        foreach ($imagenesAQuitar as $imagen) {
            $this->eliminarImagenAlmacenada($imagen->ruta);
        }

        return redirect()->route('mercado.indice')->with('success', 'La publicación se actualizó.');
    }

    public function actualizarVisibilidad(Producto $producto): RedirectResponse
    {
        $this->verificarPropietario($producto);

        if ($producto->estado === 'oculto') {
            $estado = (float) $producto->cantidad > 0 ? 'disponible' : 'agotado';
            $mensaje = 'La publicación volvió a mostrarse en el mercado.';
        } else {
            $estado = 'oculto';
            $mensaje = 'La publicación se ocultó y puedes volver a mostrarla desde tu panel.';
        }

        $producto->update(['estado' => $estado]);

        return redirect()->route('panel')->with('success', $mensaje);
    }

    public function destroy(Producto $producto): RedirectResponse
    {
        $this->verificarPropietario($producto);
        abort_unless(Auth::user()->esAdministrador(), 403);

        if ($producto->detallesPedido()->exists()) {
            return back()->withErrors(['producto' => 'No se puede eliminar esta publicación porque forma parte de pedidos. Ocúltala para conservar el historial.']);
        }

        $imagen = $producto->imagen_url;
        $rutasAdicionales = $producto->imagenes()->pluck('ruta');
        $producto->delete();
        $this->eliminarImagenAlmacenada($imagen);
        foreach ($rutasAdicionales as $ruta) {
            $this->eliminarImagenAlmacenada($ruta);
        }

        return back()->with('success', 'Se eliminó la publicación.');
    }

    private function verificarPropietario(Producto $producto): void
    {
        abort_unless(Auth::user()->esProductor(), 403);
        abort_unless((int) $producto->usuario_id === (int) Auth::id() || Auth::user()->esAdministrador(), 403);
    }

    /** Ofrece comunidades activas y conserva la asociación existente al editar. */
    private function comunidadesDisponibles(?Producto $producto = null)
    {
        $consulta = Comunidad::where('activo', true);
        if ($producto?->comunidad_id) {
            $consulta->orWhere('id', $producto->comunidad_id);
        }

        return $consulta->orderBy('nombre')->get();
    }

    private function reglaComunidad(Request $request, ?Producto $producto)
    {
        $idSeleccionado = $request->input('comunidad_id');
        if ($producto && $idSeleccionado && (string) $idSeleccionado === (string) $producto->comunidad_id) {
            return Rule::exists('comunidades', 'id');
        }

        return Rule::exists('comunidades', 'id')->where('activo', true);
    }

    private function validarDatos(Request $request, ?Producto $producto = null): array
    {
        return $request->validate([
            'nombre' => ['required', 'string', 'max:140'],
            'categoria' => ['nullable', 'string', 'max:80'],
            'descripcion' => ['nullable', 'string', 'max:3000'],
            'cantidad' => ['required', 'numeric', 'gt:0', 'max:99999999'],
            'unidad' => ['required', 'in:kg,arroba,saco,unidad,litro'],
            'precio_unitario' => ['required', 'numeric', 'min:0.01', 'max:99999999'],
            'imagen_url' => ['nullable', 'url:http,https', 'max:2048'],
            'imagen' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'quitar_imagen' => ['nullable', 'boolean'],
            'imagenes' => ['nullable', 'array', 'max:'.Producto::MAX_IMAGENES_ADICIONALES],
            'imagenes.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'quitar_imagenes' => ['nullable', 'array'],
            'quitar_imagenes.*' => ['integer'],
            'cesta_salvada' => ['required', 'boolean'],
            'comunidad_id' => ['nullable', $this->reglaComunidad($request, $producto)],
        ]);
    }

    /** Evita superar el máximo de fotos adicionales al sumar las nuevas a las que ya existen. */
    private function verificarLimiteImagenes(Request $request, Producto $producto, int $cantidadAQuitar): void
    {
        $nuevas = count($request->file('imagenes', []));
        $restantes = ProductoImagen::where('producto_id', $producto->id)->count() - $cantidadAQuitar;

        if ($restantes + $nuevas > Producto::MAX_IMAGENES_ADICIONALES) {
            throw ValidationException::withMessages([
                'imagenes' => 'Cada producto admite hasta '.Producto::MAX_IMAGENES_ADICIONALES.' fotos adicionales. Quita alguna antes de subir más.',
            ]);
        }
    }

    /** Guarda en el disco público las fotos adicionales y devuelve sus rutas. */
    private function guardarArchivosAdicionales(Request $request): array
    {
        $rutas = [];
        foreach ($request->file('imagenes', []) as $archivo) {
            $ruta = $archivo->store('productos', 'public');
            if (! $ruta) {
                foreach ($rutas as $rutaGuardada) {
                    $this->eliminarImagenAlmacenada($rutaGuardada);
                }
                throw ValidationException::withMessages(['imagenes' => 'No se pudo guardar una de las fotos. Inténtalo nuevamente.']);
            }
            $rutas[] = $ruta;
        }

        return $rutas;
    }

    /** Registra en la base de datos las fotos adicionales ya guardadas, a continuación de las existentes. */
    private function registrarImagenesAdicionales(Producto $producto, array $rutas): void
    {
        $orden = (int) ProductoImagen::where('producto_id', $producto->id)->max('orden');
        foreach ($rutas as $ruta) {
            $producto->imagenes()->create(['ruta' => $ruta, 'orden' => ++$orden]);
        }
    }

    /** Elimina solo imágenes cargadas por esta aplicación, nunca URL externas. */
    private function eliminarImagenAlmacenada(?string $ruta): void
    {
        if ($ruta && str_starts_with($ruta, 'productos/')) {
            Storage::disk('public')->delete($ruta);
        }
    }
}
