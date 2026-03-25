<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Purchases\PurchaseReceipt;
use App\Models\Purchases\PurchaseOrder;

class PurchaseReceiptController extends Controller
{
    public function create(Request $request)
    {
        $source = null;

        // Optionally copy lines from Purchase Order
        if ($request->source_type == 'purchase_order') {
            $source = PurchaseOrder::with('lines.product')->find($request->source_id);
        }

        return view('documents.create', [
            'route' => route('purchase-receipts.store'),
            'partyLabel' => 'Supplier',
            'partyField' => 'supplier_id',
            'withPrice' => true,
            'dateField' => 'receipt_date',
            'title' => 'Create Purchase Receipt',
            'source' => $source
        ]);
    }

    public function store(Request $request)
    {
        $model = PurchaseReceipt::create($request->only('supplier_id', 'receipt_date', 'status'));

        $total = 0;

        foreach ($request->lines as $line) {
            $total += ($line['quantity'] ?? 0) * ($line['unit_price'] ?? 0);
            $model->lines()->create($line);
        }

        $model->update(['total_amount' => $total]);

        return redirect()->route('purchase-receipts.index');
    }

    public function edit($id)
    {
        $model = PurchaseReceipt::with('lines.product', 'supplier')->findOrFail($id);

        return view('documents.edit', [
            'model' => $model,
            'route' => route('purchase-receipts.update', $id),
            'partyLabel' => 'Supplier',
            'partyField' => 'supplier_id',
            'withPrice' => true,
            'dateField' => 'receipt_date',
            'title' => 'Edit Purchase Receipt'
        ]);
    }

    public function update(Request $request, $id)
    {
        $model = PurchaseReceipt::findOrFail($id);

        $model->update($request->only('supplier_id', 'receipt_date', 'status'));

        $model->lines()->delete();

        $total = 0;

        foreach ($request->lines as $line) {
            $total += ($line['quantity'] ?? 0) * ($line['unit_price'] ?? 0);
            $model->lines()->create($line);
        }

        $model->update(['total_amount' => $total]);

        return redirect()->route('purchase-receipts.index');
    }
}

// Done