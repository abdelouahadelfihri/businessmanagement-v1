<?php

namespace App\Http\Controllers\Purchases;

use Illuminate\Http\Request;
use App\Models\Purchases\PurchaseInvoice;
use App\Models\Purchases\PurchaseOrder;
use App\Http\Controllers\Controller;

class PurchaseInvoiceController extends Controller
{
    public function create(Request $request)
    {
        $source = null;

        if ($request->source_type && $request->source_id) {
            if ($request->source_type === 'purchase_order') {
                $source = PurchaseOrder::with('lines.product')->find($request->source_id);
            }
        }

        return view('purchase_invoices.create', [
            'source' => $source,
            'route' => route('purchase-invoices.store'),
            'partyLabel' => 'Supplier',
            'partyField' => 'supplier_id',
            'withPrice' => true,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'date' => 'required|date',
            'status' => 'required',
            'lines' => 'required|array|min:1',
            'lines.*.product_id' => 'required|exists:products,id',
            'lines.*.quantity' => 'required|numeric|min:1',
            'lines.*.unit_price' => 'required|numeric|min:0',
        ]);

        $document = PurchaseInvoice::create([
            'supplier_id' => $data['supplier_id'],
            'date' => $data['date'],
            'status' => $data['status'],
        ]);

        $total = 0;

        foreach ($data['lines'] as $line) {
            $lineTotal = $line['quantity'] * $line['unit_price'];
            $total += $lineTotal;

            $document->lines()->create([
                'product_id' => $line['product_id'],
                'quantity' => $line['quantity'],
                'unit_price' => $line['unit_price'],
            ]);
        }

        $document->update(['total_amount' => $total]);

        return redirect()->route('purchase-invoices.index')->with('success', 'Purchase invoice created.');
    }

    public function edit($id)
    {
        $document = PurchaseInvoice::with('lines.product')->findOrFail($id);

        return view('purchase_invoices.edit', [
            'model' => $document,
            'route' => route('purchase-invoices.update', $id),
            'partyLabel' => 'Supplier',
            'partyField' => 'supplier_id',
            'withPrice' => true,
        ]);
    }

    public function update(Request $request, $id)
    {
        $document = PurchaseInvoice::findOrFail($id);

        $data = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'date' => 'required|date',
            'status' => 'required',
            'lines' => 'required|array|min:1',
            'lines.*.product_id' => 'required|exists:products,id',
            'lines.*.quantity' => 'required|numeric|min:1',
            'lines.*.unit_price' => 'required|numeric|min:0',
        ]);

        $document->update([
            'supplier_id' => $data['supplier_id'],
            'date' => $data['date'],
            'status' => $data['status'],
        ]);

        $document->lines()->delete();

        $total = 0;
        foreach ($data['lines'] as $line) {
            $lineTotal = $line['quantity'] * $line['unit_price'];
            $total += $lineTotal;

            $document->lines()->create([
                'product_id' => $line['product_id'],
                'quantity' => $line['quantity'],
                'unit_price' => $line['unit_price'],
            ]);
        }

        $document->update(['total_amount' => $total]);

        return redirect()->route('purchase-invoices.index')->with('success', 'Purchase invoice updated.');
    }
}
// Done