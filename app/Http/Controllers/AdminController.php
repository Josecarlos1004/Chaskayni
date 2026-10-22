<?php

namespace App\Http\Controllers;

use App\Models\Community;
use App\Models\FrostAlert;
use App\Models\RiskReport;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AdminController extends Controller
{
    private function ensureAdministrator(): void
    {
        abort_unless(Auth::user()->isAdministrator(), 403, 'Esta sección es solo para administración.');
    }

    public function communities(): View
    {
        $this->ensureAdministrator();

        return view('admin.communities', [
            'communities' => Community::withCount(['users', 'products'])->orderBy('name')->get(),
        ]);
    }

    public function storeCommunity(Request $request): RedirectResponse
    {
        $this->ensureAdministrator();
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120', 'unique:communities,name'],
            'district' => ['nullable', 'string', 'max:120'],
            'province' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:2000'],
        ]);
        Community::create($data);

        return back()->with('success', 'La comunidad quedó registrada.');
    }

    public function createAlert(): View
    {
        $this->ensureAdministrator();

        return view('admin.alert-create', ['communities' => Community::orderBy('name')->get()]);
    }

    public function storeAlert(Request $request): RedirectResponse
    {
        $this->ensureAdministrator();
        $data = $request->validate([
            'title' => ['required', 'string', 'max:160'],
            'message' => ['required', 'string', 'max:3000'],
            'level' => ['required', 'in:informacion,precaucion,urgente'],
            'community_id' => ['nullable', 'exists:communities,id'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
        ]);
        FrostAlert::create([
            ...$data,
            'created_by' => Auth::id(),
            'status' => 'publicada',
        ]);

        return redirect()->route('alerts.index')->with('success', 'La alerta fue publicada por administración.');
    }

    public function reports(): View
    {
        $this->ensureAdministrator();

        return view('admin.reports', [
            'reports' => RiskReport::with(['reporter', 'community'])->latest()->paginate(15),
        ]);
    }

    public function reviewReport(Request $request, RiskReport $riskReport): RedirectResponse
    {
        $this->ensureAdministrator();
        $data = $request->validate(['status' => ['required', 'in:revisado,descartado']]);
        $riskReport->update($data);

        return back()->with('success', 'Se actualizó el estado del reporte.');
    }
}