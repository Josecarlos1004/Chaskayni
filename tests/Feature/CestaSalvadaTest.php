<?php

namespace Tests\Feature;

use App\Models\DetallePedido;
use App\Models\Producto;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CestaSalvadaTest extends TestCase
{
    use RefreshDatabase;

    public function test_descuento_cesta_salvada_se_guarda_en_pedido(): void
    {
        $productor = Usuario::create([
            'name' => 'Productor de prueba',
            'email' => 'productor@example.test',
            'password' => 'test-password',
            'rol' => 'productor',
            'activo' => true,
        ]);
        $comprador = Usuario::create([
            'name' => 'Comprador de prueba',
            'email' => 'comprador@example.test',
            'password' => 'test-password',
            'rol' => 'comprador',
            'activo' => true,
        ]);

        $this->actingAs($productor)->post(route('productos.guardar'), [
            'nombre' => 'Papa nativa de prueba',
            'categoria' => 'Tubérculos',
            'cantidad' => 10,
            'unidad' => 'kg',
            'precio_unitario' => 10,
            'cesta_salvada' => 1,
        ])->assertRedirect(route('mercado.indice'));

        $producto = Producto::where('nombre', 'Papa nativa de prueba')->firstOrFail();
        $this->assertTrue($producto->cesta_salvada);
        $this->assertSame(7.0, $producto->precioUnitarioAplicado());

        $this->actingAs($comprador)->post(route('pedidos.guardar', $producto), [
            'cantidad' => 2,
        ])->assertRedirect(route('pedidos.indice'));

        $detalle = DetallePedido::where('producto_id', $producto->id)->firstOrFail();
        $this->assertSame(7.0, (float) $detalle->precio_unitario);
        $this->assertSame(14.0, (float) $detalle->subtotal);
        $this->assertSame(8.0, (float) $producto->fresh()->cantidad);
    }

    public function test_filtro_muestra_solo_cestas_salvadas(): void
    {
        $productor = Usuario::create([
            'name' => 'Productor de prueba',
            'email' => 'productor-filtro@example.test',
            'password' => 'test-password',
            'rol' => 'productor',
            'activo' => true,
        ]);

        Producto::create([
            'usuario_id' => $productor->id,
            'nombre' => 'Cesta de papa',
            'cantidad' => 5,
            'unidad' => 'kg',
            'precio_unitario' => 10,
            'cesta_salvada' => true,
            'estado' => 'disponible',
        ]);
        Producto::create([
            'usuario_id' => $productor->id,
            'nombre' => 'Papa regular',
            'cantidad' => 5,
            'unidad' => 'kg',
            'precio_unitario' => 10,
            'cesta_salvada' => false,
            'estado' => 'disponible',
        ]);

        $this->get(route('mercado.indice', ['cesta_salvada' => 1]))
            ->assertOk()
            ->assertSee('Cesta de papa')
            ->assertDontSee('Papa regular');
    }
}
