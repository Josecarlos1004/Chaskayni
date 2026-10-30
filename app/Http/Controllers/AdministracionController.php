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
            'descripcion' => ['nullable', 'string', 'max:2000'],
        ]);
        Comunidad::create($datos);

        return back()->with('success', 'La comunidad quedó registrada.');
    }

    public function crearAlerta(): View
    {
        $this->verificarAdministrador();

        return view('administracion.crear-alerta', ['comunidades' => Comunidad::orderBy('nombre')->get()]);
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
