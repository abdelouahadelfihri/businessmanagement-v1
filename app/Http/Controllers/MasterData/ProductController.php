<?php

namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;
use App\Models\MasterData\Product;
use App\Models\MasterData\Category;
use App\Models\MasterData\Unit;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Display a listing of products.
     */
    public function index()
    {
        $products = Product::with(['category', 'unit'])->get();

        return view('products.index', compact('products'));
    }

    /**
     * Show the form for creating a new product.
     */
    public function create()
    {
        $categories = Category::all();
        $units = Unit::all();

        return view('products.create', compact('categories', 'units'));
    }

    /**
     * Store a newly created product.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:100|unique:products,code',
            'bar_code' => 'nullable|string|max:100|unique:products,bar_code',
            'category' => 'required|exists:categories,id',
            'unit' => 'required|exists:units,unit_id',
            'reorder_level' => 'nullable|integer|min:0',
            'is_active' => 'required|boolean',
        ]);

        Product::create($data);

        return redirect()
            ->route('products.index')
            ->with('success', 'Product created successfully.');
    }

    /**
     * Show the form for editing the specified product.
     */
    public function edit(Product $product)
    {
        $categories = Category::all();
        $units = Unit::all();

        return view('products.edit', compact('product', 'categories', 'units'));
    }

    /**
     * Update the specified product.
     */
    public function update(Request $request, Product $product)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:100|unique:products,code,' . $product->id,
            'bar_code' => 'nullable|string|max:100|unique:products,bar_code,' . $product->id,
            'category' => 'required|exists:categories,id',
            'unit' => 'required|exists:units,unit_id',
            'reorder_level' => 'nullable|integer|min:0',
            'is_active' => 'required|boolean',
        ]);

        $product->update($data);

        return redirect()
            ->route('products.index')
            ->with('success', 'Product updated successfully.');
    }

    /**
     * Remove the specified product.
     */
    public function destroy(Product $product)
    {
        $product->delete();

        return redirect()
            ->route('products.index')
            ->with('success', 'Product deleted successfully.');
    }
}