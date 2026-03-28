<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Purchases\PurchaseReceipt;
use App\Models\Purchases\PurchaseOrder;
use App\Models\MasterData\StockMovement;
use App\Traits\StockHelper;

class PurchaseReceiptController extends Controller
{

    use StockHelper;
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

            // STOCK MOVEMENT
            StockMovement::create([
                'product_id' => $line['product_id'],
                'warehouse_id' => 1,
                'type' => 'in',
                'quantity' => $line['quantity'],
                'reason' => 'purchase_receipt',
                'date' => now(),
                'source_type' => PurchaseReceipt::class,
                'source_id' => $model->id,
            ]);

            // UPDATE STOCK
            $this->updateStock($line['product_id'], 1, $line['quantity'], 'in');
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

        // 🔥 reverse old stock first
        foreach ($model->lines as $line) {
            $this->updateStock($line->product_id, 1, $line->quantity, 'out');
        }

        // delete movements
        StockMovement::where('source_type', PurchaseReceipt::class)
            ->where('source_id', $model->id)
            ->delete();

        $model->lines()->delete();

        foreach ($request->lines as $line) {

            $model->lines()->create($line);

            StockMovement::create([
                'product_id' => $line['product_id'],
                'warehouse_id' => 1,
                'type' => 'in',
                'quantity' => $line['quantity'],
                'reason' => 'purchase_receipt',
                'date' => now(),
                'source_type' => PurchaseReceipt::class,
                'source_id' => $model->id,
            ]);

            $this->updateStock($line['product_id'], 1, $line['quantity'], 'in');
        }

        return redirect()->route('purchase-receipts.index');
    }
}

// Done