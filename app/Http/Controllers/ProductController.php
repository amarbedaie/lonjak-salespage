<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    public function index()
    {
        $products = Auth::user()->products()->latest()->get();

        return view('dashboard.products', compact('products'));
    }

    public function create()
    {
        return view('dashboard.product-form', ['product' => new Product]);
    }

    public function edit(Product $product)
    {
        $this->authorizeOwner($product);

        return view('dashboard.product-form', compact('product'));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['images'] = $this->storeImages($request);
        $data['image'] = $data['images'] ? '🖼️' : '📦';
        $data['status'] = 'aktif';

        Auth::user()->products()->create($data);

        return redirect()->route('products.index')->with('ok', 'Produk disimpan dalam pustaka.');
    }

    public function update(Request $request, Product $product)
    {
        $this->authorizeOwner($product);

        $data = $this->validated($request);
        $kept = collect($request->input('keep_images', []));
        $existing = collect($product->images ?? [])->filter(fn ($p) => $kept->contains($p));
        // Delete images the user removed.
        collect($product->images ?? [])->reject(fn ($p) => $kept->contains($p))
            ->each(fn ($p) => Storage::disk('public')->delete($p));

        $data['images'] = $existing->merge($this->storeImages($request))->values()->all();
        $data['image'] = $data['images'] ? '🖼️' : '📦';

        $product->update($data);

        return redirect()->route('products.index')->with('ok', 'Produk dikemas kini.');
    }

    public function destroy(Product $product)
    {
        $this->authorizeOwner($product);
        collect($product->images ?? [])->each(fn ($p) => Storage::disk('public')->delete($p));
        $product->delete();

        return redirect()->route('products.index')->with('ok', 'Produk dipadam.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => 'required|string|max:255',
            'sku' => 'nullable|string|max:100',
            'price' => 'required|numeric|min:0',
            'compare_price' => 'nullable|numeric|min:0',
            'cost' => 'nullable|numeric|min:0',
            'stock' => 'nullable|integer|min:0',
            'category' => 'nullable|string|max:100',
            'description' => 'nullable|string|max:2000',
            'audience' => 'nullable|string|max:255',
            'problem' => 'nullable|string|max:1000',
            'benefits' => 'nullable|string|max:1000',
            'tone' => 'nullable|string|max:50',
            'video_url' => 'nullable|url|max:500',
            'images.*' => 'nullable|image|max:5120', // 5MB each
        ]);
    }

    /** @return list<string> */
    private function storeImages(Request $request): array
    {
        $paths = [];
        foreach ((array) $request->file('images', []) as $file) {
            if ($file) {
                $paths[] = $file->store('products/'.Auth::id(), 'public');
            }
        }

        return $paths;
    }

    private function authorizeOwner(Product $product): void
    {
        abort_unless($product->user_id === Auth::id(), 403);
    }
}
