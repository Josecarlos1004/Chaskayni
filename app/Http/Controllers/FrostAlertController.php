<?php

namespace App\Http\Controllers;

use App\Models\FrostAlert;
use Illuminate\View\View;

class FrostAlertController extends Controller
{
    public function index(): View
    {
        return view('alerts.index', [
            'alerts' => FrostAlert::with(['community', 'author'])
                ->where('status', 'publicada')
                ->latest()
                ->paginate(10),
        ]);
    }
}