<?php

namespace App\Http\Controllers\Purchases;

use App\Http\Controllers\Controller;
use App\Models\Purchases\PurchaseReceipt;
use App\Models\Purchases\PurchaseOrder;
use Illuminate\Http\Request;

class PurchaseReceiptController extends Controller
{
    public function index(Request $request)
    {
        $receipts = PurchaseReceipt::with('purchaseOrder.supplier')->paginate(12);
        $selectFor = $request->query('select_for');
        $returnUrl = $request->query('return_url');
        return view('purchasereceipts.index', compact('receipts', 'selectFor', 'returnUrl'));
    }

    public function create(Request $request)
    {
        if (!$request->hasAny(['receipt_date', 'receipt_number', 'status', 'selected_order_id'])) {
            session()->forget('purchase_receipt_form');
        }

        $form = array_merge(
            session('purchase_receipt_form', []),
            $request->only(['receipt_date', 'receipt_number', 'status', 'selected_order_id'])
        );
        session(['purchase_receipt_form' => $form]);

        $selectedOrder = $form['selected_order_id'] ?? null ? PurchaseOrder::with('supplier')->find($form['selected_order_id']) : null;

        return view('purchasereceipts.create', compact('form', 'selectedOrder'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'order_id' => 'required|exists:purchase_orders,id',
            'receipt_number' => 'required|string',
            'receipt_date' => 'required|date',
            'status' => 'required|in:draft,received,closed'
        ]);

        PurchaseReceipt::create([
            'purchase_order_id' => $data['order_id'],
            'supplier_id' => PurchaseOrder::find($data['order_id'])->supplier_id,
            'receipt_number' => $data['receipt_number'],
            'date' => $data['receipt_date'],
            'status' => $data['status']
        ]);

        session()->forget('purchase_receipt_form');
        return redirect()->route('purchasereceipts.index')->with('success', 'Purchase receipt created successfully.');
    }

    public function edit(PurchaseReceipt $purchasereceipt)
    {
        $form = [
            'receipt_number' => $purchasereceipt->receipt_number,
            'receipt_date' => $purchasereceipt->date,
            'status' => $purchasereceipt->status,
            'selected_order_id' => $purchasereceipt->purchase_order_id
        ];
        $selectedOrder = $purchasereceipt->purchaseOrder()->with('supplier')->first();
        return view('purchasereceipts.edit', compact('purchasereceipt', 'form', 'selectedOrder'));
    }

    public function update(Request $request, PurchaseReceipt $purchasereceipt)
    {
        $data = $request->validate([
            'order_id' => 'required|exists:purchase_orders,id',
            'receipt_number' => 'required|string',
            'receipt_date' => 'required|date',
            'status' => 'required|in:draft,received,closed'
        ]);

        $purchasereceipt->update([
            'purchase_order_id' => $data['order_id'],
            'supplier_id' => PurchaseOrder::find($data['order_id'])->supplier_id,
            'receipt_number' => $data['receipt_number'],
            'date' => $data['receipt_date'],
            'status' => $data['status']
        ]);

        return redirect()->route('purchasereceipts.index')->with('success', 'Purchase receipt updated successfully.');
    }

    public function destroy(PurchaseReceipt $purchasereceipt)
    {
        $purchasereceipt->delete();
        return redirect()->route('purchasereceipts.index')->with('success', 'Purchase receipt deleted successfully.');
    }
}