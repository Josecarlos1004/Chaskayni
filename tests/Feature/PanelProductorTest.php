<?php

namespace Tests\Feature;

use App\Models\Pedido;
use App\Models\Producto;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PanelProductorTest extends TestCase
{
    use RefreshDatabase;

    public function test_panel_del_productor_muestra_ventas_y_pedidos_por_atender(): void
    {
        [$productor, , , $pedido] = $this->crearEscenario();

        $this->actingAs($productor)->get(route('panel'))
            ->assertOk()
            ->assertSee('Pedidos por atender')
            ->assertSee('Ventas del mes')
            ->assertSee('Pedido #'.$pedido->id)
            ->assertDontSee('Aún no has realizado pedidos');
    }

    public function test_panel_del_comprador_conserva_sus_pedidos(): void
    {
        [, $comprador] = $this->crearEscenario();

        $this->actingAs($comprador)->get(route('panel'))
            ->assertOk()
            ->assertSee('Pedidos recientes')
            ->assertDontSee('Pedidos por atender');
    }

    public function test_productor_puede_ocultar_y_volver_a_mostrar_una_publicacion(): void
    {
        [$productor, , $producto] = $this->crearEscenario();

        $this->actingAs($productor)->get(route('productos.mis'))->assertOk()->assertSee('Papa nativa');
        $this->actingAs($productor)->patch(route('productos.visibilidad', $producto))->assertRedirect();
        $this->assertSame('oculto', $producto->fresh()->estado);
        $this->actingAs($productor)->patch(route('productos.visibilidad', $producto))->assertRedirect();
        $this->assertSame('disponible', $producto->fresh()->estado);
    }

    public function test_otro_productor_no_puede_cambiar_la_visibilidad_ajena(): void
    {
        [, , $producto] = $this->crearEscenario();
        $otro = Usuario::create(['name' => 'Otro productor', 'email' => 'otro-panel@example.test', 'password' => 'test-password', 'rol' => 'productor', 'activo' => true, 'aprobado_en' => now()]);

        $this->actingAs($otro)->patch(route('productos.visibilidad', $producto))->assertForbidden();
        $this->assertSame('disponible', $producto->fresh()->estado);
    }

    public function test_reponer_existencias_vuelve_a_mostrar_un_producto_agotado(): void
    {
        [$productor, , $producto] = $this->crearEscenario();
        $producto->update(['estado' => 'agotado', 'cantidad' => 0]);

        $this->actingAs($productor)->put(route('productos.actualizar', $producto), [
            'nombre' => 'Papa nativa',
            'cantidad' => 12,
            'unidad' => 'kg',
            'precio_unitario' => 4,
            'cesta_salvada' => 0,
        ])->assertRedirect(route('mercado.indice'));

        $this->assertSame('disponible', $producto->fresh()->estado);
        $this->assertSame(12.0, (float) $producto->fresh()->cantidad);
    }

    public function test_telefono_del_comprador_solo_se_muestra_al_aceptar_el_pedido(): void
    {
        [$productor, , , $pedido] = $this->crearEscenario();

        $this->actingAs($productor)->get(route('pedidos.recibidos'))->assertOk()->assertDontSee('987654321');
        $this->actingAs($productor)->patch(route('pedidos.estado', $pedido), ['estado' => 'aceptado']);
        $this->actingAs($productor)->get(route('pedidos.recibidos'))->assertOk()->assertSee('987654321')->assertSee('wa.me/51987654321');
    }

    public function test_filtro_por_estado_y_contador_de_pendientes(): void
    {
        [$productor, , , $pedido] = $this->crearEscenario();

        $this->actingAs($productor)->get(route('pedidos.recibidos', ['estado' => 'pendiente']))->assertOk()->assertSee('Pedido #'.$pedido->id);
        $this->actingAs($productor)->get(route('pedidos.recibidos', ['estado' => 'completado']))->assertOk()->assertSee('No hay pedidos en este estado');
        $this->assertSame(1, $productor->pedidosPendientesRecibidos());
    }

    private function crearEscenario(): array
    {
        $productor = Usuario::create(['name' => 'Productor panel', 'email' => 'productor-panel@example.test', 'password' => 'test-password', 'rol' => 'productor', 'activo' => true, 'aprobado_en' => now()]);
        $comprador = Usuario::create(['name' => 'Comprador panel', 'email' => 'comprador-panel@example.test', 'password' => 'test-password', 'rol' => 'comprador', 'activo' => true, 'aprobado_en' => now(), 'telefono' => '987654321']);
        $producto = Producto::create(['usuario_id' => $productor->id, 'nombre' => 'Papa nativa', 'cantidad' => 5, 'unidad' => 'kg', 'precio_unitario' => 4, 'estado' => 'disponible']);
        $this->actingAs($comprador)->post(route('pedidos.guardar', $producto), ['cantidad' => 2])->assertRedirect(route('pedidos.indice'));

        return [$productor, $comprador, $producto->fresh(), Pedido::firstOrFail()];
    }
}
