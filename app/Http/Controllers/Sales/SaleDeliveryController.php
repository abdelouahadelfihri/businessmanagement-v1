<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Sales\SaleDelivery;
use App\Models\Sales\SaleOrder;
use App\Models\MasterData\StockMovement;
use App\Models\MasterData\WarehouseStock;
class SaleDeliveryController extends Controller
{
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
        foreach ($request->lines as $line) {

            $stock = WarehouseStock::where('product_id', $line['product_id'])
                ->where('warehouse_id', 1)
                ->value('quantity') ?? 0;

            if ($stock < $line['quantity']) {
                return back()->with('error', 'Not enough stock');
            }
        }

        $model = SaleDelivery::create(
            $request->only('customer_id', 'delivery_date', 'status')
        );

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

            $this->updateStock($line['product_id'], 1, $line['quantity'], 'out');
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
        $model = SaleDelivery::findOrFail($id);

        // reverse old stock
        foreach ($model->lines as $line) {
            $this->updateStock($line->product_id, 1, $line->quantity, 'in');
        }

        StockMovement::where('source_type', SaleDelivery::class)
            ->where('source_id', $model->id)
            ->delete();

        $model->lines()->delete();

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

            $this->updateStock($line['product_id'], 1, $line['quantity'], 'out');
        }

        return redirect()->route('sales-deliveries.index');
    }
}

// Done