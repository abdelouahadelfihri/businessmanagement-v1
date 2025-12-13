<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use App\Models\Sales\SalesQuote;
use Illuminate\Http\Request;

class SaleQuoteController extends Controller
{
    // GET /sales-quotes
    public function index()
    {
        return response()->json(
            SalesQuote::with('customer')->get()
        );
    }

    // POST /sales-quotes
    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id'  => 'required|exists:customers,customerId',
            'quote_number' => 'required|string|unique:sales_quotes',
            'date'         => 'required|date',
            'total'        => 'required|numeric',
            'status'       => 'nullable|string'
        ]);

        $quote = SalesQuote::create($validated);

        return response()->json($quote, 201);
    }

    // GET /sales-quotes/{id}
    public function show($id)
    {
        return response()->json(
            SalesQuote::with('customer')->findOrFail($id)
        );
    }

    // PUT/PATCH /sales-quotes/{id}
    public function update(Request $request, $id)
    {
        $quote = SalesQuote::findOrFail($id);

        $validated = $request->validate([
            'customer_id'  => 'sometimes|exists:customers,customerId',
            'quote_number' => 'sometimes|string|unique:sales_quotes,quote_number,' . $id,
            'date'         => 'sometimes|date',
            'total'        => 'sometimes|numeric',
            'status'       => 'sometimes|string'
        ]);

        $quote->update($validated);

        return response()->json($quote);
    }

    // DELETE /sales-quotes/{id}
    public function destroy($id)
    {
        $quote = SalesQuote::findOrFail($id);
        $quote->delete();

        return response()->json(['message' => 'Deleted successfully']);
    }
}