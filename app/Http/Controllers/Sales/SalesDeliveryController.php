<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use App\Models\Sales\SalesDelivery;
use Illuminate\Http\Request;

class SalesDeliveryController extends Controller
{
    public function index()
    {
        return response()->json(
            SalesDelivery::with('salesOrder')->get()
        );
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'sales_order_id' => 'required|exists:sales_orders,id',
            'delivery_number' => 'required|string|unique:sales_deliveries',
            'date' => 'required|date',
            'status' => 'nullable|string',
            'total' => 'required|numeric'
        ]);

        $delivery = SalesDelivery::create($validated);

        return response()->json($delivery, 201);
    }

    public function show($id)
    {
        return response()->json(
            SalesDelivery::with('salesOrder')->findOrFail($id)
        );
    }

    public function update(Request $request, $id)
    {
        $delivery = SalesDelivery::findOrFail($id);

        $validated = $request->validate([
            'sales_order_id' => 'sometimes|exists:sales_orders,id',
            'delivery_number' => 'sometimes|string|unique:sales_deliveries,delivery_number,' . $id,
            'date' => 'sometimes|date',
            'status' => 'sometimes|string',
            'total' => 'sometimes|numeric'
        ]);

        $delivery->update($validated);

        return response()->json($delivery);
    }

    public function destroy($id)
    {
        $delivery = SalesDelivery::findOrFail($id);
        $delivery->delete();

        return response()->json(['message' => 'Deleted successfully']);
    }
}