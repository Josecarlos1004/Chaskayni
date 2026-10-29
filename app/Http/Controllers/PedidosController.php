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
                ->where('buyer_id', Auth::id())
                ->latest()
                ->paginate(10),
        ]);
    }

    public function store(Request $request, Producto $producto): RedirectResponse
    {
        abort_if(Auth::user()->role === 'productor', 403, 'Las cuentas de productor no pueden comprar.');
        $datos = $request->validate([
            'quantity' => ['required', 'numeric', 'gt:0', 'max:99999999'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        // El bloqueo evita descontar las mismas existencias en compras simultáneas.
        DB::transaction(function () use ($producto, $datos) {
            $productoBloqueado = Producto::query()->lockForUpdate()->findOrFail($producto->id);
            $cantidad = (float) $datos['quantity'];

            abort_unless($productoBloqueado->status === 'disponible', 422, 'Este producto no está disponible.');
            abort_if($productoBloqueado->user_id === Auth::id(), 422, 'No puedes pedir tu propia publicación.');
            abort_if($cantidad > (float) $productoBloqueado->quantity, 422, 'No hay suficiente cantidad disponible.');

            $precioUnitario = $productoBloqueado->precioUnitarioAplicado();
            $subtotal = round($cantidad * $precioUnitario, 2);
            $pedido = Pedido::create([
                'buyer_id' => Auth::id(),
                'status' => 'pendiente',
                'total' => $subtotal,
                'notes' => $datos['notes'] ?? null,
            ]);

            DetallePedido::create([
                'order_id' => $pedido->id,
                'product_id' => $productoBloqueado->id,
                'quantity' => $cantidad,
                // Conserva el precio de esta compra aunque después cambie el precio publicado.
                'unit_price' => $precioUnitario,
                'subtotal' => $subtotal,
            ]);

            $productoBloqueado->quantity = round((float) $productoBloqueado->quantity - $cantidad, 2);
            if ((float) $productoBloqueado->quantity <= 0) {
                $productoBloqueado->status = 'agotado';
            }
            $productoBloqueado->save();
        });

        return redirect()->route('pedidos.indice')->with('success', 'Solicitud de pedido registrada. Coordina la entrega con el productor.');
    }
}
