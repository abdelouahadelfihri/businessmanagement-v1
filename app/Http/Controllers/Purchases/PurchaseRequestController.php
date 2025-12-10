<?php

namespace App\Http\Controllers;

use App\Models\Purchases\PurchaseRequest;
use App\Models\Purchases\Supplier;
use Illuminate\Http\Request;

class PurchaseRequestController extends Controller
{
    // List all requests
    public function index()
    {
        $purchaseRequests = PurchaseRequest::with('supplier')->orderBy('date','desc')->get();
        return view('purchase_requests.index', compact('purchaseRequests'));
    }

    // Show create form
    public function create()
    {
        $suppliers = Supplier::orderBy('name')->get();
        return view('purchase_requests.create', compact('suppliers'));
    }

    // Store new request
    public function store(Request $request)
    {
        $validated = $request->validate([
            'supplier_id'=>'nullable|exists:suppliers,id',
            'description'=>'required|string|max:1000',
            'date'=>'required|date',
            'status'=>'required|in:draft,submitted,approved,rejected',
        ]);

        PurchaseRequest::create($validated);
        return redirect()->route('purchase-requests.index')->with('success','Purchase request created successfully.');
    }

    // Show edit form
    public function edit(PurchaseRequest $purchaseRequest)
    {
        $suppliers = Supplier::orderBy('name')->get();
        return view('purchase_requests.edit', compact('purchaseRequest','suppliers'));
    }

    // Update request
    public function update(Request $request, PurchaseRequest $purchaseRequest)
    {
        $validated = $request->validate([
            'supplier_id'=>'nullable|exists:suppliers,id',
            'description'=>'required|string|max:1000',
            'date'=>'required|date',
            'status'=>'required|in:draft,submitted,approved,rejected',
        ]);

        $purchaseRequest->update($validated);
        return redirect()->route('purchase-requests.index')->with('success','Purchase request updated successfully.');
    }

    // Delete request
    public function destroy(PurchaseRequest $purchaseRequest)
    {
        $purchaseRequest->delete();
        return redirect()->route('purchase-requests.index')->with('success','Purchase request deleted successfully.');
    }
}