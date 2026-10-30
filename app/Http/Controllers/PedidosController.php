<?php

namespace App\Http\Controllers;

use App\Models\DetallePedido;
use App\Models\Pedido;
use App\Models\Producto;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PedidosController extends Controller
{
    public function index(): View
    {
        return view('pedidos.indice', [
            'pedidos' => Pedido::with('detalles.producto.vendedor')
                ->where('comprador_id', Auth::id())
                ->latest()
                ->paginate(10),
        ]);
    }

    public function store(Request $request, Producto $producto): RedirectResponse
    {
        abort_if(Auth::user()->rol === 'productor', 403, 'Las cuentas de productor no pueden comprar.');
        $datos = $request->validate([
            'cantidad' => ['required', 'numeric', 'gt:0', 'max:99999999'],
            'notas' => ['nullable', 'string', 'max:1000'],
        ]);

        // El bloqueo evita descontar las mismas existencias en compras simultáneas.
        DB::transaction(function () use ($producto, $datos) {
            $productoBloqueado = Producto::query()->lockForUpdate()->findOrFail($producto->id);
            $cantidad = (float) $datos['cantidad'];

            abort_unless($productoBloqueado->estado === 'disponible', 422, 'Este producto no está disponible.');
            abort_if($productoBloqueado->usuario_id === Auth::id(), 422, 'No puedes pedir tu propia publicación.');
            abort_if($cantidad > (float) $productoBloqueado->cantidad, 422, 'No hay suficiente cantidad disponible.');

            $precioUnitario = $productoBloqueado->precioUnitarioAplicado();
            $subtotal = round($cantidad * $precioUnitario, 2);
            $pedido = Pedido::create([
                'comprador_id' => Auth::id(),
                'estado' => 'pendiente',
                'total' => $subtotal,
                'notas' => $datos['notas'] ?? null,
            ]);

            DetallePedido::create([
                'pedido_id' => $pedido->id,
                'producto_id' => $productoBloqueado->id,
                'cantidad' => $cantidad,
                // Conserva el precio de esta compra aunque después cambie el precio publicado.
                'precio_unitario' => $precioUnitario,
                'subtotal' => $subtotal,
            ]);

            $productoBloqueado->cantidad = round((float) $productoBloqueado->cantidad - $cantidad, 2);
            if ((float) $productoBloqueado->cantidad <= 0) {
                $productoBloqueado->estado = 'agotado';
            }
            $productoBloqueado->save();
        });

        return redirect()->route('pedidos.indice')->with('success', 'Solicitud de pedido registrada. Coordina la entrega con el productor.');
    }
}
