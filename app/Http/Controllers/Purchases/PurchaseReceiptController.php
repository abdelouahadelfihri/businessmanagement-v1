<?php

namespace App\Http\Controllers\Purchases;

use Illuminate\Http\Request;
use App\Models\Purchases\PurchaseReceipt;
use App\Models\MasterData\StockMovement;
use App\Models\MasterData\Warehouse;
use App\Traits\StockHelper;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
class PurchaseReceiptController extends Controller
{

    use StockHelper;

    public function create()
    {
        $warehouses = Warehouse::all();

        return view('documents.create', [
            'route' => route('purchase-receipts.store'),
            'warehouses' => $warehouses,
            'partyLabel' => 'Supplier',
            'partyField' => 'supplier_id',
            'withPrice' => true,
            'dateField' => 'receipt_date',
            'title' => 'Create Purchase Receipt',
        ]);
    }

    public function store(Request $request)
    {
        DB::transaction(function () use ($request) {

            $warehouseId = $request->warehouse_id;

            $model = PurchaseReceipt::create(
                $request->only('supplier_id', 'receipt_date', 'status', 'warehouse_id')
            );

            foreach ($request->lines as $line) {

                $model->lines()->create($line);

                StockMovement::create([
                    'product_id' => $line['product_id'],
                    'warehouse_id' => $warehouseId,
                    'type' => 'in',
                    'quantity' => $line['quantity'],
                    'reason' => 'purchase_receipt',
                    'date' => now(),
                    'source_type' => PurchaseReceipt::class,
                    'source_id' => $model->id,
                ]);

                $this->updateStock(
                    $line['product_id'],
                    $warehouseId,
                    $line['quantity'],
                    'in'
                );
            }

        });

        return redirect()->route('purchase-receipts.index');
    }

    public function edit($id)
    {
        $model = PurchaseReceipt::with('lines.product', 'supplier')->findOrFail($id);
        $warehouses = Warehouse::all();

        return view('documents.edit', [
            'model' => $model,
            'route' => route('purchase-receipts.update', $id),
            'warehouses' => $warehouses,
            'partyLabel' => 'Supplier',
            'partyField' => 'supplier_id',
            'withPrice' => true,
            'dateField' => 'receipt_date',
            'title' => 'Edit Purchase Receipt'
        ]);
    }

    public function update(Request $request, $id)
    {
        DB::transaction(function () use ($request, $id) {

            $model = PurchaseReceipt::with('lines')->findOrFail($id);

            $newWarehouse = $request->warehouse_id;
            $oldWarehouse = $model->warehouse_id;

            // 🔴 reverse old stock from OLD warehouse
            foreach ($model->lines as $line) {
                $this->updateStock(
                    $line->product_id,
                    $oldWarehouse,
                    $line->quantity,
                    'out'
                );
            }

            StockMovement::where('source_type', PurchaseReceipt::class)
                ->where('source_id', $model->id)
                ->delete();

            $model->lines()->delete();

            $model->update(
                $request->only('supplier_id', 'receipt_date', 'status', 'warehouse_id')
            );

            // 🟢 apply new stock to NEW warehouse
            foreach ($request->lines as $line) {

                $model->lines()->create($line);

                StockMovement::create([
                    'product_id' => $line['product_id'],
                    'warehouse_id' => $newWarehouse,
                    'type' => 'in',
                    'quantity' => $line['quantity'],
                    'reason' => 'purchase_receipt',
                    'date' => now(),
                    'source_type' => PurchaseReceipt::class,
                    'source_id' => $model->id,
                ]);

                $this->updateStock(
                    $line['product_id'],
                    $newWarehouse,
                    $line['quantity'],
                    'in'
                );
            }

        });

        return redirect()->route('purchase-receipts.index');
    }
}

// Done