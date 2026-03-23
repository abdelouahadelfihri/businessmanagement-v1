<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Purchases\PurchaseInvoice;
use App\Models\Purchases\PurchaseOrder;

class PurchaseInvoiceController extends Controller
{
    public function create(Request $request)
    {
        $source = null;

        if ($request->source_type == 'purchase_order') {
            $source = PurchaseOrder::with('lines.product')->find($request->source_id);
        }

        return view('documents.create', [
            'route' => route('purchase-invoices.store'),
            'partyLabel' => 'Supplier',
            'partyField' => 'supplier_id',
            'withPrice' => true,
            'dateField' => 'invoice_date',
            'title' => 'Create Purchase Invoice',
            'source' => $source
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'supplier_id' => 'required',
            'invoice_date' => 'required|date',
            'status' => 'required',
            'lines' => 'required|array|min:1',
        ]);

        $model = PurchaseInvoice::create($data);

        $total = 0;

        foreach ($request->lines as $line) {
            $total += $line['quantity'] * $line['unit_price'];
            $model->lines()->create($line);
        }

        $model->update(['total_amount' => $total]);

        return redirect()->route('purchase-invoices.index');
    }

    public function edit($id)
    {
        $model = PurchaseInvoice::with('lines.product', 'supplier')->findOrFail($id);

        return view('documents.edit', [
            'model' => $model,
            'route' => route('purchase-invoices.update', $id),
            'partyLabel' => 'Supplier',
            'partyField' => 'supplier_id',
            'withPrice' => true,
            'dateField' => 'invoice_date',
            'title' => 'Edit Purchase Invoice'
        ]);
    }

    public function update(Request $request, $id)
    {
        $model = PurchaseInvoice::findOrFail($id);

        $model->update($request->only('supplier_id', 'invoice_date', 'status'));

        $model->lines()->delete();

        $total = 0;

        foreach ($request->lines as $line) {
            $total += $line['quantity'] * $line['unit_price'];
            $model->lines()->create($line);
        }

        $model->update(['total_amount' => $total]);

        return redirect()->route('purchase-invoices.index');
    }
}