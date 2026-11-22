<?php

namespace App\Http\Controllers;

use App\Models\Comunidad;
use App\Models\Refugio;
use App\Models\ReporteRiesgo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Throwable;

class ReportesRiesgoController extends Controller
{
    public function create(): View
    {
        return view('riesgos.crear', [
            'refugios' => Refugio::where('activo', true)->whereNotNull('latitud')->whereNotNull('longitud')->get(['id', 'nombre', 'latitud', 'longitud']),
            'comunidades' => Comunidad::where('activo', true)->orderBy('nombre')->get(['id', 'nombre']),
            'datosMapa' => ['refugios' => Refugio::where('activo', true)->whereNotNull('latitud')->whereNotNull('longitud')->get(['nombre', 'direccion', 'latitud', 'longitud'])],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $datos = $request->validate([
            'tipo' => ['required', 'in:clima,refugio'],
            'titulo' => ['required', 'string', 'max:160'],
            'descripcion' => ['required', 'string', 'max:3000'],
            'latitud' => ['nullable', 'numeric', 'between:-90,90'],
            'longitud' => ['nullable', 'numeric', 'between:-180,180'],
            'comunidad_id' => ['nullable', Rule::exists('comunidades', 'id')->where('activo', true)],
            'foto_evidencia' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        $fotoEvidencia = $datos['foto_evidencia'] ?? null;
        unset($datos['foto_evidencia']);
        $rutaEvidencia = null;

        try {
            DB::transaction(function () use ($datos, $fotoEvidencia, &$rutaEvidencia): void {
                $reporte = ReporteRiesgo::create([
                    ...$datos,
                    'reportante_id' => Auth::id(),
                    'comunidad_id' => $datos['comunidad_id'] ?? (Auth::user()->comunidad?->activo ? Auth::user()->comunidad_id : null),
                    'estado' => 'pendiente',
                ]);

                if ($fotoEvidencia) {
                    // El disco local usa storage/app/private y no expone esta imagen como archivo público.
                    $rutaEvidencia = $fotoEvidencia->store('evidencias-reportes', 'local');
                    if (! $rutaEvidencia) {
                        throw ValidationException::withMessages(['foto_evidencia' => 'No se pudo guardar la fotografía. Inténtalo nuevamente.']);
                    }
                    $reporte->update(['foto_evidencia' => $rutaEvidencia]);
                }
            });
        } catch (Throwable $error) {
            if ($rutaEvidencia) {
                Storage::disk('local')->delete($rutaEvidencia);
            }
            throw $error;
        }

        return redirect()->route('panel')->with('success', 'Gracias. Tu reporte quedó pendiente de revisión.');
    }
    /** Muestra el detalle únicamente si administración aprobó el reporte. */
    public function detalle(ReporteRiesgo $reporteRiesgo): View
    {
        abort_unless($reporteRiesgo->estado === 'publicado', 404);
        $reporteRiesgo->load(['comunidad', 'alerta']);

        return view('riesgos.detalle', ['reporte' => $reporteRiesgo]);
    }

    /** Sirve la foto del incidente solo después de aprobar el reporte. */
    public function verFotoPublica(ReporteRiesgo $reporteRiesgo): StreamedResponse
    {
        abort_unless($reporteRiesgo->estado === 'publicado', 404);
        $rutaFoto = $reporteRiesgo->foto_evidencia;
        abort_unless($rutaFoto && Storage::disk('local')->exists($rutaFoto), 404);

        return Storage::disk('local')->response(
            $rutaFoto,
            'foto-incidente-'.$reporteRiesgo->id,
            [
                'Cache-Control' => 'public, max-age=300, must-revalidate',
                'X-Content-Type-Options' => 'nosniff',
            ],
            'inline',
        );
    }
}
