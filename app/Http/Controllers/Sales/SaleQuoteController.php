<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Sales\SaleQuotation;

class SaleQuoteController extends Controller
{
    public function create()
    {
        return view('documents.create', [
            'route' => route('sales-quotes.store'),
            'partyLabel' => 'Customer',
            'partyField' => 'customer_id',
            'withPrice' => true,
            'dateField' => 'quote_date',
            'title' => 'Create Sales Quote'
        ]);
    }

    public function store(Request $request)
    {
        $model = SaleQuotation::create($request->only('customer_id', 'quote_date', 'status'));

        $total = 0;

        foreach ($request->lines ?? [] as $line) {
            $total += $line['quantity'] * $line['unit_price'];
            $model->lines()->create($line);
        }

        $model->update(['total_amount' => $total]);

        return redirect()->route('sales-quotes.index');
    }

    public function edit($id)
    {
        $model = SaleQuotation::with('lines.product', 'customer')->findOrFail($id);

        return view('documents.edit', [
            'model' => $model,
            'route' => route('sales-quotes.update', $id),
            'partyLabel' => 'Customer',
            'partyField' => 'customer_id',
            'withPrice' => true,
            'dateField' => 'quote_date',
            'title' => 'Edit Sales Quote'
        ]);
    }

    public function update(Request $request, $id)
    {
        $model = SaleQuotation::findOrFail($id);

        $model->update($request->only('customer_id', 'quote_date', 'status'));

        $model->lines()->delete();

        $total = 0;

        foreach ($request->lines ?? [] as $line) {
            $total += $line['quantity'] * $line['unit_price'];
            $model->lines()->create($line);
        }

        $model->update(['total_amount' => $total]);

        return redirect()->route('sales-quotes.index');
    }
}