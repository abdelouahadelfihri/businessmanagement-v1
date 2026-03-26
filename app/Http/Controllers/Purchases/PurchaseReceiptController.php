<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Purchases\PurchaseReceipt;
use App\Models\Purchases\PurchaseOrder;
use App\Models\MasterData\StockMovement;

class PurchaseReceiptController extends Controller
{
    public function create(Request $request)
    {
        $source = null;

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
        $model = PurchaseReceipt::create(
            $request->only('supplier_id', 'receipt_date', 'status')
        );

        foreach ($request->lines as $line) {

            $model->lines()->create($line);

            // STOCK IN
            StockMovement::create([
                'product_id' => $line['product_id'],
                'quantity' => $line['quantity'],
                'type' => 'in',
                'reference_type' => 'purchase_receipt',
                'reference_id' => $model->id,
            ]);
        }

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

        // DELETE OLD STOCK
        StockMovement::where('reference_type', 'purchase_receipt')
            ->where('reference_id', $model->id)
            ->delete();

        $model->lines()->delete();

        foreach ($request->lines as $line) {

            $model->lines()->create($line);

            // RECREATE STOCK IN
            StockMovement::create([
                'product_id' => $line['product_id'],
                'quantity' => $line['quantity'],
                'type' => 'in',
                'reference_type' => 'purchase_receipt',
                'reference_id' => $model->id,
            ]);
        }

        return redirect()->route('purchase-receipts.index');
    }
}

// Done