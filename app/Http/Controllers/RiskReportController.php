<?php

namespace App\Http\Controllers;

use App\Models\RiskReport;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class RiskReportController extends Controller
{
    public function create(): View
    {
        return view('risks.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:160'],
            'description' => ['required', 'string', 'max:3000'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
        ]);

        RiskReport::create([
            ...$data,
            'reporter_id' => Auth::id(),
            'community_id' => Auth::user()->community_id,
        ]);

        return redirect()->route('dashboard')->with('success', 'Gracias. Tu reporte quedó pendiente de revisión.');
    }
}