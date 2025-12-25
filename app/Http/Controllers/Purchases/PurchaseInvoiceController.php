<?php

namespace App\Http\Controllers\Purchases;

use App\Http\Controllers\Controller;
use App\Models\Purchases\PurchaseInvoice;
use App\Models\Purchases\PurchaseOrder;
use Illuminate\Http\Request;

class PurchaseInvoiceController extends Controller
{
    public function index(Request $request)
    {
        $invoices = PurchaseInvoice::with('purchaseOrder.supplier')->paginate(12);
        $selectFor = $request->query('select_for');
        $returnUrl = $request->query('return_url');
        return view('purchaseinvoices.index', compact('invoices', 'selectFor', 'returnUrl'));
    }

    public function create(Request $request)
    {
        if (!$request->hasAny(['invoice_number', 'date', 'total', 'paid', 'selected_order_id'])) {
            session()->forget('purchase_invoice_form');
        }

        $form = array_merge(
            session('purchase_invoice_form', []),
            $request->only(['invoice_number', 'date', 'total', 'paid', 'selected_order_id'])
        );
        session(['purchase_invoice_form' => $form]);

        $selectedOrder = $form['selected_order_id'] ?? null ? PurchaseOrder::with('supplier')->find($form['selected_order_id']) : null;

        return view('purchaseinvoices.create', compact('form', 'selectedOrder'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'order_id' => 'required|exists:purchase_orders,id',
            'invoice_number' => 'required|string',
            'date' => 'required|date',
            'total' => 'nullable|numeric',
            'paid' => 'nullable|numeric'
        ]);

        PurchaseInvoice::create([
            'purchase_order_id' => $data['order_id'],
            'supplier_id' => PurchaseOrder::find($data['order_id'])->supplier_id,
            'invoice_number' => $data['invoice_number'],
            'date' => $data['date'],
            'total' => $data['total'] ?? 0,
            'paid' => $data['paid'] ?? 0
        ]);

        session()->forget('purchase_invoice_form');
        return redirect()->route('purchaseinvoices.index')->with('success', 'Purchase invoice created successfully.');
    }

    public function edit(PurchaseInvoice $purchaseinvoice)
    {
        $form = [
            'invoice_number' => $purchaseinvoice->invoice_number,
            'date' => $purchaseinvoice->date,
            'total' => $purchaseinvoice->total,
            'paid' => $purchaseinvoice->paid,
            'selected_order_id' => $purchaseinvoice->purchase_order_id
        ];
        $selectedOrder = $purchaseinvoice->purchaseOrder()->with('supplier')->first();
        return view('purchaseinvoices.edit', compact('purchaseinvoice', 'form', 'selectedOrder'));
    }

    public function update(Request $request, PurchaseInvoice $purchaseinvoice)
    {
        $data = $request->validate([
            'order_id' => 'required|exists:purchase_orders,id',
            'invoice_number' => 'required|string',
            'date' => 'required|date',
            'total' => 'nullable|numeric',
            'paid' => 'nullable|numeric'
        ]);

        $purchaseinvoice->update([
            'purchase_order_id' => $data['order_id'],
            'supplier_id' => PurchaseOrder::find($data['order_id'])->supplier_id,
            'invoice_number' => $data['invoice_number'],
            'date' => $data['date'],
            'total' => $data['total'] ?? 0,
            'paid' => $data['paid'] ?? 0
        ]);

        return redirect()->route('purchaseinvoices.index')->with('success', 'Purchase invoice updated successfully.');
    }

    public function destroy(PurchaseInvoice $purchaseinvoice)
    {
        $purchaseinvoice->delete();
        return redirect()->route('purchaseinvoices.index')->with('success', 'Purchase invoice deleted successfully.');
    }
}