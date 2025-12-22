<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use App\Models\Sales\SaleQuote;
use Illuminate\Http\Request;

class SaleQuotationController extends Controller
{
    // GET /sales-quotes
    public function index()
    {
        return response()->json(
            SaleQuote::with('customer')->get()
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

        $quote = SaleQuote::create($validated);

        return response()->json($quote, 201);
    }

    // GET /sales-quotes/{id}
    public function show($id)
    {
        return response()->json(
            SaleQuote::with('customer')->findOrFail($id)
        );
    }

    // PUT/PATCH /sales-quotes/{id}
    public function update(Request $request, $id)
    {
        $quote = SaleQuote::findOrFail($id);

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
        $quote = SaleQuote::findOrFail($id);
        $quote->delete();

        return response()->json(['message' => 'Deleted successfully']);
    }
}