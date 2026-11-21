<?php

namespace App\Http\Controllers;

use App\Models\DetallePedido;
use App\Models\Pedido;
use App\Models\Producto;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
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

    /** Muestra las solicitudes de compra dirigidas a las publicaciones del productor. */
    public function recibidos(Request $request): View
    {
        $usuario = Auth::user();
        abort_unless($usuario->esProductor(), 403, 'Solo productores y administración pueden ver esta sección.');
        $consulta = Pedido::with(['comprador', 'detalles.producto'])->latest();
        if (! $usuario->esAdministrador()) {
            $consulta->deProductor($usuario->id);
        }

        // Agrupa los estados intermedios en "en curso" para filtrar con menos opciones.
        $grupos = [
            'pendiente' => ['pendiente'],
            'en_curso' => ['aceptado', 'preparando', 'en_camino'],
            'completado' => ['completado'],
            'rechazado' => ['rechazado'],
        ];
        $filtro = $request->query('estado');
        if (is_string($filtro) && isset($grupos[$filtro])) {
            $consulta->whereIn('estado', $grupos[$filtro]);
        } else {
            $filtro = null;
        }

        return view('pedidos.recibidos', [
            'pedidos' => $consulta->paginate(15)->withQueryString(),
            'filtro' => $filtro,
        ]);
    }

    /** Valida el flujo, evita cambios ajenos y devuelve existencias al rechazar. */
    public function actualizarEstado(Request $request, Pedido $pedido): RedirectResponse
    {
        $usuario = Auth::user();
        abort_unless($usuario->esProductor(), 403, 'Solo productores y administración pueden actualizar pedidos.');
        $datos = $request->validate(['estado' => ['required', 'in:aceptado,rechazado,preparando,en_camino,completado']]);
        DB::transaction(function () use ($pedido, $usuario, $datos): void {
            $pedidoBloqueado = Pedido::query()->lockForUpdate()->findOrFail($pedido->id);
            $detalles = DetallePedido::where('pedido_id', $pedidoBloqueado->id)->orderBy('id')->lockForUpdate()->get();
            abort_if($detalles->isEmpty(), 422, 'El pedido no tiene productos asociados.');
            $productos = [];
            foreach ($detalles as $detalle) {
                $producto = Producto::query()->lockForUpdate()->findOrFail($detalle->producto_id);
                abort_unless($usuario->esAdministrador() || (int) $producto->usuario_id === (int) $usuario->id, 403, 'No puedes modificar un pedido ajeno.');
                $productos[] = [$detalle, $producto];
            }
            $estadoNuevo = $datos['estado'];
            $transiciones = [
                'pendiente' => ['aceptado', 'rechazado'],
                'aceptado' => ['preparando'],
                'preparando' => ['en_camino'],
                'en_camino' => ['completado'],
            ];
            $transicion = in_array($estadoNuevo, $transiciones[$pedidoBloqueado->estado] ?? [], true);
            if (! $transicion) {
                throw ValidationException::withMessages(['estado' => 'Ese cambio no es válido para el estado actual del pedido.']);
            }
            if ($estadoNuevo === 'rechazado') {
                foreach ($productos as [$detalle, $producto]) {
                    $producto->cantidad = round((float) $producto->cantidad + (float) $detalle->cantidad, 2);
                    if ($producto->estado === 'agotado' && (float) $producto->cantidad > 0) {
                        $producto->estado = 'disponible';
                    }
                    $producto->save();
                }
            }
            $pedidoBloqueado->update(['estado' => $estadoNuevo]);
        });

        return redirect()->route('pedidos.recibidos')->with('success', 'Se actualizó el pedido.');
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