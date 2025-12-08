<?php

namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;
use App\Models\MasterData\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->json(
            Product::with(['categoryRelation', 'unitRelation'])->get()
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'         => 'required|string|max:255',
            'code'         => 'required|string|max:255',
            'bar_code'     => 'required|string|max:255',
            'category'     => 'required|exists:categories,id',
            'unit'         => 'required|exists:measurement_units,unit_id',
            'reorder_level'=> 'nullable|integer|min:0',
            'is_active'    => 'boolean',
        ]);

        $product = Product::create($validated);

        return response()->json($product, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $product = Product::with(['categoryRelation', 'unitRelation'])->findOrFail($id);
        return response()->json($product);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        $validated = $request->validate([
            'name'         => 'sometimes|required|string|max:255',
            'code'         => 'sometimes|required|string|max:255',
            'bar_code'     => 'sometimes|required|string|max:255',
            'category'     => 'sometimes|required|exists:categories,id',
            'unit'         => 'sometimes|required|exists:measurement_units,unit_id',
            'reorder_level'=> 'nullable|integer|min:0',
            'is_active'    => 'boolean',
        ]);

        $product->update($validated);

        return response()->json($product);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        Product::findOrFail($id)->delete();

        return response()->json(['message' => 'Product deleted successfully']);
    }
}