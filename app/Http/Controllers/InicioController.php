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
                ->where('estado', 'disponible')
                ->where('cantidad', '>', 0)
                ->latest()
                ->take(6)
                ->get(),
            'alertas' => AlertaHelada::with('comunidad')
                ->where('estado', 'publicada')
                ->latest()
                ->take(3)
                ->get(),
        ]);
    }
}
