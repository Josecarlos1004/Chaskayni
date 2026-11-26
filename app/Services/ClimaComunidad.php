<?php

namespace App\Services;

use App\Models\Comunidad;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class ClimaComunidad
{
    /** Consulta condiciones y pronóstico para las coordenadas confirmadas de una comunidad. */
    public function consultar(Comunidad $comunidad): array
    {
        if ($comunidad->latitud === null || $comunidad->longitud === null) {
            throw new RuntimeException('La comunidad todavía no tiene coordenadas registradas.');
        }

        $parametrosPronostico = [
            'latitude' => $comunidad->latitud,
            'longitude' => $comunidad->longitud,
            'current' => 'temperature_2m,relative_humidity_2m,apparent_temperature,wind_speed_10m,weather_code',
            'hourly' => 'temperature_2m,weather_code,precipitation_probability',
            'daily' => 'temperature_2m_max,temperature_2m_min',
            'forecast_days' => 2,
            'timezone' => 'America/Lima',
        ];

        if ($comunidad->altitud_msnm !== null) {
            $parametrosPronostico['elevation'] = $comunidad->altitud_msnm;
        }

        $respuestaClima = Http::acceptJson()
            ->connectTimeout(3)
            ->timeout(8)
            ->get('https://api.open-meteo.com/v1/forecast', $parametrosPronostico);

        if (! $respuestaClima->successful()) {
            throw new RuntimeException('El servicio meteorológico no respondió correctamente.');
        }

        $clima = $respuestaClima->json();
        $actual = $clima['current'] ?? [];
        $horas = $clima['hourly']['time'] ?? [];
        $temperaturas = $clima['hourly']['temperature_2m'] ?? [];
        $codigos = $clima['hourly']['weather_code'] ?? [];
        $probabilidades = $clima['hourly']['precipitation_probability'] ?? [];

        if (! is_numeric($actual['temperature_2m'] ?? null)) {
            throw new RuntimeException('El servicio no entregó una temperatura actual válida.');
        }

        $ahora = Carbon::now('America/Lima');
        $limite = $ahora->copy()->addHours(24);
        $minima = null;
        $horaMinima = null;
        $horario = [];

        foreach ($horas as $indice => $hora) {
            if (! is_numeric($temperaturas[$indice] ?? null)) {
                continue;
            }

            $fechaHora = Carbon::parse($hora, 'America/Lima');
            if ($fechaHora->greaterThanOrEqualTo($ahora) && $fechaHora->lessThanOrEqualTo($limite)) {
                $temperatura = (float) $temperaturas[$indice];
                if ($minima === null || $temperatura < $minima) {
                    $minima = $temperatura;
                    $horaMinima = $fechaHora;
                }

                if (count($horario) < 6) {
                    $horario[] = [
                        'hora' => $fechaHora->format('H:i'),
                        'temperatura' => round($temperatura, 1),
                        'codigo_tiempo' => $codigos[$indice] ?? null,
                        'probabilidad_lluvia' => is_numeric($probabilidades[$indice] ?? null)
                            ? (int) $probabilidades[$indice]
                            : null,
                    ];
                }
            }
        }

        if ($minima === null) {
            throw new RuntimeException('No hay un pronóstico horario disponible para las próximas 24 horas.');
        }

        $nombreUbicacion = collect([
            $comunidad->nombre,
            $comunidad->distrito,
            $comunidad->provincia,
        ])->filter()->implode(', ');

        return [
            'ubicacion' => $nombreUbicacion,
            'latitud' => (float) $comunidad->latitud,
            'longitud' => (float) $comunidad->longitud,
            'temperatura_actual' => round((float) $actual['temperature_2m'], 1),
            'sensacion_termica' => is_numeric($actual['apparent_temperature'] ?? null)
                ? round((float) $actual['apparent_temperature'], 1)
                : null,
            'humedad' => is_numeric($actual['relative_humidity_2m'] ?? null)
                ? (int) $actual['relative_humidity_2m']
                : null,
            'viento_kmh' => is_numeric($actual['wind_speed_10m'] ?? null)
                ? round((float) $actual['wind_speed_10m'], 1)
                : null,
            'codigo_tiempo' => $actual['weather_code'] ?? null,
            'maxima_hoy' => is_numeric($clima['daily']['temperature_2m_max'][0] ?? null)
                ? round((float) $clima['daily']['temperature_2m_max'][0], 1)
                : null,
            'minima_hoy' => is_numeric($clima['daily']['temperature_2m_min'][0] ?? null)
                ? round((float) $clima['daily']['temperature_2m_min'][0], 1)
                : null,
            'minima_24h' => round($minima, 1),
            'hora_minima' => $horaMinima?->format('H:i'),
            'horario' => $horario,
            'actualizado_en' => isset($actual['time'])
                ? Carbon::parse($actual['time'], $clima['timezone'] ?? 'America/Lima')->format('d/m/Y H:i')
                : Carbon::now('America/Lima')->format('d/m/Y H:i'),
        ];
    }
}

