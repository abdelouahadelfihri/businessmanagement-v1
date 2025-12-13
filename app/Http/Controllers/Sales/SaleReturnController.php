<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use App\Models\Sales\SalesReturn;
use Illuminate\Http\Request;

class SaleReturnController extends Controller
{
    public function index()
    {
        return SalesReturn::with(['customer', 'salesOrder'])->get();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'sales_order_id' => 'required|exists:sales_orders,id',
            'customer_id' => 'required|exists:customers,id',
            'return_number' => 'required|unique:sales_returns,return_number',
            'date' => 'required|date',
            'reason' => 'required|string',
            'subtotal' => 'required|numeric',
            'tax' => 'required|numeric',
            'total' => 'required|numeric',
            'status' => 'nullable|string',
        ]);

        $return = SalesReturn::create($validated);

        return response()->json($return, 201);
    }

    public function show($id)
    {
        return SalesReturn::with(['customer', 'salesOrder'])->findOrFail($id);
    }

    public function update(Request $request, $id)
    {
        $return = SalesReturn::findOrFail($id);

        $validated = $request->validate([
            'sales_order_id' => 'sometimes|exists:sales_orders,id',
            'customer_id' => 'sometimes|exists:customers,id',
            'return_number' => 'sometimes|unique:sales_returns,return_number,' . $id,
            'date' => 'sometimes|date',
            'reason' => 'sometimes|string',
            'subtotal' => 'sometimes|numeric',
            'tax' => 'sometimes|numeric',
            'total' => 'sometimes|numeric',
            'status' => 'nullable|string',
        ]);

        $return->update($validated);

        return response()->json($return);
    }

    public function destroy($id)
    {
        $return = SalesReturn::findOrFail($id);
        $return->delete();

        return response()->json(['message' => 'Deleted successfully']);
    }
}