<?php

namespace App\Http\Controllers\Purchases;

use App\Http\Controllers\Controller;
use App\Models\Purchase\PurchaseInvoice;
use Illuminate\Http\Request;

class PurchaseInvoiceController extends Controller
{
    public function index()
    {
        return PurchaseInvoice::with(['purchaseOrder', 'supplier'])->get();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'purchase_order_id' => 'required|exists:purchase_orders,id',
            'supplier_id' => 'required|exists:suppliers,id',
            'invoice_number' => 'required|string|unique:purchase_invoices,invoice_number',
            'date' => 'required|date',
            'subtotal' => 'required|numeric',
            'tax' => 'required|numeric',
            'total' => 'required|numeric',
            'status' => 'nullable|string',
        ]);

        $invoice = PurchaseInvoice::create($validated);

        return response()->json($invoice, 201);
    }

    public function show($id)
    {
        return PurchaseInvoice::with(['purchaseOrder', 'supplier'])->findOrFail($id);
    }

    public function update(Request $request, $id)
    {
        $invoice = PurchaseInvoice::findOrFail($id);

        $validated = $request->validate([
            'purchase_order_id' => 'sometimes|exists:purchase_orders,id',
            'supplier_id' => 'sometimes|exists:suppliers,id',
            'invoice_number' => 'sometimes|string|unique:purchase_invoices,invoice_number,' . $id,
            'date' => 'sometimes|date',
            'subtotal' => 'sometimes|numeric',
            'tax' => 'sometimes|numeric',
            'total' => 'sometimes|numeric',
            'status' => 'sometimes|string',
        ]);

        $invoice->update($validated);

        return response()->json($invoice);
    }

    public function destroy($id)
    {
        $invoice = PurchaseInvoice::findOrFail($id);
        $invoice->delete();

        return response()->json(['message' => 'Deleted successfully']);
    }
}