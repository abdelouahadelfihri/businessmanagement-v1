<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use App\Models\Sales\SalesOrder;
use Illuminate\Http\Request;

class SaleOrderController extends Controller
{
    public function index()
    {
        return response()->json(
            SalesOrder::with('customer')->get()
        );
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id'  => 'required|exists:customers,customerId',
            'order_number' => 'required|string|unique:sales_orders',
            'date'         => 'required|date',
            'total'        => 'required|numeric',
            'status'       => 'nullable|string'
        ]);

        $order = SalesOrder::create($validated);

        return response()->json($order, 201);
    }

    public function show($id)
    {
        return response()->json(
            SalesOrder::with('customer')->findOrFail($id)
        );
    }

    public function update(Request $request, $id)
    {
        $order = SalesOrder::findOrFail($id);

        $validated = $request->validate([
            'customer_id'  => 'sometimes|exists:customers,customerId',
            'order_number' => 'sometimes|string|unique:sales_orders,order_number,' . $id,
            'date'         => 'sometimes|date',
            'total'        => 'sometimes|numeric',
            'status'       => 'sometimes|string'
        ]);

        $order->update($validated);

        return response()->json($order);
    }

    public function destroy($id)
    {
        $order = SalesOrder::findOrFail($id);
        $order->delete();

        return response()->json(['message' => 'Deleted successfully']);
    }
}