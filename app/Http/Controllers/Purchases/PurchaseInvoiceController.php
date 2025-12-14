<?php

namespace App\Http\Controllers\Purchases;

use App\Http\Controllers\Controller;
use App\Models\Purchases\PurchaseInvoice;
use App\Models\Purchases\PurchaseOrder;
use App\Models\Purchases\Supplier;
use Illuminate\Http\Request;

class PurchaseInvoiceController extends Controller
{
    public function index()
    {
        $invoices = PurchaseInvoice::with(['supplier', 'purchaseOrder'])
            ->paginate(12);

        return view('purchaseinvoices.index', compact('invoices'));
    }

    public function create(Request $request)
    {
        $suppliers = Supplier::all();
        $orders    = PurchaseOrder::all();

        // Optional preselected IDs
        $selectedSupplierId = $request->query('selected_supplier_id');
        $selectedOrderId    = $request->query('selected_purchase_order_id');

        return view('purchaseinvoices.create', compact(
            'suppliers',
            'orders',
            'selectedSupplierId',
            'selectedOrderId'
        ));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'purchase_order_id' => 'required|exists:purchase_orders,id',
            'supplier_id'       => 'required|exists:suppliers,id',
            'invoice_number'    => 'required|string|max:100|unique:purchase_invoices,invoice_number',
            'date'              => 'required|date',
            'subtotal'          => 'required|numeric|min:0',
            'tax'               => 'nullable|numeric|min:0',
            'total'             => 'required|numeric|min:0',
            'status'            => 'required|string|max:50',
        ]);

        PurchaseInvoice::create($data);

        return redirect()
            ->route('purchase-invoices.index')
            ->with('success', 'Purchase invoice created.');
    }

    public function edit(PurchaseInvoice $purchaseInvoice, Request $request)
    {
        $suppliers = Supplier::all();
        $orders    = PurchaseOrder::all();

        $selectedSupplierId = $request->query('selected_supplier_id')
            ?? $purchaseInvoice->supplier_id;

        $selectedOrderId = $request->query('selected_purchase_order_id')
            ?? $purchaseInvoice->purchase_order_id;

        return view('purchaseinvoices.edit', compact(
            'purchaseInvoice',
            'suppliers',
            'orders',
            'selectedSupplierId',
            'selectedOrderId'
        ));
    }

    public function update(Request $request, PurchaseInvoice $purchaseInvoice)
    {
        $data = $request->validate([
            'purchase_order_id' => 'required|exists:purchase_orders,id',
            'supplier_id'       => 'required|exists:suppliers,id',
            'invoice_number'    => 'required|string|max:100|unique:purchase_invoices,invoice_number,' . $purchaseInvoice->id,
            'date'              => 'required|date',
            'subtotal'          => 'required|numeric|min:0',
            'tax'               => 'nullable|numeric|min:0',
            'total'             => 'required|numeric|min:0',
            'status'            => 'required|string|max:50',
        ]);

        $purchaseInvoice->update($data);

        return redirect()
            ->route('purchase-invoices.index')
            ->with('success', 'Purchase invoice updated.');
    }
}