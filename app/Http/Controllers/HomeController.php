<?php

namespace App\Http\Controllers;

use App\Models\FrostAlert;
use App\Models\Product;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        return view('home', [
            'products' => Product::with(['seller', 'community'])
                ->where('status', 'disponible')
                ->where('quantity', '>', 0)
                ->latest()
                ->take(6)
                ->get(),
            'alerts' => FrostAlert::with('community')
                ->where('status', 'publicada')
                ->latest()
                ->take(3)
                ->get(),
        ]);
    }
}