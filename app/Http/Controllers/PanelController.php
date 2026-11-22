<?php

namespace App\Http\Controllers;

use App\Models\AlertaHelada;
use App\Models\Pedido;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class PanelController extends Controller
{
    public function index(): View|RedirectResponse
    {
        $usuario = Auth::user();

        if ($usuario->esAdministrador()) {
            return redirect()->route('administracion.panel');
        }

        return view('panel', [
            'cantidadPublicaciones' => $usuario->productos()->count(),
            'productosOcultos' => $usuario->esProductor()
                ? $usuario->productos()->with(['vendedor', 'comunidad'])->where('estado', 'oculto')->latest()->get()
                : collect(),
            'cantidadPedidos' => $usuario->pedidos()->count(),
            'cantidadAlertas' => AlertaHelada::activas()->count(),
            'pedidosRecientes' => Pedido::with('detalles.producto')
                ->where('comprador_id', $usuario->id)
                ->latest()
                ->take(5)
                ->get(),
        ]);
    }
}
