<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SalesDelivery;
use App\Models\SalesOrder;

class DeliveryController extends Controller
{
    public function create(Request $request)
    {
        $source = null;
        if ($request->source_type == 'sales_order') {
            $source = SalesOrder::with('lines.product')->find($request->source_id);
        }

        return view('documents.create', [
            'route' => route('sales-deliveries.store'),
            'partyLabel' => 'Customer',
            'partyField' => 'customer_id',
            'withPrice' => false, // Delivery usually does not include prices
            'dateField' => 'delivery_date',
            'title' => 'Create Sales Delivery',
            'source' => $source
        ]);
    }

    public function store(Request $request)
    {
        $model = SalesDelivery::create($request->only('customer_id', 'delivery_date', 'status'));

        foreach ($request->lines as $line) {
            $model->lines()->create($line);
        }

        return redirect()->route('sales-deliveries.index');
    }

    public function edit($id)
    {
        $model = SalesDelivery::with('lines.product', 'customer')->findOrFail($id);

        return view('documents.edit', [
            'model' => $model,
            'route' => route('sales-deliveries.update', $id),
            'partyLabel' => 'Customer',
            'partyField' => 'customer_id',
            'withPrice' => false, // Delivery usually does not include prices
            'dateField' => 'delivery_date',
            'title' => 'Edit Sales Delivery'
        ]);
    }

    public function update(Request $request, $id)
    {
        $model = SalesDelivery::findOrFail($id);

        $model->update($request->only('customer_id', 'delivery_date', 'status'));

        $model->lines()->delete();

        foreach ($request->lines as $line) {
            $model->lines()->create($line);
        }

        return redirect()->route('sales-deliveries.index');
    }
}
// Done