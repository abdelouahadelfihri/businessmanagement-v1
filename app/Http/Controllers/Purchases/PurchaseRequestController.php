<?php

namespace App\Http\Controllers;

use App\Models\Purchases\PurchaseRequest;
use App\Models\Purchases\Supplier;
use Illuminate\Http\Request;

class PurchaseRequestController extends Controller
{
    public function index()
    {
        $requests = PurchaseRequest::with('supplier')->paginate(10);
        return view('purchase_requests.index', compact('requests'));
    }

    public function create()
    {
        $suppliers = Supplier::all();
        return view('purchase_requests.create', compact('suppliers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'description' => 'required',
            'date' => 'required|date',
            'status' => 'required'
        ]);

        PurchaseRequest::create($validated);

        return redirect()->route('purchase_requests.index')
            ->with('success', 'Purchase request created successfully.');
    }

    public function edit(PurchaseRequest $purchase_request)
    {
        $suppliers = Supplier::all();
        return view('purchase_requests.edit', compact('purchase_request', 'suppliers'));
    }

    public function update(Request $request, PurchaseRequest $purchase_request)
    {
        $validated = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'description' => 'required',
            'date' => 'required|date',
            'status' => 'required'
        ]);

        $purchase_request->update($validated);

        return redirect()->route('purchase_requests.index')
            ->with('success', 'Purchase request updated successfully.');
    }

    public function destroy(PurchaseRequest $purchase_request)
    {
        $purchase_request->delete();

        return redirect()->route('purchase_requests.index')
            ->with('success', 'Purchase request deleted.');
    }
}