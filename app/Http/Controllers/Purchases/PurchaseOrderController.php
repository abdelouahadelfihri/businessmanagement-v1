<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Purchases\PurchaseOrder;
use App\Models\Purchases\PurchaseRequest;

class PurchaseOrderController extends Controller
{
    public function create(Request $request)
    {
        $source = null;

        if ($request->source_type && $request->source_id) {
            if ($request->source_type === 'purchase_request') {
                $source = PurchaseRequest::with('lines.product')->find($request->source_id);
            }
        }

        return view('documents.create', [
            'route' => route('purchase-orders.store'),
            'partyLabel' => 'Supplier',
            'partyField' => 'supplier_id',
            'withPrice' => true,
            'dateField' => 'date',
            'title' => 'Create Purchase Order',
            'source' => $source, // optional (nice feature 👍)
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

        $document = PurchaseOrder::create([
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

        return redirect()->route('purchase-orders.index')->with('success', 'Purchase order created.');
    }

    public function edit($id)
    {
        $document = PurchaseOrder::with('lines.product')->findOrFail($id);

        return view('purchase_orders.edit', [
            'model' => $document,
            'route' => route('purchase-orders.update', $id),
            'partyLabel' => 'Supplier',
            'partyField' => 'supplier_id',
            'withPrice' => true,
        ]);
    }

    public function update(Request $request, $id)
    {
        $document = PurchaseOrder::findOrFail($id);

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

        // Remove old lines
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

        return redirect()->route('purchase-orders.index')->with('success', 'Purchase order updated.');
    }
}
// Done