<?php

namespace App\Http\Controllers;

use App\Models\ReporteRiesgo;
use App\Models\Refugio;
use App\Models\Comunidad;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ReportesRiesgoController extends Controller
{
    public function create(): View
    {
        return view('riesgos.crear', [
            'refugios' => Refugio::where('activo', true)->whereNotNull('latitud')->whereNotNull('longitud')->get(['id', 'nombre', 'latitud', 'longitud']),
            'comunidades' => Comunidad::orderBy('nombre')->get(['id', 'nombre']),
            'datosMapa' => ['refugios' => Refugio::where('activo', true)->whereNotNull('latitud')->whereNotNull('longitud')->get(['nombre', 'direccion', 'latitud', 'longitud'])],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $datos = $request->validate([
            'titulo' => ['required', 'string', 'max:160'],
            'descripcion' => ['required', 'string', 'max:3000'],
            'latitud' => ['nullable', 'numeric', 'between:-90,90'],
            'longitud' => ['nullable', 'numeric', 'between:-180,180'],
            'comunidad_id' => ['nullable', 'exists:comunidades,id'],
        ]);

        ReporteRiesgo::create([
            ...$datos,
            'reportante_id' => Auth::id(),
            'comunidad_id' => $datos['comunidad_id'] ?? Auth::user()->comunidad_id,
        ]);

        return redirect()->route('panel')->with('success', 'Gracias. Tu reporte quedó pendiente de revisión.');
    }
}
