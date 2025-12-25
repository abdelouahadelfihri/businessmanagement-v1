<?php

namespace App\Http\Controllers\Purchases;

use App\Http\Controllers\Controller;
use App\Models\Purchases\PurchaseOrder;
use App\Models\MasterData\Supplier;
use App\Models\Purchases\PurchaseRequest;
use Illuminate\Http\Request;

class PurchaseOrderController extends Controller
{
    public function index(Request $request)
    {
        $orders = PurchaseOrder::with('supplier', 'request')->paginate(12);
        $selectFor = $request->query('select_for');
        $returnUrl = $request->query('return_url');
        return view('purchaseorders.index', compact('orders', 'selectFor', 'returnUrl'));
    }

    public function create(Request $request)
    {
        if (!$request->hasAny(['order_date', 'status', 'selected_supplier_id', 'selected_request_id'])) {
            session()->forget('purchase_order_form');
        }

        $form = array_merge(
            session('purchase_order_form', []),
            $request->only(['order_date', 'status', 'selected_supplier_id', 'selected_request_id'])
        );
        session(['purchase_order_form' => $form]);

        $selectedSupplier = $form['selected_supplier_id'] ?? null ? Supplier::find($form['selected_supplier_id']) : null;
        $selectedRequest = $form['selected_request_id'] ?? null ? PurchaseRequest::find($form['selected_request_id']) : null;

        return view('purchaseorders.create', compact('form', 'selectedSupplier', 'selectedRequest'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'request_id' => 'nullable|exists:purchases_requests,id',
            'order_date' => 'required|date',
            'status' => 'required|in:draft,sent,partially_received,completed,cancelled'
        ]);

        PurchaseOrder::create([
            'supplier_id' => $data['supplier_id'],
            'request_id' => $data['request_id'] ?? null,
            'order_date' => $data['order_date'],
            'status' => $data['status']
        ]);

        session()->forget('purchase_order_form');
        return redirect()->route('purchaseorders.index')->with('success', 'Purchase order created successfully.');
    }

    public function edit(PurchaseOrder $purchaseorder)
    {
        $form = [
            'order_date' => $purchaseorder->order_date,
            'status' => $purchaseorder->status,
            'selected_supplier_id' => $purchaseorder->supplier_id,
            'selected_request_id' => $purchaseorder->request_id,
        ];
        $selectedSupplier = $purchaseorder->supplier;
        $selectedRequest = $purchaseorder->request;
        return view('purchaseorders.edit', compact('purchaseorder', 'form', 'selectedSupplier', 'selectedRequest'));
    }

    public function update(Request $request, PurchaseOrder $purchaseorder)
    {
        $data = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'request_id' => 'nullable|exists:purchases_requests,id',
            'order_date' => 'required|date',
            'status' => 'required|in:draft,sent,partially_received,completed,cancelled'
        ]);

        $purchaseorder->update([
            'supplier_id' => $data['supplier_id'],
            'request_id' => $data['request_id'] ?? null,
            'order_date' => $data['order_date'],
            'status' => $data['status']
        ]);

        return redirect()->route('purchaseorders.index')->with('success', 'Purchase order updated successfully.');
    }

    public function destroy(PurchaseOrder $purchaseorder)
    {
        $purchaseorder->delete();
        return redirect()->route('purchaseorders.index')->with('success', 'Purchase order deleted successfully.');
    }
}