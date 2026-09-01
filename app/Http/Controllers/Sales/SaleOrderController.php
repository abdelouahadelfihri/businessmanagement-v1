<?php

namespace App\Http\Controllers\Sales;

use Illuminate\Http\Request;
use App\Models\Sales\SaleOrder;
use App\Models\Sales\SaleQuotation;
use App\Http\Controllers\Controller;

class SaleOrderController extends Controller
{
    public function create(Request $request)
    {
        $source = null;
        if ($request->source_type == 'sales_quote') {
            $source = SaleQuotation::with('lines.product')->find($request->source_id);
        }

        return view('documents.create', [
            'route' => route('sales-orders.store'),
            'partyLabel' => 'Customer',
            'partyField' => 'customer_id',
            'withPrice' => true,
            'dateField' => 'order_date',
            'title' => 'Create Sales Order',
            'source' => $source
        ]);
    }

    public function store(Request $request)
    {
        $model = SaleOrder::create($request->only('customer_id', 'order_date', 'status'));

        $total = 0;

        foreach ($request->lines as $line) {
            $total += $line['quantity'] * $line['unit_price'];
            $model->lines()->create($line);
        }

        $model->update(['total_amount' => $total]);

        return redirect()->route('sales-orders.index');
    }

    public function edit($id)
    {
        $model = SaleOrder::with('lines.product', 'customer')->findOrFail($id);

        return view('documents.edit', [
            'model' => $model,
            'route' => route('sales-orders.update', $id),
            'partyLabel' => 'Customer',
            'partyField' => 'customer_id',
            'withPrice' => true,
            'dateField' => 'order_date',
            'title' => 'Edit Sales Order'
        ]);
    }

    public function update(Request $request, $id)
    {
        $model = SaleOrder::findOrFail($id);

        $model->update($request->only('customer_id', 'order_date', 'status'));

        $model->lines()->delete();

        $total = 0;
        foreach ($request->lines as $line) {
            $total += $line['quantity'] * $line['unit_price'];
            $model->lines()->create($line);
        }

        $model->update(['total_amount' => $total]);

        return redirect()->route('sales-orders.index');
    }
}

// Done