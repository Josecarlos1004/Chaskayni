<?php

namespace Database\Seeders;

use App\Models\AlertaHelada;
use App\Models\Comunidad;
use App\Models\Usuario;
use Illuminate\Database\Seeder;

class AlertasDemostracionSeeder extends Seeder
{
    public function run(): void
    {
        $autor = Usuario::where('name', 'like', '%alejandro%')->where('activo', true)->orderBy('id')->first();

        if (! $autor) {
            $this->command?->warn('No se encontró ninguna cuenta activa con el nombre alejandro. Crea o aprueba la cuenta y vuelve a ejecutar este seeder.');

            return;
        }

        $creadas = 0;

        foreach ($this->alertas() as $datos) {
            $comunidad = Comunidad::firstOrCreate(['nombre' => $datos['comunidad']]);

            $riesgo = match (true) {
                $datos['temperatura'] <= -5 => 'alto',
                $datos['temperatura'] <= -2 => 'medio',
                default => 'bajo',
            };

            $nivel = match ($riesgo) {
                'alto' => 'urgente',
                'medio' => 'precaucion',
                default => 'informacion',
            };

            $titulo = 'Riesgo '.$riesgo.' de helada (demostración)';

            $alerta = AlertaHelada::firstOrCreate(
                ['comunidad_id' => $comunidad->id, 'titulo' => $titulo],
                [
                    'creado_por' => $autor->id,
                    'tipo' => 'clima',
                    'mensaje' => 'Temperatura prevista: '.$datos['temperatura'].' °C. Alerta de demostración para probar el sistema.',
                    'nivel' => $nivel,
                    'temperatura_c' => $datos['temperatura'],
                    'nivel_riesgo' => $riesgo,
                    'fecha_inicio' => now(),
                    'fecha_fin' => now()->addDays(7),
                    'estado' => 'publicada',
                ],
            );

            $creadas += $alerta->wasRecentlyCreated ? 1 : 0;
        }

        $this->command?->info("Alertas creadas: {$creadas}");
    }

    private function alertas(): array
    {
        return [
            ['comunidad' => 'Chinchero', 'temperatura' => -6.5],
            ['comunidad' => 'Maras', 'temperatura' => -3.0],
            ['comunidad' => 'Anta', 'temperatura' => -1.5],
        ];
    }
}