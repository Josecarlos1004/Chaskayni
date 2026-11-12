<?php

namespace Tests\Feature;

use App\Models\Pedido;
use App\Models\Producto;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PedidosGestionTest extends TestCase
{
    use RefreshDatabase;

    public function test_productor_acepta_y_completa_su_pedido(): void
    {
        [$productor, $pedido, $producto] = $this->crearPedido();

        $this->actingAs($productor)->get(route('pedidos.recibidos'))->assertOk()->assertSee('Pedidos recibidos');
        $this->actingAs($productor)->patch(route('pedidos.estado', $pedido), ['estado' => 'aceptado'])->assertRedirect(route('pedidos.recibidos'));
        $this->assertSame('aceptado', $pedido->fresh()->estado);
        $this->actingAs($productor)->patch(route('pedidos.estado', $pedido), ['estado' => 'completado'])->assertRedirect(route('pedidos.recibidos'));
        $this->assertSame('completado', $pedido->fresh()->estado);
        $this->assertSame(3.0, (float) $producto->fresh()->cantidad);
    }

    public function test_rechazar_devuelve_stock_una_vez_y_bloquea_pedidos_ajenos(): void
    {
        [$productor, $pedido, $producto] = $this->crearPedido();
        $otro = Usuario::create(['name' => 'Otro productor', 'email' => 'otro-pedido@example.test', 'password' => 'test-password', 'rol' => 'productor', 'activo' => true]);

        $this->actingAs($otro)->patch(route('pedidos.estado', $pedido), ['estado' => 'rechazado'])->assertForbidden();
        $this->actingAs($productor)->patch(route('pedidos.estado', $pedido), ['estado' => 'rechazado'])->assertRedirect(route('pedidos.recibidos'));
        $this->assertSame(5.0, (float) $producto->fresh()->cantidad);
        $this->actingAs($productor)->patch(route('pedidos.estado', $pedido), ['estado' => 'rechazado'])->assertSessionHasErrors('estado');
        $this->assertSame(5.0, (float) $producto->fresh()->cantidad);
    }

    private function crearPedido(): array
    {
        $productor = Usuario::create(['name' => 'Productor pedido', 'email' => 'productor-pedido@example.test', 'password' => 'test-password', 'rol' => 'productor', 'activo' => true]);
        $comprador = Usuario::create(['name' => 'Comprador pedido', 'email' => 'comprador-pedido@example.test', 'password' => 'test-password', 'rol' => 'comprador', 'activo' => true]);
        $producto = Producto::create(['usuario_id' => $productor->id, 'nombre' => 'Papa nativa', 'cantidad' => 5, 'unidad' => 'kg', 'precio_unitario' => 4, 'estado' => 'disponible']);
        $this->actingAs($comprador)->post(route('pedidos.guardar', $producto), ['cantidad' => 2])->assertRedirect(route('pedidos.indice'));

        return [$productor, Pedido::firstOrFail(), $producto];
    }
}
