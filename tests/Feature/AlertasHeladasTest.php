<?php

namespace Tests\Feature;

use App\Models\AlertaHelada;
use App\Models\Comunidad;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AlertasHeladasTest extends TestCase
{
    use RefreshDatabase;

    public function test_evalua_el_nivel_de_riesgo_segun_la_temperatura(): void
    {
        $administrador = Usuario::factory()->create(['rol' => 'administrador']);
        $comunidad = Comunidad::create([
            'nombre' => 'Comunidad de prueba',
            'provincia' => 'Cusco',
            'altitud_msnm' => 3400,
        ]);

        foreach ([
            [-1, 'bajo', 'informacion'],
            [-2, 'medio', 'precaucion'],
            [-5, 'alto', 'urgente'],
        ] as [$temperatura, $riesgo, $nivel]) {
            $this->actingAs($administrador)
                ->post(route('administracion.alertas.temperatura'), [
                    'comunidad_id' => $comunidad->id,
                    'temperatura_c' => $temperatura,
                ])
                ->assertRedirect(route('alertas.indice'));

            $alerta = AlertaHelada::latest('id')->firstOrFail();
            $this->assertSame($riesgo, $alerta->nivel_riesgo);
            $this->assertSame($nivel, $alerta->nivel);
            $this->assertSame(number_format($temperatura, 2, '.', ''), $alerta->temperatura_c);
        }
    }

    public function test_no_publica_alerta_si_la_temperatura_no_es_bajo_cero(): void
    {
        $administrador = Usuario::factory()->create(['rol' => 'administrador']);
        $comunidad = Comunidad::create([
            'nombre' => 'Comunidad de prueba',
            'provincia' => 'Cusco',
        ]);

        $this->actingAs($administrador)
            ->from(route('administracion.alertas.crear'))
            ->post(route('administracion.alertas.temperatura'), [
                'comunidad_id' => $comunidad->id,
                'temperatura_c' => 0,
            ])
            ->assertRedirect(route('administracion.alertas.crear'))
            ->assertSessionHasErrors('temperatura_c');

        $this->assertDatabaseCount('alertas_heladas', 0);
    }

    public function test_el_administrador_puede_cerrar_una_alerta_activa(): void
    {
        $administrador = Usuario::factory()->create(['rol' => 'administrador']);
        $comunidad = Comunidad::create([
            'nombre' => 'Comunidad de prueba',
            'provincia' => 'Cusco',
        ]);
        $alerta = AlertaHelada::create([
            'creado_por' => $administrador->id,
            'comunidad_id' => $comunidad->id,
            'titulo' => 'Aviso de prueba',
            'mensaje' => 'Aviso temporal para la prueba.',
            'nivel' => 'precaucion',
            'estado' => 'publicada',
        ]);

        $this->actingAs($administrador)
            ->patch(route('administracion.alertas.cerrar', $alerta))
            ->assertRedirect();

        $this->assertSame('cerrada', $alerta->fresh()->estado);
        $this->assertNotNull($alerta->fresh()->cerrado_en);
    }
}
