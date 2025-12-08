<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use App\Models\Sales\SalesReturnLine;
use Illuminate\Http\Request;

class SalesReturnLineController extends Controller
{
    public function index()
    {
        return SalesReturnLine::with(['salesReturn', 'product'])->get();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'sales_return_id' => 'required|exists:sales_returns,id',
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|numeric|min:1',
            'unit_price' => 'required|numeric|min:0',
            'subtotal' => 'required|numeric|min:0'
        ]);

        $line = SalesReturnLine::create($validated);

        return response()->json($line, 201);
    }

    public function show($id)
    {
        return SalesReturnLine::with(['salesReturn', 'product'])->findOrFail($id);
    }

    public function update(Request $request, $id)
    {
        $line = SalesReturnLine::findOrFail($id);

        $validated = $request->validate([
            'sales_return_id' => 'sometimes|exists:sales_returns,id',
            'product_id' => 'sometimes|exists:products,id',
            'quantity' => 'sometimes|numeric|min:1',
            'unit_price' => 'sometimes|numeric|min:0',
            'subtotal' => 'sometimes|numeric|min:0'
        ]);

        $line->update($validated);

        return response()->json($line);
    }

    public function destroy($id)
    {
        $line = SalesReturnLine::findOrFail($id);
        $line->delete();

        return response()->json(['message' => 'Deleted successfully']);
    }
}