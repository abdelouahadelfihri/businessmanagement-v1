<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Sales\SaleInvoice;
use App\Models\Sales\SaleOrder;

class SalesInvoiceController extends Controller
{
    public function create(Request $request)
    {
        $source = null;
        if ($request->source_type == 'sales_order') {
            $source = SaleOrder::with('lines.product')->find($request->source_id);
        }

        return view('documents.create', [
            'route' => route('sales-invoices.store'),
            'partyLabel' => 'Customer',
            'partyField' => 'customer_id',
            'withPrice' => true,
            'dateField' => 'invoice_date',
            'title' => 'Create Sales Invoice',
            'source' => $source
        ]);
    }

    public function store(Request $request)
    {
        $model = SaleInvoice::create($request->only('customer_id', 'invoice_date', 'status'));

        $total = 0;
        foreach ($request->lines as $line) {
            $total += $line['quantity'] * $line['unit_price'];
            $model->lines()->create($line);
        }

        $model->update(['total_amount' => $total]);

        return redirect()->route('sales-invoices.index');
    }

    public function edit($id)
    {
        $model = SaleInvoice::with('lines.product', 'customer')->findOrFail($id);

        return view('documents.edit', [
            'model' => $model,
            'route' => route('sales-invoices.update', $id),
            'partyLabel' => 'Customer',
            'partyField' => 'customer_id',
            'withPrice' => true,
            'dateField' => 'invoice_date',
            'title' => 'Edit Sales Invoice'
        ]);
    }

    public function update(Request $request, $id)
    {
        $model = SaleInvoice::findOrFail($id);

        $model->update($request->only('customer_id', 'invoice_date', 'status'));

        $model->lines()->delete();

        $total = 0;
        foreach ($request->lines as $line) {
            $total += $line['quantity'] * $line['unit_price'];
            $model->lines()->create($line);
        }

        $model->update(['total_amount' => $total]);

        return redirect()->route('sales-invoices.index');
    }
}

// Done