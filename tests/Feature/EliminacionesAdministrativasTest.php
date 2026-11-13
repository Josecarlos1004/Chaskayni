<?php

namespace Tests\Feature;

use App\Models\AlertaHelada;
use App\Models\DetallePedido;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\ReporteRiesgo;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EliminacionesAdministrativasTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_puede_eliminar_un_reporte_y_una_alerta_individuales(): void
    {
        $admin = $this->crearUsuario('administrador');
        $reporte = ReporteRiesgo::create([
            'reportante_id' => $admin->id,
            'titulo' => 'Reporte de prueba',
            'descripcion' => 'Descripción de prueba',
            'estado' => 'pendiente',
        ]);
        $alerta = AlertaHelada::create([
            'creado_por' => $admin->id,
            'titulo' => 'Alerta de prueba',
            'mensaje' => 'Mensaje de prueba',
            'nivel' => 'informacion',
            'estado' => 'publicada',
        ]);

        $this->actingAs($admin)->delete(route('administracion.reportes.eliminar', $reporte))->assertRedirect();
        $this->actingAs($admin)->delete(route('administracion.alertas.eliminar', $alerta))->assertRedirect();

        $this->assertDatabaseMissing('reportes_riesgo', ['id' => $reporte->id]);
        $this->assertDatabaseMissing('alertas_heladas', ['id' => $alerta->id]);
    }

    public function test_eliminar_pedido_devuelve_existencias_y_borra_sus_detalles(): void
    {
        $admin = $this->crearUsuario('administrador');
        $comprador = $this->crearUsuario('comprador');
        $productor = $this->crearUsuario('productor');
        $producto = Producto::create([
            'usuario_id' => $productor->id,
            'nombre' => 'Papa de prueba',
            'cantidad' => 3,
            'unidad' => 'kg',
            'precio_unitario' => 4,
            'estado' => 'disponible',
        ]);
        $pedido = Pedido::create([
            'comprador_id' => $comprador->id,
            'estado' => 'pendiente',
            'total' => 8,
        ]);
        $detalle = DetallePedido::create([
            'pedido_id' => $pedido->id,
            'producto_id' => $producto->id,
            'cantidad' => 2,
            'precio_unitario' => 4,
            'subtotal' => 8,
        ]);

        $this->actingAs($admin)
            ->delete(route('administracion.pedidos.eliminar', $pedido))
            ->assertRedirect();

        $this->assertSame(5.0, (float) $producto->fresh()->cantidad);
        $this->assertDatabaseMissing('pedidos', ['id' => $pedido->id]);
        $this->assertDatabaseMissing('detalles_pedido', ['id' => $detalle->id]);
    }

    public function test_no_se_elimina_una_cuenta_con_publicaciones_vinculadas(): void
    {
        $admin = $this->crearUsuario('administrador');
        $productor = $this->crearUsuario('productor');
        Producto::create([
            'usuario_id' => $productor->id,
            'nombre' => 'Cosecha vinculada',
            'cantidad' => 1,
            'unidad' => 'kg',
            'precio_unitario' => 5,
            'estado' => 'disponible',
        ]);

        $this->actingAs($admin)
            ->delete(route('administracion.usuarios.eliminar', $productor))
            ->assertRedirect()
            ->assertSessionHasErrors('cuenta');

        $this->assertDatabaseHas('users', ['id' => $productor->id]);
    }

    public function test_no_administrador_no_puede_eliminar_reportes(): void
    {
        $comprador = $this->crearUsuario('comprador');
        $reporte = ReporteRiesgo::create([
            'reportante_id' => $comprador->id,
            'titulo' => 'Reporte protegido',
            'descripcion' => 'Descripción',
            'estado' => 'pendiente',
        ]);

        $this->actingAs($comprador)
            ->delete(route('administracion.reportes.eliminar', $reporte))
            ->assertForbidden();

        $this->assertDatabaseHas('reportes_riesgo', ['id' => $reporte->id]);
    }

    private function crearUsuario(string $rol): Usuario
    {
        static $secuencia = 0;
        $secuencia++;

        return Usuario::create([
            'name' => ucfirst($rol).' de prueba',
            'email' => $rol.'-'.$secuencia.'@ejemplo.test',
            'password' => 'contrasena-segura-de-prueba',
            'rol' => $rol,
            'activo' => true,
        ]);
    }
}
