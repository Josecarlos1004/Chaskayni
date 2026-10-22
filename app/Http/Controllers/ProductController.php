<?php

namespace App\Http\Controllers;

use App\Models\Community;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ProductController extends Controller
{
    private function ensureProducer(): void
    {
        abort_unless(Auth::user()->isProducer(), 403, 'Solo productores pueden administrar publicaciones.');
    }

    public function create(): View
    {
        $this->ensureProducer();

        return view('products.form', [
            'product' => new Product(),
            'communities' => Community::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->ensureProducer();
        $data = $this->validated($request);
        $data['user_id'] = Auth::id();
        $data['community_id'] = $data['community_id'] ?? Auth::user()->community_id;
        $data['status'] = 'disponible';

        Product::create($data);

        return redirect()->route('market.index')->with('success', 'Publicaste un producto en el mercado.');
    }

    public function edit(Product $product): View
    {
        $this->authorizeOwner($product);

        return view('products.form', [
            'product' => $product,
            'communities' => Community::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $this->authorizeOwner($product);
        $product->update($this->validated($request));

        return redirect()->route('market.index')->with('success', 'La publicación se actualizó.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        $this->authorizeOwner($product);

        if ($product->orderItems()->exists()) {
            $product->update(['status' => 'oculto']);
        } else {
            $product->delete();
        }

        return redirect()->route('market.index')->with('success', 'La publicación dejó de mostrarse en el mercado.');
    }

    private function authorizeOwner(Product $product): void
    {
        abort_unless(Auth::user()->isProducer(), 403);
        abort_unless($product->user_id === Auth::id() || Auth::user()->isAdministrator(), 403);
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:140'],
            'category' => ['nullable', 'string', 'max:80'],
            'description' => ['nullable', 'string', 'max:3000'],
            'quantity' => ['required', 'numeric', 'gt:0', 'max:99999999'],
            'unit' => ['required', 'in:kg,arroba,saco,unidad,litro'],
            'unit_price' => ['required', 'numeric', 'min:0.01', 'max:99999999'],
            'image_url' => ['nullable', 'url:http,https', 'max:2048'],
            'community_id' => ['nullable', 'exists:communities,id'],
        ]);
    }
}