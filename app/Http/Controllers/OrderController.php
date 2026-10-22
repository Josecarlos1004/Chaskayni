<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function index(): View
    {
        return view('orders.index', [
            'orders' => Order::with('items.product.seller')
                ->where('buyer_id', Auth::id())
                ->latest()
                ->paginate(10),
        ]);
    }

    public function store(Request $request, Product $product): RedirectResponse
    {
        abort_if(Auth::user()->role === 'productor', 403, 'Las cuentas de productor no pueden comprar.');
        $data = $request->validate([
            'quantity' => ['required', 'numeric', 'gt:0', 'max:99999999'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        DB::transaction(function () use ($product, $data) {
            $lockedProduct = Product::query()->lockForUpdate()->findOrFail($product->id);
            $quantity = (float) $data['quantity'];

            abort_unless($lockedProduct->status === 'disponible', 422, 'Este producto no está disponible.');
            abort_if($lockedProduct->user_id === Auth::id(), 422, 'No puedes pedir tu propia publicación.');
            abort_if($quantity > (float) $lockedProduct->quantity, 422, 'No hay suficiente cantidad disponible.');

            $subtotal = round($quantity * (float) $lockedProduct->unit_price, 2);
            $order = Order::create([
                'buyer_id' => Auth::id(),
                'status' => 'pendiente',
                'total' => $subtotal,
                'notes' => $data['notes'] ?? null,
            ]);

            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $lockedProduct->id,
                'quantity' => $quantity,
                'unit_price' => $lockedProduct->unit_price,
                'subtotal' => $subtotal,
            ]);

            $lockedProduct->quantity = round((float) $lockedProduct->quantity - $quantity, 2);
            if ((float) $lockedProduct->quantity <= 0) {
                $lockedProduct->status = 'agotado';
            }
            $lockedProduct->save();
        });

        return redirect()->route('orders.index')->with('success', 'Solicitud de pedido registrada. Coordina la entrega con el productor.');
    }
}