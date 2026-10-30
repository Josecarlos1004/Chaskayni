<?php

namespace App\Http\Controllers;

use App\Models\AlertaHelada;
use Illuminate\View\View;

class AlertasHeladasController extends Controller
{
    public function index(): View
    {
        return view('alertas.indice', [
            'alertas' => AlertaHelada::with(['comunidad', 'autor'])
                ->where('estado', 'publicada')
                ->latest()
                ->paginate(10),
        ]);
    }
}
