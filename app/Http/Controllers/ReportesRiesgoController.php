<?php

namespace App\Http\Controllers;

use App\Models\Comunidad;
use App\Models\Refugio;
use App\Models\ReporteRiesgo;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class ReportesRiesgoController extends Controller
{
    /** Busca una descripción aproximada del punto sin consultar ni revelar datos del reportante. */
    public function buscarUbicacion(Request $request): JsonResponse
    {
        $coordenadas = $request->validate([
            'latitud' => ['required', 'numeric', 'between:-90,90'],
            'longitud' => ['required', 'numeric', 'between:-180,180'],
        ]);

        $latitud = number_format((float) $coordenadas['latitud'], 5, '.', '');
        $longitud = number_format((float) $coordenadas['longitud'], 5, '.', '');
        $claveCache = 'ubicacion-reporte:'.sha1($latitud.','.$longitud);

        try {
            $ubicacion = Cache::remember($claveCache, now()->addDays(30), function () use ($latitud, $longitud): ?array {
                $respuesta = Http::acceptJson()
                    ->withHeaders(['User-Agent' => config('app.name').' (búsqueda de ubicación de reportes)'])
                    ->timeout(5)
                    ->get(config('services.photon.url'), [
                        'lat' => $latitud,
                        'lon' => $longitud,
                    ]);

                if (! $respuesta->successful()) {
                    return null;
                }

                $propiedades = $respuesta->json('features.0.properties');
                if (! is_array($propiedades)) {
                    return null;
                }

                $partes = array_filter([
                    $propiedades['name'] ?? null,
                    $propiedades['street'] ?? null,
                    $propiedades['housenumber'] ?? null,
                    $propiedades['district'] ?? null,
                    $propiedades['city'] ?? $propiedades['locality'] ?? null,
                    $propiedades['county'] ?? null,
                    $propiedades['state'] ?? null,
                    $propiedades['postcode'] ?? null,
                    $propiedades['country'] ?? null,
                ], static fn ($parte): bool => is_string($parte) && trim($parte) !== '');

                return $partes ? ['nombre' => implode(', ', array_unique($partes))] : null;
            });
        } catch (Throwable) {
            $ubicacion = null;
        }

        return response()->json(['ubicacion' => $ubicacion]);
    }

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
            'tipo' => ['required', 'in:clima,refugio,otro'],
            'tipo_personalizado' => ['exclude_unless:tipo,otro', 'required', 'string', 'min:3', 'max:100'],
            'titulo' => ['required', 'string', 'max:160'],
            'descripcion' => ['required', 'string', 'max:3000'],
            'latitud' => ['nullable', 'numeric', 'between:-90,90'],
            'longitud' => ['nullable', 'numeric', 'between:-180,180'],
            'ubicacion_detallada' => ['nullable', 'string', 'max:500'],
            'comunidad_id' => ['nullable', Rule::exists('comunidades', 'id')->where('activo', true)],
            'foto_evidencia' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        $datos['tipo_personalizado'] = $datos['tipo'] === 'otro' ? ($datos['tipo_personalizado'] ?? null) : null;
        $fotoEvidencia = $datos['foto_evidencia'] ?? null;
        unset($datos['foto_evidencia']);
        $rutaEvidencia = null;
        $esAdministrador = Auth::user()->esAdministrador();

        try {
            DB::transaction(function () use ($datos, $fotoEvidencia, &$rutaEvidencia, $esAdministrador): void {
                $reporte = ReporteRiesgo::create([
                    ...$datos,
                    'reportante_id' => Auth::id(),
                    'comunidad_id' => $datos['comunidad_id'] ?? (Auth::user()->comunidad?->activo ? Auth::user()->comunidad_id : null),
                    'estado' => $esAdministrador ? 'publicado' : 'pendiente',
                ]);

                if ($fotoEvidencia) {
                    // La foto permanece privada hasta que el reporte tenga autorización para publicarse.
                    $rutaEvidencia = $fotoEvidencia->store('evidencias-reportes', 'privado');
                    if (! $rutaEvidencia) {
                        throw ValidationException::withMessages(['foto_evidencia' => 'No se pudo guardar la fotografía. Inténtalo nuevamente.']);
                    }
                    $reporte->update(['foto_evidencia' => $rutaEvidencia]);
                }

                if ($esAdministrador) {
                    $reporte->alerta()->create([
                        'creado_por' => Auth::id(),
                        'comunidad_id' => $reporte->comunidad_id,
                        'tipo' => $reporte->tipo,
                        'tipo_personalizado' => $reporte->tipo === 'otro' ? $reporte->tipo_personalizado : null,
                        'titulo' => $reporte->titulo,
                        'mensaje' => $reporte->descripcion,
                        'nivel' => 'precaucion',
                        'fecha_inicio' => now(),
                        'estado' => 'publicada',
                    ]);
                }
            });
        } catch (Throwable $error) {
            if ($rutaEvidencia) {
                Storage::disk('privado')->delete($rutaEvidencia);
            }
            throw $error;
        }

        if ($esAdministrador) {
            return redirect()->route('alertas.indice')->with('success', 'La situación se publicó directamente como alerta comunitaria.');
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
        abort_unless($rutaFoto && Storage::disk('privado')->exists($rutaFoto), 404);

        return Storage::disk('privado')->response(
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
