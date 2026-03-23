<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Purchases\PurchaseOrder;
use App\Models\Purchases\PurchaseRequest;

class PurchaseOrderController extends Controller
{
    public function create(Request $request)
    {
        $source = null;

        if ($request->source_type == 'purchase_request') {
            $source = PurchaseRequest::with('lines.product')->find($request->source_id);
        }

        return view('documents.create', [
            'route' => route('purchase-orders.store'),
            'partyLabel' => 'Supplier',
            'partyField' => 'supplier_id',
            'withPrice' => true,
            'dateField' => 'order_date',
            'title' => 'Create Purchase Order',
            'source' => $source
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'order_date' => 'required|date',
            'status' => 'required',
            'lines' => 'required|array|min:1',
            'lines.*.product_id' => 'required|exists:products,id',
            'lines.*.quantity' => 'required|numeric|min:1',
            'lines.*.unit_price' => 'required|numeric|min:0',
        ]);

        $po = PurchaseOrder::create([
            'supplier_id' => $data['supplier_id'],
            'order_date' => $data['order_date'],
            'status' => $data['status'],
        ]);

        $total = 0;

        foreach ($data['lines'] as $line) {
            $total += $line['quantity'] * $line['unit_price'];

            $po->lines()->create($line);
        }

        $po->update(['total_amount' => $total]);

        return redirect()->route('purchase-orders.index');
    }

    public function edit($id)
    {
        $model = PurchaseOrder::with('lines.product', 'supplier')->findOrFail($id);

        return view('documents.edit', [
            'model' => $model,
            'route' => route('purchase-orders.update', $id),
            'partyLabel' => 'Supplier',
            'partyField' => 'supplier_id',
            'withPrice' => true,
            'dateField' => 'order_date',
            'title' => 'Edit Purchase Order'
        ]);
    }

    public function update(Request $request, $id)
    {
        $model = PurchaseOrder::findOrFail($id);

        $data = $request->validate([
            'supplier_id' => 'required',
            'order_date' => 'required|date',
            'status' => 'required',
            'lines' => 'required|array|min:1',
        ]);

        $model->update($data);

        $model->lines()->delete();

        $total = 0;

        foreach ($request->lines as $line) {
            $total += $line['quantity'] * ($line['unit_price'] ?? 0);
            $model->lines()->create($line);
        }

        $model->update(['total_amount' => $total]);

        return redirect()->route('purchase-orders.index');
    }
}