<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use App\Models\Sales\Delivery;
use Illuminate\Http\Request;

class DeliveryController extends Controller
{
    public function index()
    {
        return Delivery::with('salesOrder')->get();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'sales_order_id' => 'required|exists:sales_orders,id',
            'delivery_number' => 'required|string|unique:deliveries,delivery_number',
            'date' => 'required|date',
            'status' => 'nullable|string',
            'total' => 'required|numeric'
        ]);

        $delivery = Delivery::create($validated);

        return response()->json($delivery, 201);
    }

    public function show($id)
    {
        return Delivery::with('salesOrder')->findOrFail($id);
    }

    public function update(Request $request, $id)
    {
        $delivery = Delivery::findOrFail($id);

        $validated = $request->validate([
            'sales_order_id' => 'sometimes|exists:sales_orders,id',
            'delivery_number' => 'sometimes|string|unique:deliveries,delivery_number,' . $id,
            'date' => 'sometimes|date',
            'status' => 'sometimes|string',
            'total' => 'sometimes|numeric'
        ]);

        $delivery->update($validated);

        return response()->json($delivery);
    }

    public function destroy($id)
    {
        $delivery = Delivery::findOrFail($id);
        $delivery->delete();

        return response()->json(['message' => 'Deleted successfully']);
    }
}