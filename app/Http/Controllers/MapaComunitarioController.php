<?php

namespace App\Http\Controllers;

use App\Models\Refugio;
use App\Models\ReporteRiesgo;
use Illuminate\View\View;

class MapaComunitarioController extends Controller
{
    public function index(): View
    {
        $refugios = Refugio::with('comunidad')
            ->where('activo', true)
            ->orderBy('nombre')
            ->get();
        $reportes = ReporteRiesgo::with('comunidad')
            ->where('estado', 'publicado')
            ->latest()
            ->get();

        return view('riesgos.mapa', [
            'refugios' => $refugios,
            'reportes' => $reportes,
            'datosMapa' => [
                'refugios' => $refugios
                    ->filter(fn ($refugio) => $refugio->latitud !== null && $refugio->longitud !== null)
                    ->map(fn ($refugio) => [
                        'nombre' => $refugio->nombre,
                        'direccion' => $refugio->direccion,
                        'latitud' => $refugio->latitud,
                        'longitud' => $refugio->longitud,
                    ])->values(),
                'reportes' => $reportes
                    ->filter(fn ($reporte) => $reporte->latitud !== null && $reporte->longitud !== null)
                    ->map(fn ($reporte) => [
                        'titulo' => $reporte->titulo,
                        'tipo' => $reporte->tipo === 'otro' ? ($reporte->tipo_personalizado ?: 'Otra emergencia') : ($reporte->tipo === 'refugio' ? 'Refugios' : 'Clima'),
                        'descripcion' => $reporte->descripcion,
                        'latitud' => $reporte->latitud,
                        'longitud' => $reporte->longitud,
                        'url' => route('riesgos.detalle', $reporte),
                    ])->values(),
            ],
        ]);
    }
}
