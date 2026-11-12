<?php

namespace Tests\Feature;

use App\Models\Comunidad;
use App\Models\Refugio;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdministracionCatalogosTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_edita_y_desactiva_comunidad_y_puede_eliminarla_sin_borrar_cuentas(): void
    {
        $admin = Usuario::create([
            'name' => 'Administración',
            'email' => 'admin-catalogo@example.test',
            'password' => 'test-password',
            'rol' => 'administrador',
            'activo' => true,
        ]);
        $comunidad = Comunidad::create([
            'nombre' => 'Comunidad de prueba',
            'provincia' => 'Cusco',
            'activo' => true,
        ]);
        $usuario = Usuario::create([
            'name' => 'Productor de prueba',
            'email' => 'productor-catalogo@example.test',
            'password' => 'test-password',
            'rol' => 'productor',
            'activo' => true,
            'comunidad_id' => $comunidad->id,
        ]);

        $this->actingAs($admin)->put(route('administracion.comunidades.actualizar', $comunidad), [
            'nombre' => 'Comunidad actualizada',
            'provincia' => 'Cusco',
            'activo' => 0,
        ])->assertRedirect();

        $this->assertDatabaseHas('comunidades', ['id' => $comunidad->id, 'nombre' => 'Comunidad actualizada', 'activo' => 0]);
        $this->actingAs($admin)->delete(route('administracion.comunidades.eliminar', $comunidad))->assertRedirect();
        $this->assertDatabaseMissing('comunidades', ['id' => $comunidad->id]);
        $this->assertDatabaseHas('users', ['id' => $usuario->id, 'comunidad_id' => null]);
    }

    public function test_admin_actualiza_el_estado_del_refugio_y_puede_eliminarlo(): void
    {
        $admin = Usuario::create([
            'name' => 'Administración refugios',
            'email' => 'admin-refugios@example.test',
            'password' => 'test-password',
            'rol' => 'administrador',
            'activo' => true,
        ]);
        $refugio = Refugio::create([
            'nombre' => 'Refugio de prueba',
            'direccion' => 'Cusco',
            'capacidad' => 20,
            'activo' => true,
        ]);

        $this->actingAs($admin)->put(route('administracion.refugios.actualizar', $refugio), [
            'nombre' => 'Refugio actualizado',
            'direccion' => 'Cusco',
            'capacidad' => 25,
            'activo' => 0,
        ])->assertRedirect();

        $this->assertDatabaseHas('refugios', ['id' => $refugio->id, 'nombre' => 'Refugio actualizado', 'capacidad' => 25, 'activo' => 0]);
        $this->actingAs($admin)->delete(route('administracion.refugios.eliminar', $refugio))->assertRedirect();
        $this->assertDatabaseMissing('refugios', ['id' => $refugio->id]);
    }

    public function test_comprador_no_puede_eliminar_una_comunidad(): void
    {
        $comprador = Usuario::create([
            'name' => 'Comprador',
            'email' => 'comprador-catalogo@example.test',
            'password' => 'test-password',
            'rol' => 'comprador',
            'activo' => true,
        ]);
        $comunidad = Comunidad::create([
            'nombre' => 'Comunidad protegida',
            'provincia' => 'Cusco',
            'activo' => true,
        ]);

        $this->actingAs($comprador)->delete(route('administracion.comunidades.eliminar', $comunidad))->assertForbidden();
        $this->assertDatabaseHas('comunidades', ['id' => $comunidad->id]);
    }
}
