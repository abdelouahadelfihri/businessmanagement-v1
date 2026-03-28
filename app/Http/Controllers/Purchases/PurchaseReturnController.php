<?php

namespace App\Http\Controllers\Purchases;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Purchases\PurchaseReturn;
use App\Models\Purchases\PurchaseReturnLine;
use App\Models\MasterData\StockMovement;
use App\Models\MasterData\WarehouseStock;

class PurchaseReturnController extends Controller
{
    public function index()
    {
        $returns = PurchaseReturn::with('supplier')->orderBy('date', 'desc')->get();
        return view('documents.purchases.returns.index', compact('returns'));
    }

    public function create()
    {
        return view('documents.purchases.returns.create');
    }

    public function store(Request $request)
    {
        DB::transaction(function () use ($request) {
            $return = PurchaseReturn::create($request->only([
                'purchase_receipt_id',
                'supplier_id',
                'date',
                'warehouse_id',
                'reason',
                'subtotal',
                'tax',
                'total',
                'status'
            ]));

            foreach ($request->lines as $line) {
                $lineModel = PurchaseReturnLine::create([
                    'purchase_return_id' => $return->id,
                    'product_id' => $line['product_id'],
                    'quantity' => $line['quantity'],
                    'unit_price' => $line['unit_price'],
                    'subtotal' => $line['quantity'] * $line['unit_price']
                ]);

                // Stock movement: remove from warehouse (out)
                $this->updateStock($line['product_id'], $request->warehouse_id, $line['quantity'], 'out');

                StockMovement::create([
                    'product_id' => $line['product_id'],
                    'warehouse_id' => $request->warehouse_id,
                    'type' => 'out',
                    'quantity' => $line['quantity'],
                    'reason' => 'Purchase Return',
                    'date' => $request->date,
                    'source_type' => PurchaseReturn::class,
                    'source_id' => $return->id
                ]);
            }
        });

        return redirect()->route('purchasereturns.index')->with('success', 'Purchase return created successfully.');
    }

    public function edit(PurchaseReturn $purchasereturn)
    {
        $purchasereturn->load('lines.product', 'supplier');
        return view('documents.purchases.returns.edit', compact('purchasereturn'));
    }

    public function update(Request $request, PurchaseReturn $purchasereturn)
    {
        DB::transaction(function () use ($request, $purchasereturn) {
            // Reverse old stock first
            foreach ($purchasereturn->lines as $line) {
                $this->updateStock($line->product_id, $purchasereturn->warehouse_id, $line->quantity, 'in');
            }

            $purchasereturn->lines()->delete();
            $purchasereturn->stockMovements()->delete();

            $purchasereturn->update($request->only([
                'purchase_receipt_id',
                'supplier_id',
                'date',
                'warehouse_id',
                'reason',
                'subtotal',
                'tax',
                'total',
                'status'
            ]));

            foreach ($request->lines as $line) {
                $lineModel = PurchaseReturnLine::create([
                    'purchase_return_id' => $purchasereturn->id,
                    'product_id' => $line['product_id'],
                    'quantity' => $line['quantity'],
                    'unit_price' => $line['unit_price'],
                    'subtotal' => $line['quantity'] * $line['unit_price']
                ]);

                // Stock movement
                $this->updateStock($line['product_id'], $request->warehouse_id, $line['quantity'], 'out');

                StockMovement::create([
                    'product_id' => $line['product_id'],
                    'warehouse_id' => $request->warehouse_id,
                    'type' => 'out',
                    'quantity' => $line['quantity'],
                    'reason' => 'Purchase Return',
                    'date' => $request->date,
                    'source_type' => PurchaseReturn::class,
                    'source_id' => $purchasereturn->id
                ]);
            }
        });

        return redirect()->route('purchasereturns.index')->with('success', 'Purchase return updated successfully.');
    }

    public function destroy(PurchaseReturn $purchasereturn)
    {
        DB::transaction(function () use ($purchasereturn) {
            foreach ($purchasereturn->lines as $line) {
                $this->updateStock($line->product_id, $purchasereturn->warehouse_id, $line->quantity, 'in');
            }

            $purchasereturn->lines()->delete();
            $purchasereturn->stockMovements()->delete();
            $purchasereturn->delete();
        });

        return redirect()->route('purchasereturns.index')->with('success', 'Purchase return deleted.');
    }

    private function updateStock($productId, $warehouseId, $qty, $type)
    {
        $stock = WarehouseStock::firstOrCreate(
            ['product_id' => $productId, 'warehouse_id' => $warehouseId],
            ['quantity' => 0]
        );

        if ($type === 'in') {
            $stock->quantity += $qty;
        } else {
            $stock->quantity -= $qty;
        }

        $stock->save();
    }
}