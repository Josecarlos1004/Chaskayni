<?php

namespace App\Http\Controllers;

use App\Models\AlertaHelada;
use App\Models\Producto;
use Illuminate\View\View;

class InicioController extends Controller
{
    public function index(): View
    {
        return view('inicio', [
            'productos' => Producto::with(['vendedor', 'comunidad'])
                ->where('status', 'disponible')
                ->where('quantity', '>', 0)
                ->latest()
                ->take(6)
                ->get(),
            'alertas' => AlertaHelada::with('comunidad')
                ->where('status', 'publicada')
                ->latest()
                ->take(3)
                ->get(),
        ]);
    }
}
