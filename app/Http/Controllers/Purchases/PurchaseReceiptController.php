<?php

namespace App\Http\Controllers\Purchases;

use App\Http\Controllers\Controller;
use App\Models\Purchases\PurchaseReceipt;
use Illuminate\Http\Request;

class PurchaseReceiptController extends Controller
{
    public function index()
    {
        return PurchaseReceipt::with(['purchaseOrder', 'supplier'])->get();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'purchase_order_id' => 'required|exists:purchase_orders,id',
            'supplier_id' => 'required|exists:suppliers,id',
            'receipt_number' => 'required|string|unique:purchase_receipts,receipt_number',
            'date' => 'required|date',
            'total' => 'required|numeric',
            'status' => 'nullable|string',
        ]);

        $receipt = PurchaseReceipt::create($validated);

        return response()->json($receipt, 201);
    }

    public function show($id)
    {
        return PurchaseReceipt::with(['purchaseOrder', 'supplier'])->findOrFail($id);
    }

    public function update(Request $request, $id)
    {
        $receipt = PurchaseReceipt::findOrFail($id);

        $validated = $request->validate([
            'purchase_order_id' => 'sometimes|exists:purchase_orders,id',
            'supplier_id' => 'sometimes|exists:suppliers,id',
            'receipt_number' => 'sometimes|string|unique:purchase_receipts,receipt_number,' . $id,
            'date' => 'sometimes|date',
            'total' => 'sometimes|numeric',
            'status' => 'sometimes|string',
        ]);

        $receipt->update($validated);

        return response()->json($receipt);
    }

    public function destroy($id)
    {
        $receipt = PurchaseReceipt::findOrFail($id);
        $receipt->delete();

        return response()->json(['message' => 'Deleted successfully']);
    }
}