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
            ->where('status', 'disponible')
            ->where('quantity', '>', 0)
            ->when($request->filled('q'), function ($query) use ($request) {
                $termino = trim((string) $request->query('q'));
                $query->where(function ($subconsulta) use ($termino) {
                    $subconsulta->where('name', 'like', "%{$termino}%")
                        ->orWhere('category', 'like', "%{$termino}%")
                        ->orWhere('description', 'like', "%{$termino}%");
                });
            })
            ->when($request->filled('category'), fn ($query) => $query->where('category', $request->query('category')))
            ->when($request->boolean('cesta_salvada'), fn ($query) => $query->where('cesta_salvada', true))
            ->latest()
            ->paginate(12)
            ->withQueryString();

        $categorias = Producto::where('status', 'disponible')
            ->whereNotNull('category')
            ->distinct()
            ->orderBy('category')
            ->pluck('category');

        return view('mercado.indice', compact('productos', 'categorias'));
    }
}
