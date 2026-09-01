<?php

namespace App\Http\Controllers\Sales;

use Illuminate\Http\Request;
use App\Models\Sales\SaleQuotation;
use App\Http\Controllers\Controller;

class SaleQuoteController extends Controller
{
    public function create()
    {
        return view('sales_quotes.create', [
            'route' => route('sales-quotes.store'),
            'partyLabel' => 'Customer',
            'partyField' => 'customer_id',
            'withPrice' => true,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'date' => 'required|date',
            'status' => 'required',
            'lines' => 'nullable|array',
            'lines.*.product_id' => 'required_with:lines|exists:products,id',
            'lines.*.quantity' => 'required_with:lines|numeric|min:1',
            'lines.*.unit_price' => 'required_with:lines|numeric|min:0',
        ]);

        $quote = SaleQuotation::create([
            'customer_id' => $data['customer_id'],
            'date' => $data['date'],
            'status' => $data['status'],
        ]);

        $total = 0;
        if (isset($data['lines'])) {
            foreach ($data['lines'] as $line) {
                $total += $line['quantity'] * $line['unit_price'];
                $quote->lines()->create([
                    'product_id' => $line['product_id'],
                    'quantity' => $line['quantity'],
                    'unit_price' => $line['unit_price'],
                ]);
            }
        }

        $quote->update(['total_amount' => $total]);

        return redirect()->route('sales-quotes.index')->with('success', 'Sales Quote created.');
    }

    public function edit($id)
    {
        $quote = SaleQuotation::with('lines.product')->findOrFail($id);

        return view('sales_quotes.edit', [
            'model' => $quote,
            'route' => route('sales-quotes.update', $id),
            'partyLabel' => 'Customer',
            'partyField' => 'customer_id',
            'withPrice' => true,
        ]);
    }

    public function update(Request $request, $id)
    {
        $quote = SaleQuotation::findOrFail($id);

        $data = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'date' => 'required|date',
            'status' => 'required',
            'lines' => 'nullable|array',
            'lines.*.product_id' => 'required_with:lines|exists:products,id',
            'lines.*.quantity' => 'required_with:lines|numeric|min:1',
            'lines.*.unit_price' => 'required_with:lines|numeric|min:0',
        ]);

        $quote->update([
            'customer_id' => $data['customer_id'],
            'date' => $data['date'],
            'status' => $data['status'],
        ]);

        $quote->lines()->delete();

        $total = 0;
        if (isset($data['lines'])) {
            foreach ($data['lines'] as $line) {
                $total += $line['quantity'] * $line['unit_price'];
                $quote->lines()->create([
                    'product_id' => $line['product_id'],
                    'quantity' => $line['quantity'],
                    'unit_price' => $line['unit_price'],
                ]);
            }
        }

        $quote->update(['total_amount' => $total]);

        return redirect()->route('sales-quotes.index')->with('success', 'Sales Quote updated.');
    }
}

// Done