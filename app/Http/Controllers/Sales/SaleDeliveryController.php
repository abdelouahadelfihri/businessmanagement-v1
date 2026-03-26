<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Sales\SaleDelivery;
use App\Models\Sales\SaleOrder;
use App\Models\MasterData\StockMovement;

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
        $model = SaleDelivery::create(
            $request->only('customer_id', 'delivery_date', 'status')
        );

        foreach ($request->lines as $line) {

            $model->lines()->create($line);

            // STOCK OUT
            StockMovement::create([
                'product_id' => $line['product_id'],
                'quantity' => $line['quantity'],
                'type' => 'out',
                'reference_type' => 'sales_delivery',
                'reference_id' => $model->id,
            ]);
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

        $model->update($request->only('customer_id', 'delivery_date', 'status'));

        // DELETE OLD STOCK
        StockMovement::where('reference_type', 'sales_delivery')
            ->where('reference_id', $model->id)
            ->delete();

        $model->lines()->delete();

        foreach ($request->lines as $line) {

            $model->lines()->create($line);

            // RECREATE STOCK OUT
            StockMovement::create([
                'product_id' => $line['product_id'],
                'quantity' => $line['quantity'],
                'type' => 'out',
                'reference_type' => 'sales_delivery',
                'reference_id' => $model->id,
            ]);
        }

        return redirect()->route('sales-deliveries.index');
    }
}

// Done