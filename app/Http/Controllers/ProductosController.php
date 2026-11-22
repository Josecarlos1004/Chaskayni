<?php

namespace App\Http\Controllers;

use App\Models\Comunidad;
use App\Models\Producto;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
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
        unset($datos['imagen'], $datos['quitar_imagen']);

        if ($request->hasFile('imagen')) {
            $rutaImagen = $request->file('imagen')->store('productos', 'public');
            if (! $rutaImagen) {
                throw ValidationException::withMessages(['imagen' => 'No se pudo guardar la imagen. Inténtalo nuevamente.']);
            }
            $datos['imagen_url'] = $rutaImagen;
        }
        $datos['usuario_id'] = Auth::id();
        $datos['comunidad_id'] = $datos['comunidad_id']
            ?? (Auth::user()->comunidad?->activo ? Auth::user()->comunidad_id : null);
        $datos['estado'] = 'disponible';

        Producto::create($datos);

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
        unset($datos['imagen'], $datos['quitar_imagen']);

        $imagenAnterior = $producto->imagen_url;
        if ($request->hasFile('imagen')) {
            $rutaImagen = $request->file('imagen')->store('productos', 'public');
            if (! $rutaImagen) {
                throw ValidationException::withMessages(['imagen' => 'No se pudo guardar la imagen. Inténtalo nuevamente.']);
            }
            $datos['imagen_url'] = $rutaImagen;
        } elseif ($request->boolean('quitar_imagen')) {
            $datos['imagen_url'] = null;
        } elseif (! $request->filled('imagen_url')) {
            // Un campo URL vacío conserva la foto actual al editar.
            unset($datos['imagen_url']);
        }

        $producto->update($datos);

        if ($imagenAnterior !== $producto->imagen_url) {
            $this->eliminarImagenAlmacenada($imagenAnterior);
        }

        return redirect()->route('mercado.indice')->with('success', 'La publicación se actualizó.');
    }

    public function destroy(Producto $producto): RedirectResponse
    {
        $this->verificarPropietario($producto);

        if (Auth::user()->esAdministrador()) {
            if ($producto->detallesPedido()->exists()) {
                return back()->withErrors(['producto' => 'No se puede eliminar esta publicación porque forma parte de pedidos. Ocúltala para conservar el historial.']);
            }

            $imagen = $producto->imagen_url;
            $producto->delete();
            $this->eliminarImagenAlmacenada($imagen);

            return back()->with('success', 'Se eliminó la publicación.');
        }

        // Los productos con pedidos se ocultan para conservar el historial asociado.
        if ($producto->detallesPedido()->exists()) {
            $producto->update(['estado' => 'oculto']);
        } else {
            $imagen = $producto->imagen_url;
            $producto->delete();
            $this->eliminarImagenAlmacenada($imagen);
        }

        return redirect()->route('mercado.indice')->with('success', 'La publicación dejó de mostrarse en el mercado.');
    }

    private function verificarPropietario(Producto $producto): void
    {
        abort_unless(Auth::user()->esProductor(), 403);
        abort_unless($producto->usuario_id === Auth::id() || Auth::user()->esAdministrador(), 403);
    }

    /** Ofrece comunidades activas y conserva la asociación existente al editar. */
    private function comunidadesDisponibles(?Producto $producto = null)
    {
        $consulta = Comunidad::where('activo', true);
        if ($producto?->comunidad_id) {
            $consulta->orWhereKey($producto->comunidad_id);
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
            'cesta_salvada' => ['required', 'boolean'],
            'comunidad_id' => ['nullable', $this->reglaComunidad($request, $producto)],
        ]);
    }

    /** Elimina solo imágenes cargadas por esta aplicación, nunca URL externas. */
    private function eliminarImagenAlmacenada(?string $ruta): void
    {
        if ($ruta && str_starts_with($ruta, 'productos/')) {
            Storage::disk('public')->delete($ruta);
        }
    }
}
