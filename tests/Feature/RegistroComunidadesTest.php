<?php

namespace Tests\Feature;

use App\Models\Comunidad;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistroComunidadesTest extends TestCase
{
    use RefreshDatabase;

    public function test_registro_conserva_la_comunidad_existente_seleccionada(): void
    {
        $comunidad = Comunidad::create([
            'nombre' => 'Comunidad registrada',
            'provincia' => 'Cusco',
            'activo' => true,
        ]);

        $this->post(route('registro.guardar'), $this->datosRegistro([
            'tipo_comunidad' => 'existente',
            'comunidad_id' => $comunidad->id,
        ]))->assertRedirect(route('login'));

        $this->assertDatabaseHas('users', [
            'email' => 'persona@example.test',
            'comunidad_id' => $comunidad->id,
            'comunidad_propuesta' => null,
            'aprobado_en' => null,
        ]);
    }

    public function test_registro_guarda_la_comunidad_propuesta_solo_para_revision(): void
    {
        $this->post(route('registro.guardar'), $this->datosRegistro([
            'tipo_comunidad' => 'propuesta',
            'comunidad_propuesta' => 'Comunidad por verificar',
            'distrito_propuesto' => 'San Sebastián',
        ]))->assertRedirect(route('login'));

        $this->assertDatabaseHas('users', [
            'email' => 'persona@example.test',
            'comunidad_id' => null,
            'comunidad_propuesta' => 'Comunidad por verificar',
            'distrito_propuesto' => 'San Sebastián',
            'aprobado_en' => null,
        ]);
        $this->assertDatabaseMissing('comunidades', ['nombre' => 'Comunidad por verificar']);
    }

    public function test_administracion_debe_asignar_comunidad_activa_para_aprobar(): void
    {
        $administrador = Usuario::create([
            'name' => 'Administración',
            'email' => 'admin-registro@example.test',
            'password' => 'contrasena-de-prueba',
            'rol' => 'administrador',
            'activo' => true,
        ]);
        $persona = Usuario::create([
            'name' => 'Persona pendiente',
            'email' => 'persona-pendiente@example.test',
            'password' => 'contrasena-de-prueba',
            'rol' => 'comprador',
            'activo' => true,
            'comunidad_propuesta' => 'Comunidad propuesta',
            'distrito_propuesto' => 'San Sebastián',
            'aprobado_en' => null,
        ]);
        $this->actingAs($administrador)
            ->get(route('administracion.usuarios.indice'))
            ->assertOk()
            ->assertSee('Comunidad propuesta');

        $datosAprobacion = [
            'rol' => 'comprador',
            'activo' => 1,
            'aprobado' => 1,
            'comunidad_id' => '',
        ];
        $this->actingAs($administrador)
            ->patch(route('administracion.usuarios.actualizar', $persona), $datosAprobacion)
            ->assertSessionHasErrors('comunidad_id');
        $this->assertNull($persona->fresh()->aprobado_en);

        $comunidadValidada = Comunidad::create([
            'nombre' => 'Comunidad validada',
            'distrito' => 'San Sebastián',
            'provincia' => 'Cusco',
            'activo' => true,
        ]);
        $this->actingAs($administrador)
            ->patch(route('administracion.usuarios.actualizar', $persona), [
                ...$datosAprobacion,
                'comunidad_id' => $comunidadValidada->id,
            ])
            ->assertRedirect();

        $this->assertSame($comunidadValidada->id, $persona->fresh()->comunidad_id);
        $this->assertNotNull($persona->fresh()->aprobado_en);
        $this->assertNull($persona->fresh()->comunidad_propuesta);
    }

    private function datosRegistro(array $adicionales = []): array
    {
        return array_merge([
            'name' => 'Persona de prueba',
            'email' => 'persona@example.test',
            'dni' => '12345678',
            'telefono' => '987654321',
            'rol' => 'comprador',
            'password' => 'contrasena-segura',
            'password_confirmation' => 'contrasena-segura',
        ], $adicionales);
    }
}