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
            'role' => 'productor',
        ]);
        $comprador = Usuario::create([
            'name' => 'Comprador de prueba',
            'email' => 'comprador@example.test',
            'password' => 'test-password',
            'role' => 'comprador',
        ]);

        $this->actingAs($productor)->post(route('productos.guardar'), [
            'name' => 'Papa nativa de prueba',
            'category' => 'Tubérculos',
            'quantity' => 10,
            'unit' => 'kg',
            'unit_price' => 10,
            'cesta_salvada' => 1,
        ])->assertRedirect(route('mercado.indice'));

        $producto = Producto::where('name', 'Papa nativa de prueba')->firstOrFail();
        $this->assertTrue($producto->cesta_salvada);
        $this->assertSame(7.0, $producto->precioUnitarioAplicado());

        $this->actingAs($comprador)->post(route('pedidos.guardar', $producto), [
            'quantity' => 2,
        ])->assertRedirect(route('pedidos.indice'));

        $detalle = DetallePedido::where('product_id', $producto->id)->firstOrFail();
        $this->assertSame(7.0, (float) $detalle->unit_price);
        $this->assertSame(14.0, (float) $detalle->subtotal);
        $this->assertSame(8.0, (float) $producto->fresh()->quantity);
    }

    public function test_filtro_muestra_solo_cestas_salvadas(): void
    {
        $productor = Usuario::create([
            'name' => 'Productor de prueba',
            'email' => 'productor-filtro@example.test',
            'password' => 'test-password',
            'role' => 'productor',
        ]);

        Producto::create([
            'user_id' => $productor->id,
            'name' => 'Cesta de papa',
            'quantity' => 5,
            'unit' => 'kg',
            'unit_price' => 10,
            'cesta_salvada' => true,
            'status' => 'disponible',
        ]);
        Producto::create([
            'user_id' => $productor->id,
            'name' => 'Papa regular',
            'quantity' => 5,
            'unit' => 'kg',
            'unit_price' => 10,
            'cesta_salvada' => false,
            'status' => 'disponible',
        ]);

        $this->get(route('mercado.indice', ['cesta_salvada' => 1]))
            ->assertOk()
            ->assertSee('Cesta de papa')
            ->assertDontSee('Papa regular');
    }
}
