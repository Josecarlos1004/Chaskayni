<?php

namespace App\Http\Controllers;

use App\Models\FrostAlert;
use App\Models\Order;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $user = Auth::user();

        return view('dashboard', [
            'productsCount' => $user->products()->count(),
            'ordersCount' => $user->purchases()->count(),
            'alertsCount' => FrostAlert::where('status', 'publicada')->count(),
            'recentOrders' => Order::with('items.product')
                ->where('buyer_id', $user->id)
                ->latest()
                ->take(5)
                ->get(),
        ]);
    }
}