<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Purchases\PurchaseRequest;

class PurchaseRequestController extends Controller
{
    public function create()
    {
        return view('documents.create', [
            'route' => route('purchase-requests.store'),
            'partyLabel' => 'Supplier',
            'partyField' => 'supplier_id',
            'withPrice' => false,
            'dateField' => 'request_date',
            'title' => 'Create Purchase Request'
        ]);
    }

    public function store(Request $request)
    {
        $model = PurchaseRequest::create($request->only('supplier_id', 'request_date', 'status'));

        foreach ($request->lines ?? [] as $line) {
            $model->lines()->create($line);
        }

        return redirect()->route('purchase-requests.index');
    }

    public function edit($id)
    {
        $model = PurchaseRequest::with('lines.product', 'supplier')->findOrFail($id);

        return view('documents.edit', [
            'model' => $model,
            'route' => route('purchase-requests.update', $id),
            'partyLabel' => 'Supplier',
            'partyField' => 'supplier_id',
            'withPrice' => false,
            'dateField' => 'request_date',
            'title' => 'Edit Purchase Request'
        ]);
    }

    public function update(Request $request, $id)
    {
        $model = PurchaseRequest::findOrFail($id);

        $model->update($request->only('supplier_id', 'request_date', 'status'));

        $model->lines()->delete();

        foreach ($request->lines ?? [] as $line) {
            $model->lines()->create($line);
        }

        return redirect()->route('purchase-requests.index');
    }
}