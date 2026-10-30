<?php

namespace App\Http\Controllers;

use App\Models\AlertaHelada;
use App\Models\Comunidad;
use App\Models\ReporteRiesgo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AdministracionController extends Controller
{
    private function verificarAdministrador(): void
    {
        abort_unless(Auth::user()->esAdministrador(), 403, 'Esta sección es solo para administración.');
    }

    public function listarComunidades(): View
    {
        $this->verificarAdministrador();

        return view('administracion.comunidades', [
            'comunidades' => Comunidad::withCount(['usuarios', 'productos'])->orderBy('nombre')->get(),
        ]);
    }

    public function guardarComunidad(Request $request): RedirectResponse
    {
        $this->verificarAdministrador();
        $datos = $request->validate([
            'nombre' => ['required', 'string', 'max:120', 'unique:comunidades,nombre'],
            'distrito' => ['nullable', 'string', 'max:120'],
            'provincia' => ['required', 'string', 'max:120'],
            'altitud_msnm' => ['nullable', 'integer', 'between:0,8000'],
            'descripcion' => ['nullable', 'string', 'max:2000'],
        ]);

        Comunidad::create($datos);

        return back()->with('success', 'La comunidad quedó registrada.');
    }

    public function crearAlerta(): View
    {
        $this->verificarAdministrador();

        return view('administracion.crear-alerta', [
            'comunidades' => Comunidad::orderBy('nombre')->get(),
        ]);
    }

    public function guardarAlerta(Request $request): RedirectResponse
    {
        $this->verificarAdministrador();
        $datos = $request->validate([
            'titulo' => ['required', 'string', 'max:160'],
            'mensaje' => ['required', 'string', 'max:3000'],
            'nivel' => ['required', 'in:informacion,precaucion,urgente'],
            'comunidad_id' => ['nullable', 'exists:comunidades,id'],
            'fecha_inicio' => ['nullable', 'date'],
            'fecha_fin' => ['nullable', 'date', 'after_or_equal:fecha_inicio'],
        ]);
        AlertaHelada::create([
            ...$datos,
            'creado_por' => Auth::id(),
            'estado' => 'publicada',
        ]);

        return redirect()->route('alertas.indice')->with('success', 'La alerta fue publicada por administración.');
    }

    public function evaluarTemperatura(Request $request): RedirectResponse
    {
        $this->verificarAdministrador();
        $datos = $request->validate([
            'comunidad_id' => ['required', 'exists:comunidades,id'],
            'temperatura_c' => ['required', 'numeric', 'between:-80,60'],
        ]);
        $temperatura = round((float) $datos['temperatura_c'], 2);

        if ($temperatura >= 0) {
            return back()->withErrors([
                'temperatura_c' => 'Con 0 °C o más no se genera una alerta de helada.',
            ])->withInput();
        }

        $riesgo = match (true) {
            $temperatura <= -5 => 'alto',
            $temperatura <= -2 => 'medio',
            default => 'bajo',
        };
        $nivelAviso = match ($riesgo) {
            'alto' => 'urgente',
            'medio' => 'precaucion',
            default => 'informacion',
        };

        AlertaHelada::create([
            'creado_por' => Auth::id(),
            'comunidad_id' => $datos['comunidad_id'],
            'titulo' => 'Riesgo '.$riesgo.' de helada',
            'mensaje' => 'Temperatura prevista: '.$temperatura.' °C. Revisa las medidas de protección frente a heladas recomendadas para tu comunidad.',
            'nivel' => $nivelAviso,
            'temperatura_c' => $temperatura,
            'nivel_riesgo' => $riesgo,
            'fecha_inicio' => now(),
            'estado' => 'publicada',
        ]);

        return redirect()->route('alertas.indice')->with('success', 'Se evaluó la temperatura y se publicó la alerta.');
    }

    public function cerrarAlerta(AlertaHelada $alertaHelada): RedirectResponse
    {
        $this->verificarAdministrador();
        abort_unless($alertaHelada->estado === 'publicada', 404);

        $alertaHelada->update([
            'estado' => 'cerrada',
            'cerrado_en' => now(),
        ]);

        return back()->with('success', 'La alerta se cerró.');
    }

    public function listarReportes(): View
    {
        $this->verificarAdministrador();

        return view('administracion.reportes', [
            'reportes' => ReporteRiesgo::with(['reportante', 'comunidad'])->latest()->paginate(15),
        ]);
    }

    public function revisarReporte(Request $request, ReporteRiesgo $reporteRiesgo): RedirectResponse
    {
        $this->verificarAdministrador();
        $datos = $request->validate(['estado' => ['required', 'in:revisado,descartado']]);
        $reporteRiesgo->update($datos);

        return back()->with('success', 'Se actualizó el estado del reporte.');
    }
}
