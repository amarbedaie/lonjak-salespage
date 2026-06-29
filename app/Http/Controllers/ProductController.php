<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProductController extends Controller
{
    public function index()
    {
        $products = Auth::user()->products()->latest()->get();

        return view('dashboard.products', compact('products'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'sku' => 'nullable|string|max:100',
            'price' => 'required|numeric|min:0',
            'cost' => 'nullable|numeric|min:0',
            'stock' => 'nullable|integer|min:0',
        ]);

        Auth::user()->products()->create([
            'name' => $data['name'],
            'sku' => $data['sku'] ?? null,
            'price' => $data['price'],
            'cost' => $data['cost'] ?? 0,
            'stock' => $data['stock'] ?? 0,
            'image' => '📦',
            'status' => 'aktif',
        ]);

        return back()->with('ok', 'Produk ditambah.');
    }
}
