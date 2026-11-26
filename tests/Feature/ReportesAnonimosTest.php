<?php

namespace Tests\Feature;

use App\Models\ReporteRiesgo;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ReportesAnonimosTest extends TestCase
{
    use RefreshDatabase;

    public function test_el_formulario_exige_foto_y_gps_a_visitantes(): void
    {
        $this->from(route('riesgos.crear'))
            ->post(route('riesgos.guardar'), [
                'tipo' => 'otro',
                'tipo_personalizado' => 'Accidente vial',
                'titulo' => 'Accidente en la vía',
                'descripcion' => 'Hay un accidente que requiere atención.',
            ])
            ->assertRedirect(route('riesgos.crear'))
            ->assertSessionHasErrors(['foto_evidencia', 'latitud', 'longitud', 'gps_confirmado']);

        $this->assertDatabaseCount('reportes_riesgo', 0);
    }

    public function test_una_persona_sin_cuenta_puede_enviar_un_reporte_pendiente(): void
    {
        Storage::fake('privado');

        $this->get(route('riesgos.crear'))
            ->assertOk()
            ->assertSee('sin crear una cuenta')
            ->assertSee('Enviar reporte');

        $respuesta = $this->post(route('riesgos.guardar'), [
            'tipo' => 'otro',
            'tipo_personalizado' => 'Accidente vial',
            'titulo' => 'Accidente en la vía',
            'descripcion' => 'Hay un accidente que requiere atención.',
            'latitud' => '-13.5320000',
            'longitud' => '-71.9670000',
            'gps_confirmado' => '1',
            'foto_evidencia' => UploadedFile::fake()->create('accidente.jpg', 10, 'image/jpeg'),
        ]);

        $respuesta->assertRedirect(route('alertas.indice'))
            ->assertSessionHas('success');

        $reporte = ReporteRiesgo::query()->firstOrFail();
        $this->assertNull($reporte->reportante_id);
        $this->assertSame('pendiente', $reporte->estado);
        $this->assertSame('Accidente vial', $reporte->tipo_personalizado);
        $this->assertSame('-13.5320000', $reporte->latitud);
        $this->assertNotEmpty($reporte->foto_evidencia);
        Storage::disk('privado')->assertExists($reporte->foto_evidencia);

        $administrador = Usuario::factory()->create(['rol' => 'administrador']);
        $this->actingAs($administrador)
            ->get(route('administracion.reportes.indice'))
            ->assertOk()
            ->assertSee('Reporte anónimo')
            ->assertSee('Accidente en la vía');
    }
}
