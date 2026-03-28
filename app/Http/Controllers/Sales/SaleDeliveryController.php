<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Sales\SaleDelivery;
use App\Models\MasterData\StockMovement;
use App\Models\MasterData\WarehouseStock;
use App\Models\MasterData\Warehouse;
use App\Traits\StockHelper;

class SaleDeliveryController extends Controller
{
    use StockHelper;

    public function create()
    {
        $warehouses = Warehouse::all();

        return view('documents.create', [
            'route' => route('sales-deliveries.store'),
            'warehouses' => $warehouses,
            'partyLabel' => 'Customer',
            'partyField' => 'customer_id',
            'withPrice' => false,
            'dateField' => 'delivery_date',
            'title' => 'Create Sales Delivery',
        ]);
    }

    public function store(Request $request)
    {
        DB::transaction(function () use ($request) {

            $warehouseId = $request->warehouse_id;

            // 🔴 check stock in selected warehouse
            foreach ($request->lines as $line) {

                $stock = WarehouseStock::where('product_id', $line['product_id'])
                    ->where('warehouse_id', $warehouseId)
                    ->value('quantity') ?? 0;

                if ($stock < $line['quantity']) {
                    throw new \Exception('Not enough stock');
                }
            }

            $model = SaleDelivery::create(
                $request->only('customer_id', 'delivery_date', 'status', 'warehouse_id')
            );

            foreach ($request->lines as $line) {

                $model->lines()->create($line);

                StockMovement::create([
                    'product_id' => $line['product_id'],
                    'warehouse_id' => $warehouseId,
                    'type' => 'out',
                    'quantity' => $line['quantity'],
                    'reason' => 'sales_delivery',
                    'date' => now(),
                    'source_type' => SaleDelivery::class,
                    'source_id' => $model->id,
                ]);

                $this->updateStock(
                    $line['product_id'],
                    $warehouseId,
                    $line['quantity'],
                    'out'
                );
            }

        });

        return redirect()->route('sales-deliveries.index');
    }

    public function edit($id)
    {
        $model = SaleDelivery::with('lines.product', 'customer')->findOrFail($id);
        $warehouses = Warehouse::all();

        return view('documents.edit', [
            'model' => $model,
            'route' => route('sales-deliveries.update', $id),
            'warehouses' => $warehouses,
            'partyLabel' => 'Customer',
            'partyField' => 'customer_id',
            'withPrice' => false,
            'dateField' => 'delivery_date',
            'title' => 'Edit Sales Delivery'
        ]);
    }

    public function update(Request $request, $id)
    {
        DB::transaction(function () use ($request, $id) {

            $model = SaleDelivery::with('lines')->findOrFail($id);

            $newWarehouse = $request->warehouse_id;
            $oldWarehouse = $model->warehouse_id;

            // 🔴 reverse from OLD warehouse
            foreach ($model->lines as $line) {
                $this->updateStock(
                    $line->product_id,
                    $oldWarehouse,
                    $line->quantity,
                    'in'
                );
            }

            StockMovement::where('source_type', SaleDelivery::class)
                ->where('source_id', $model->id)
                ->delete();

            $model->lines()->delete();

            // 🔴 check stock in NEW warehouse
            foreach ($request->lines as $line) {

                $stock = WarehouseStock::where('product_id', $line['product_id'])
                    ->where('warehouse_id', $newWarehouse)
                    ->value('quantity') ?? 0;

                if ($stock < $line['quantity']) {
                    throw new \Exception('Not enough stock');
                }
            }

            $model->update(
                $request->only('customer_id', 'delivery_date', 'status', 'warehouse_id')
            );

            // 🟢 apply to NEW warehouse
            foreach ($request->lines as $line) {

                $model->lines()->create($line);

                StockMovement::create([
                    'product_id' => $line['product_id'],
                    'warehouse_id' => $newWarehouse,
                    'type' => 'out',
                    'quantity' => $line['quantity'],
                    'reason' => 'sales_delivery',
                    'date' => now(),
                    'source_type' => SaleDelivery::class,
                    'source_id' => $model->id,
                ]);

                $this->updateStock(
                    $line['product_id'],
                    $newWarehouse,
                    $line['quantity'],
                    'out'
                );
            }

        });

        return redirect()->route('sales-deliveries.index');
    }
}