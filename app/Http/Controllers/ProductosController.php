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
            'comunidades' => Comunidad::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->verificarProductor();
        $datos = $this->validarDatos($request);
        $datos['user_id'] = Auth::id();
        $datos['community_id'] = $datos['community_id'] ?? Auth::user()->community_id;
        $datos['status'] = 'disponible';

        Producto::create($datos);

        return redirect()->route('mercado.indice')->with('success', 'Publicaste un producto en el mercado.');
    }

    public function edit(Producto $producto): View
    {
        $this->verificarPropietario($producto);

        return view('productos.formulario', [
            'producto' => $producto,
            'comunidades' => Comunidad::orderBy('name')->get(),
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
            $producto->update(['status' => 'oculto']);
        } else {
            $producto->delete();
        }

        return redirect()->route('mercado.indice')->with('success', 'La publicación dejó de mostrarse en el mercado.');
    }

    private function verificarPropietario(Producto $producto): void
    {
        abort_unless(Auth::user()->esProductor(), 403);
        abort_unless($producto->user_id === Auth::id() || Auth::user()->esAdministrador(), 403);
    }

    private function validarDatos(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:140'],
            'category' => ['nullable', 'string', 'max:80'],
            'description' => ['nullable', 'string', 'max:3000'],
            'quantity' => ['required', 'numeric', 'gt:0', 'max:99999999'],
            'unit' => ['required', 'in:kg,arroba,saco,unidad,litro'],
            'unit_price' => ['required', 'numeric', 'min:0.01', 'max:99999999'],
            'image_url' => ['nullable', 'url:http,https', 'max:2048'],
            'cesta_salvada' => ['required', 'boolean'],
            'community_id' => ['nullable', 'exists:communities,id'],
        ]);
    }
}
