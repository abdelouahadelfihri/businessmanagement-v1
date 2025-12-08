<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use App\Models\Sales\DeliveryLine;
use Illuminate\Http\Request;

class DeliveryLineController extends Controller
{
    public function index()
    {
        return DeliveryLine::with(['delivery', 'product'])->get();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'delivery_id' => 'required|exists:deliveries,id',
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|numeric|min:1',
            'unit_price' => 'required|numeric|min:0',
            'subtotal' => 'required|numeric|min:0',
        ]);

        $line = DeliveryLine::create($validated);

        return response()->json($line, 201);
    }

    public function show($id)
    {
        return DeliveryLine::with(['delivery', 'product'])->findOrFail($id);
    }

    public function update(Request $request, $id)
    {
        $line = DeliveryLine::findOrFail($id);

        $validated = $request->validate([
            'delivery_id' => 'sometimes|exists:deliveries,id',
            'product_id' => 'sometimes|exists:products,id',
            'quantity' => 'sometimes|numeric|min:1',
            'unit_price' => 'sometimes|numeric|min:0',
            'subtotal' => 'sometimes|numeric|min:0',
        ]);

        $line->update($validated);

        return response()->json($line);
    }

    public function destroy($id)
    {
        $line = DeliveryLine::findOrFail($id);
        $line->delete();

        return response()->json(['message' => 'Deleted successfully']);
    }
}