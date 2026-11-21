<?php

namespace App\Http\Controllers;

use App\Models\AlertaHelada;
use App\Models\DetallePedido;
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

        if ($usuario->esProductor()) {
            return $this->panelProductor($usuario);
        }

        return view('panel', [
            'cantidadPublicaciones' => $usuario->productos()->count(),
            'cantidadPedidos' => $usuario->pedidos()->count(),
            'cantidadAlertas' => AlertaHelada::activas()->count(),
            'pedidosRecientes' => Pedido::with('detalles.producto')
                ->where('comprador_id', $usuario->id)
                ->latest()
                ->take(5)
                ->get(),
        ]);
    }

    /** Resume las ventas y los pedidos por atender de un productor. */
    private function panelProductor($usuario): View
    {
        $disponibles = $usuario->productos()->where('estado', 'disponible')->where('cantidad', '>', 0);

        return view('panel', [
            'cantidadPublicaciones' => $usuario->productos()->count(),
            'cantidadPedidos' => $usuario->pedidos()->count(),
            'cantidadAlertas' => AlertaHelada::activas()->count(),
            'pedidosRecientes' => collect(),
            'pedidosPendientes' => $usuario->pedidosPendientesRecibidos(),
            'ventasMes' => (float) DetallePedido::whereHas('producto', fn ($productos) => $productos->where('usuario_id', $usuario->id))
                ->whereHas('pedido', fn ($pedidos) => $pedidos->where('estado', '!=', 'rechazado')->where('created_at', '>=', now()->startOfMonth()))
                ->sum('subtotal'),
            'productosActivos' => (clone $disponibles)->count(),
            'productosPocoStock' => (clone $disponibles)->where('cantidad', '<=', 5)->count(),
            'pedidosPorAtender' => Pedido::with(['comprador', 'detalles.producto'])
                ->where('estado', 'pendiente')
                ->deProductor($usuario->id)
                ->latest()
                ->take(5)
                ->get(),
            'alertasComunidad' => AlertaHelada::activas()->with('comunidad')
                ->where(fn ($consulta) => $consulta->whereNull('comunidad_id')->orWhere('comunidad_id', $usuario->comunidad_id))
                ->latest()
                ->take(3)
                ->get(),
        ]);
    }
}