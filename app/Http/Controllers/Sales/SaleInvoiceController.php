<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use App\Models\Sales\SalesInvoice;
use Illuminate\Http\Request;

class SaleInvoiceController extends Controller
{
    public function index()
    {
        return SalesInvoice::with(['customer', 'salesOrder'])->get();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'sales_order_id' => 'required|exists:sales_orders,id',
            'customer_id' => 'required|exists:customers,id',
            'invoice_number' => 'required|unique:sales_invoices,invoice_number',
            'date' => 'required|date',
            'subtotal' => 'required|numeric',
            'tax' => 'required|numeric',
            'total' => 'required|numeric',
            'status' => 'nullable|string',
        ]);

        $invoice = SalesInvoice::create($validated);
        return response()->json($invoice, 201);
    }

    public function show($id)
    {
        return SalesInvoice::with(['customer', 'salesOrder'])->findOrFail($id);
    }

    public function update(Request $request, $id)
    {
        $invoice = SalesInvoice::findOrFail($id);

        $validated = $request->validate([
            'sales_order_id' => 'sometimes|exists:sales_orders,id',
            'customer_id' => 'sometimes|exists:customers,id',
            'invoice_number' => 'sometimes|unique:sales_invoices,invoice_number,' . $id,
            'date' => 'sometimes|date',
            'subtotal' => 'sometimes|numeric',
            'tax' => 'sometimes|numeric',
            'total' => 'sometimes|numeric',
            'status' => 'nullable|string',
        ]);

        $invoice->update($validated);

        return response()->json($invoice);
    }

    public function destroy($id)
    {
        $invoice = SalesInvoice::findOrFail($id);
        $invoice->delete();

        return response()->json(['message' => 'Deleted successfully']);
    }
}