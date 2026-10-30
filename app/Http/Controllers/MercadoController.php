<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MercadoController extends Controller
{
    public function index(Request $request): View
    {
        $productos = Producto::with(['vendedor', 'comunidad'])
            ->where('estado', 'disponible')
            ->where('cantidad', '>', 0)
            ->when($request->filled('q'), function ($query) use ($request) {
                $termino = trim((string) $request->query('q'));
                $query->where(function ($subconsulta) use ($termino) {
                    $subconsulta->where('nombre', 'like', "%{$termino}%")
                        ->orWhere('categoria', 'like', "%{$termino}%")
                        ->orWhere('descripcion', 'like', "%{$termino}%");
                });
            })
            ->when($request->filled('categoria'), fn ($query) => $query->where('categoria', $request->query('categoria')))
            ->when($request->boolean('cesta_salvada'), fn ($query) => $query->where('cesta_salvada', true))
            ->latest()
            ->paginate(12)
            ->withQueryString();

        $categorias = Producto::where('estado', 'disponible')
            ->whereNotNull('categoria')
            ->distinct()
            ->orderBy('categoria')
            ->pluck('categoria');

        return view('mercado.indice', compact('productos', 'categorias'));
    }
}
