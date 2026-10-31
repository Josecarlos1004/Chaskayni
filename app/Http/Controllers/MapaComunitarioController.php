<?php

namespace App\Http\Controllers;

use App\Models\Refugio;
use App\Models\ReporteRiesgo;
use Illuminate\View\View;

class MapaComunitarioController extends Controller
{
    public function index(): View
    {
        $refugios = Refugio::where('activo', true)->whereNotNull('latitud')->whereNotNull('longitud')->orderBy('nombre')->get();
        $reportes = ReporteRiesgo::where('estado', 'revisado')->whereNotNull('latitud')->whereNotNull('longitud')->latest()->get();

        return view('riesgos.mapa', [
            'refugios' => Refugio::with('comunidad')->where('activo', true)->orderBy('nombre')->get(),
            'reportes' => ReporteRiesgo::with('comunidad')->where('estado', 'revisado')->latest()->get(),
            'datosMapa' => [
                'refugios' => $refugios->map(fn ($refugio) => ['nombre' => $refugio->nombre, 'direccion' => $refugio->direccion, 'latitud' => $refugio->latitud, 'longitud' => $refugio->longitud])->values(),
                'reportes' => $reportes->map(fn ($reporte) => ['titulo' => $reporte->titulo, 'descripcion' => $reporte->descripcion, 'latitud' => $reporte->latitud, 'longitud' => $reporte->longitud])->values(),
            ],
        ]);
    }
}
