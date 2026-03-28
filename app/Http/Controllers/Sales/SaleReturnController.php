<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Sales\SaleReturn;
use App\Models\Sales\SaleReturnLine;
use App\Models\MasterData\StockMovement;
use App\Models\MasterData\WarehouseStock;

class SaleReturnController extends Controller
{
    public function index()
    {
        $returns = SaleReturn::with('customer')->orderBy('date', 'desc')->get();
        return view('documents.sales.returns.index', compact('returns'));
    }

    public function create()
    {
        return view('documents.sales.returns.create');
    }

    public function store(Request $request)
    {
        DB::transaction(function () use ($request) {
            $return = SaleReturn::create($request->only([
                'sales_order_id',
                'customer_id',
                'date',
                'warehouse_id',
                'reason',
                'subtotal',
                'tax',
                'total',
                'status'
            ]));

            foreach ($request->lines as $line) {
                $lineModel = SaleReturnLine::create([
                    'sale_return_id' => $return->id,
                    'product_id' => $line['product_id'],
                    'quantity' => $line['quantity'],
                    'unit_price' => $line['unit_price'],
                    'subtotal' => $line['quantity'] * $line['unit_price']
                ]);

                // Stock movement (back to warehouse)
                $this->updateStock($line['product_id'], $request->warehouse_id, $line['quantity'], 'in');

                StockMovement::create([
                    'product_id' => $line['product_id'],
                    'warehouse_id' => $request->warehouse_id,
                    'type' => 'in',
                    'quantity' => $line['quantity'],
                    'reason' => 'Sale Return',
                    'date' => $request->date,
                    'source_type' => SaleReturn::class,
                    'source_id' => $return->id
                ]);
            }
        });

        return redirect()->route('salereturns.index')->with('success', 'Sale return created successfully.');
    }

    public function edit(SaleReturn $salereturn)
    {
        $salereturn->load('lines.product', 'customer');
        return view('documents.sales.returns.edit', compact('salereturn'));
    }

    public function update(Request $request, SaleReturn $salereturn)
    {
        DB::transaction(function () use ($request, $salereturn) {
            // Reverse old stock first
            foreach ($salereturn->lines as $line) {
                $this->updateStock($line->product_id, $salereturn->warehouse_id, $line->quantity, 'out');
            }

            $salereturn->lines()->delete();
            $salereturn->stockMovements()->delete();

            $salereturn->update($request->only([
                'sales_order_id',
                'customer_id',
                'date',
                'warehouse_id',
                'reason',
                'subtotal',
                'tax',
                'total',
                'status'
            ]));

            foreach ($request->lines as $line) {
                $lineModel = SaleReturnLine::create([
                    'sale_return_id' => $salereturn->id,
                    'product_id' => $line['product_id'],
                    'quantity' => $line['quantity'],
                    'unit_price' => $line['unit_price'],
                    'subtotal' => $line['quantity'] * $line['unit_price']
                ]);

                $this->updateStock($line['product_id'], $request->warehouse_id, $line['quantity'], 'in');

                StockMovement::create([
                    'product_id' => $line['product_id'],
                    'warehouse_id' => $request->warehouse_id,
                    'type' => 'in',
                    'quantity' => $line['quantity'],
                    'reason' => 'Sale Return',
                    'date' => $request->date,
                    'source_type' => SaleReturn::class,
                    'source_id' => $salereturn->id
                ]);
            }
        });

        return redirect()->route('salereturns.index')->with('success', 'Sale return updated successfully.');
    }

    public function destroy(SaleReturn $salereturn)
    {
        DB::transaction(function () use ($salereturn) {
            foreach ($salereturn->lines as $line) {
                $this->updateStock($line->product_id, $salereturn->warehouse_id, $line->quantity, 'out');
            }
            $salereturn->lines()->delete();
            $salereturn->stockMovements()->delete();
            $salereturn->delete();
        });

        return redirect()->route('salereturns.index')->with('success', 'Sale return deleted.');
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