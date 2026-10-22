<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MarketController extends Controller
{
    public function index(Request $request): View
    {
        $products = Product::with(['seller', 'community'])
            ->where('status', 'disponible')
            ->where('quantity', '>', 0)
            ->when($request->filled('q'), function ($query) use ($request) {
                $term = trim((string) $request->query('q'));
                $query->where(function ($subquery) use ($term) {
                    $subquery->where('name', 'like', "%{$term}%")
                        ->orWhere('category', 'like', "%{$term}%")
                        ->orWhere('description', 'like', "%{$term}%");
                });
            })
            ->when($request->filled('category'), fn ($query) => $query->where('category', $request->query('category')))
            ->latest()
            ->paginate(12)
            ->withQueryString();

        $categories = Product::where('status', 'disponible')
            ->whereNotNull('category')
            ->distinct()
            ->orderBy('category')
            ->pluck('category');

        return view('market.index', compact('products', 'categories'));
    }
}