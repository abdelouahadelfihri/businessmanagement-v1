<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Purchases\PurchaseRequest;

class PurchaseRequestController extends Controller
{
    public function create()
    {
        return view('purchase_requests.create', [
            'route' => route('purchase-requests.store'),
            'partyLabel' => 'Supplier',
            'partyField' => 'supplier_id',
            'withPrice' => false, // no price for PR
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'supplier_id' => 'nullable|exists:suppliers,id',
            'date' => 'required|date',
            'status' => 'required',
            'lines' => 'nullable|array',
            'lines.*.product_id' => 'required_with:lines|exists:products,id',
            'lines.*.quantity' => 'required_with:lines|numeric|min:1',
        ]);

        $requestModel = PurchaseRequest::create([
            'supplier_id' => $data['supplier_id'] ?? null,
            'date' => $data['date'],
            'status' => $data['status'],
        ]);

        if (isset($data['lines'])) {
            foreach ($data['lines'] as $line) {
                $requestModel->lines()->create([
                    'product_id' => $line['product_id'],
                    'quantity' => $line['quantity'],
                ]);
            }
        }

        return redirect()->route('purchase-requests.index')->with('success', 'Purchase Request created.');
    }

    public function edit($id)
    {
        $model = PurchaseRequest::with('lines.product')->findOrFail($id);

        return view('purchase_requests.edit', [
            'model' => $model,
            'route' => route('purchase-requests.update', $id),
            'partyLabel' => 'Supplier',
            'partyField' => 'supplier_id',
            'withPrice' => false,
        ]);
    }

    public function update(Request $request, $id)
    {
        $model = PurchaseRequest::findOrFail($id);

        $data = $request->validate([
            'supplier_id' => 'nullable|exists:suppliers,id',
            'date' => 'required|date',
            'status' => 'required',
            'lines' => 'nullable|array',
            'lines.*.product_id' => 'required_with:lines|exists:products,id',
            'lines.*.quantity' => 'required_with:lines|numeric|min:1',
        ]);

        $model->update([
            'supplier_id' => $data['supplier_id'] ?? null,
            'date' => $data['date'],
            'status' => $data['status'],
        ]);

        $model->lines()->delete();

        if (isset($data['lines'])) {
            foreach ($data['lines'] as $line) {
                $model->lines()->create([
                    'product_id' => $line['product_id'],
                    'quantity' => $line['quantity'],
                ]);
            }
        }

        return redirect()->route('purchase-requests.index')->with('success', 'Purchase Request updated.');
    }
}

//Done