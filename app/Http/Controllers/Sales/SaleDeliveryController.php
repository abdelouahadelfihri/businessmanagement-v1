<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Sales\SaleDelivery;
use App\Models\Sales\SaleOrder;
use App\Models\MasterData\StockMovement;
use App\Models\MasterData\WarehouseStock;
use App\Traits\StockHelper;
class SaleDeliveryController extends Controller
{

    use StockHelper;
    public function create(Request $request)
    {
        $source = null;

        if ($request->source_type == 'sales_order') {
            $source = SaleOrder::with('lines.product')->find($request->source_id);
        }

        return view('documents.create', [
            'route' => route('sales-deliveries.store'),
            'partyLabel' => 'Customer',
            'partyField' => 'customer_id',
            'withPrice' => false,
            'dateField' => 'delivery_date',
            'title' => 'Create Sales Delivery',
            'source' => $source
        ]);
    }

    public function store(Request $request)
    {
        // 🔴 STEP 1: CHECK STOCK
        foreach ($request->lines as $line) {

            $stock = WarehouseStock::where('product_id', $line['product_id'])
                ->where('warehouse_id', 1)
                ->value('quantity') ?? 0;

            if ($stock < $line['quantity']) {
                return back()->with('error', 'Not enough stock');
            }
        }

        // 🟢 STEP 2: CREATE DOCUMENT
        $model = SaleDelivery::create(
            $request->only('customer_id', 'delivery_date', 'status')
        );

        foreach ($request->lines as $line) {

            // create line
            $model->lines()->create($line);

            // create movement
            StockMovement::create([
                'product_id' => $line['product_id'],
                'warehouse_id' => 1,
                'type' => 'out',
                'quantity' => $line['quantity'],
                'reason' => 'sales_delivery',
                'date' => now(),
                'source_type' => SaleDelivery::class,
                'source_id' => $model->id,
            ]);

            // update stock
            $this->updateStock(
                $line['product_id'],
                1,
                $line['quantity'],
                'out'
            );
        }

        return redirect()->route('sales-deliveries.index');
    }

    public function edit($id)
    {
        $model = SaleDelivery::with('lines.product', 'customer')->findOrFail($id);

        return view('documents.edit', [
            'model' => $model,
            'route' => route('sales-deliveries.update', $id),
            'partyLabel' => 'Customer',
            'partyField' => 'customer_id',
            'withPrice' => false,
            'dateField' => 'delivery_date',
            'title' => 'Edit Sales Delivery'
        ]);
    }

    public function update(Request $request, $id)
    {
        $model = SaleDelivery::with('lines')->findOrFail($id);

        // 🔴 STEP 1: REVERSE OLD STOCK
        foreach ($model->lines as $line) {

            $this->updateStock(
                $line->product_id,
                1,
                $line->quantity,
                'in' // reverse OUT
            );
        }

        // 🔴 STEP 2: DELETE OLD MOVEMENTS
        StockMovement::where('source_type', SaleDelivery::class)
            ->where('source_id', $model->id)
            ->delete();

        // 🔴 STEP 3: DELETE OLD LINES
        $model->lines()->delete();

        // 🔴 STEP 4: CHECK NEW STOCK
        foreach ($request->lines as $line) {

            $stock = WarehouseStock::where('product_id', $line['product_id'])
                ->where('warehouse_id', 1)
                ->value('quantity') ?? 0;

            if ($stock < $line['quantity']) {
                return back()->with('error', 'Not enough stock');
            }
        }

        // 🔴 STEP 5: UPDATE HEADER
        $model->update(
            $request->only('customer_id', 'delivery_date', 'status')
        );

        // 🟢 STEP 6: RECREATE
        foreach ($request->lines as $line) {

            $model->lines()->create($line);

            StockMovement::create([
                'product_id' => $line['product_id'],
                'warehouse_id' => 1,
                'type' => 'out',
                'quantity' => $line['quantity'],
                'reason' => 'sales_delivery',
                'date' => now(),
                'source_type' => SaleDelivery::class,
                'source_id' => $model->id,
            ]);

            $this->updateStock(
                $line['product_id'],
                1,
                $line['quantity'],
                'out'
            );
        }

        return redirect()->route('sales-deliveries.index');
    }
}

// Done