<?php

namespace Tests\Feature;

use App\Models\Comunidad;
use App\Models\Refugio;
use App\Models\ReporteRiesgo;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MapaComunitarioTest extends TestCase
{
    use RefreshDatabase;

    public function test_el_mapa_publico_muestra_refugios_activos_y_solo_reportes_revisados(): void
    {
        $usuario = Usuario::factory()->create();
        $comunidad = Comunidad::create(['nombre' => 'Comunidad de prueba', 'provincia' => 'Cusco']);
        Refugio::create([
            'nombre' => 'Refugio de prueba', 'direccion' => 'Dirección de prueba', 'capacidad' => 20,
            'activo' => true, 'latitud' => -13.53, 'longitud' => -71.96, 'comunidad_id' => $comunidad->id,
        ]);
        ReporteRiesgo::create([
            'reportante_id' => $usuario->id, 'comunidad_id' => $comunidad->id, 'titulo' => 'Reporte revisado visible',
            'descripcion' => 'Descripción verificada', 'latitud' => -13.52, 'longitud' => -71.95, 'estado' => 'publicado',
        ]);
        ReporteRiesgo::create([
            'reportante_id' => $usuario->id, 'comunidad_id' => $comunidad->id, 'titulo' => 'Reporte pendiente privado',
            'descripcion' => 'Aún debe revisarse', 'latitud' => -13.51, 'longitud' => -71.94, 'estado' => 'pendiente',
        ]);

        $this->get(route('mapa.indice'))
            ->assertOk()
            ->assertSee('Refugio de prueba')
            ->assertSee('Reporte revisado visible')
            ->assertDontSee('Reporte pendiente privado');
    }

    public function test_solo_administracion_puede_registrar_refugios(): void
    {
        $datos = [
            'nombre' => 'Refugio nuevo', 'direccion' => 'Calle principal', 'capacidad' => 45,
            'latitud' => '-13.53', 'longitud' => '-71.96',
        ];

        $this->post(route('administracion.refugios.guardar'), $datos)->assertRedirect(route('login'));

        $administrador = Usuario::factory()->create(['rol' => 'administrador']);
        $this->actingAs($administrador)->post(route('administracion.refugios.guardar'), $datos)
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('refugios', ['nombre' => 'Refugio nuevo', 'activo' => 1]);
    }
}
