<?php

namespace App\Http\Controllers;

use App\Models\Comunidad;
use App\Models\Producto;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ProductosController extends Controller
{
    private function verificarProductor(): void
    {
        abort_unless(Auth::user()->esProductor(), 403, 'Solo productores pueden administrar publicaciones.');
    }

    public function create(): View
    {
        $this->verificarProductor();

        return view('productos.formulario', [
            'producto' => new Producto,
            'comunidades' => Comunidad::orderBy('nombre')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->verificarProductor();
        $datos = $this->validarDatos($request);
        $datos['usuario_id'] = Auth::id();
        $datos['comunidad_id'] = $datos['comunidad_id'] ?? Auth::user()->comunidad_id;
        $datos['estado'] = 'disponible';

        Producto::create($datos);

        return redirect()->route('mercado.indice')->with('success', 'Publicaste un producto en el mercado.');
    }

    public function edit(Producto $producto): View
    {
        $this->verificarPropietario($producto);

        return view('productos.formulario', [
            'producto' => $producto,
            'comunidades' => Comunidad::orderBy('nombre')->get(),
        ]);
    }

    public function update(Request $request, Producto $producto): RedirectResponse
    {
        $this->verificarPropietario($producto);
        $producto->update($this->validarDatos($request));

        return redirect()->route('mercado.indice')->with('success', 'La publicación se actualizó.');
    }

    public function destroy(Producto $producto): RedirectResponse
    {
        $this->verificarPropietario($producto);

        // Si hay pedidos asociados, se oculta para conservar su historial.
        if ($producto->detallesPedido()->exists()) {
            $producto->update(['estado' => 'oculto']);
        } else {
            $producto->delete();
        }

        return redirect()->route('mercado.indice')->with('success', 'La publicación dejó de mostrarse en el mercado.');
    }

    private function verificarPropietario(Producto $producto): void
    {
        abort_unless(Auth::user()->esProductor(), 403);
        abort_unless($producto->usuario_id === Auth::id() || Auth::user()->esAdministrador(), 403);
    }

    private function validarDatos(Request $request): array
    {
        return $request->validate([
            'nombre' => ['required', 'string', 'max:140'],
            'categoria' => ['nullable', 'string', 'max:80'],
            'descripcion' => ['nullable', 'string', 'max:3000'],
            'cantidad' => ['required', 'numeric', 'gt:0', 'max:99999999'],
            'unidad' => ['required', 'in:kg,arroba,saco,unidad,litro'],
            'precio_unitario' => ['required', 'numeric', 'min:0.01', 'max:99999999'],
            'imagen_url' => ['nullable', 'url:http,https', 'max:2048'],
            'cesta_salvada' => ['required', 'boolean'],
            'comunidad_id' => ['nullable', 'exists:comunidades,id'],
        ]);
    }
}
