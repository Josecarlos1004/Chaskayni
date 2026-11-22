<?php

namespace App\Http\Controllers;

use App\Models\AlertaHelada;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AlertasHeladasController extends Controller
{
    public function index(): View
    {
        return view('alertas.indice', [
            'alertas' => AlertaHelada::activas()
                ->with(['comunidad', 'autor', 'reporteOrigen'])
                ->latest()
                ->paginate(10),
        ]);
    }

    /** Entrega la foto solo si la alerta continúa vigente y publicada. */
    public function verFotoPublica(AlertaHelada $alertaHelada): StreamedResponse
    {
        abort_unless($alertaHelada->foto && AlertaHelada::activas()->whereKey($alertaHelada->id)->exists(), 404);
        abort_unless(Storage::disk('public')->exists($alertaHelada->foto), 404);

        return Storage::disk('public')->response($alertaHelada->foto, 'foto-alerta-'.$alertaHelada->id, [
            'Cache-Control' => 'public, max-age=300, must-revalidate',
            'X-Content-Type-Options' => 'nosniff',
        ], 'inline');
    }
}
