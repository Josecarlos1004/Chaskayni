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
            'comunidades' => Comunidad::withCount(['usuarios', 'productos'])->orderBy('name')->get(),
        ]);
    }

    public function guardarComunidad(Request $request): RedirectResponse
    {
        $this->verificarAdministrador();
        $datos = $request->validate([
            'name' => ['required', 'string', 'max:120', 'unique:communities,name'],
            'district' => ['nullable', 'string', 'max:120'],
            'province' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:2000'],
        ]);
        Comunidad::create($datos);

        return back()->with('success', 'La comunidad quedó registrada.');
    }

    public function crearAlerta(): View
    {
        $this->verificarAdministrador();

        return view('administracion.crear-alerta', ['comunidades' => Comunidad::orderBy('name')->get()]);
    }

    public function guardarAlerta(Request $request): RedirectResponse
    {
        $this->verificarAdministrador();
        $datos = $request->validate([
            'title' => ['required', 'string', 'max:160'],
            'message' => ['required', 'string', 'max:3000'],
            'level' => ['required', 'in:informacion,precaucion,urgente'],
            'community_id' => ['nullable', 'exists:communities,id'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
        ]);
        AlertaHelada::create([
            ...$datos,
            'created_by' => Auth::id(),
            'status' => 'publicada',
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
        $datos = $request->validate(['status' => ['required', 'in:revisado,descartado']]);
        $reporteRiesgo->update($datos);

        return back()->with('success', 'Se actualizó el estado del reporte.');
    }
}
