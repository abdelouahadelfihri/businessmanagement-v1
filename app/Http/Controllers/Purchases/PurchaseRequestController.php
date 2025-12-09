<?php

namespace App\Http\Controllers;

use App\Models\PurchaseRequest;
use App\Models\Supplier;
use Illuminate\Http\Request;

class PurchaseRequestController extends Controller
{
    public function index(Request $request)
    {
        $q = $request->query('q');
        $query = PurchaseRequest::with('supplier');

        if ($q) {
            $query->where('description', 'like', "%{$q}%")
                ->orWhereHas('supplier', function ($q2) use ($q) {
                    $q2->where('name', 'like', "%{$q}%");
                });
        }

        $requests = $query->orderBy('date', 'desc')->paginate(10)->withQueryString();

        return view('purchase_requests.index', compact('requests', 'q'));
    }

    public function create()
    {
        $suppliers = Supplier::orderBy('name')->get();
        return view('purchase_requests.create', compact('suppliers'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'supplier_id' => 'nullable|exists:suppliers,id',
            'description' => 'required|string',
            'date' => 'required|date',
            'status' => 'required|string',
        ]);

        PurchaseRequest::create($data);

        return redirect()->route('purchase-requests.index')->with('success', 'Purchase request created.');
    }

    public function edit(PurchaseRequest $purchaseRequest)
    {
        $suppliers = Supplier::orderBy('name')->get();
        return view('purchase_requests.edit', compact('purchaseRequest', 'suppliers'));
    }

    public function update(Request $request, PurchaseRequest $purchaseRequest)
    {
        $data = $request->validate([
            'supplier_id' => 'nullable|exists:suppliers,id',
            'description' => 'required|string',
            'date' => 'required|date',
            'status' => 'required|string',
        ]);

        $purchaseRequest->update($data);

        return redirect()->route('purchase-requests.index')->with('success', 'Purchase request updated.');
    }

    public function show(PurchaseRequest $purchaseRequest)
    {
        return view('purchase_requests.show', compact('purchaseRequest'));
    }

    public function destroy(PurchaseRequest $purchaseRequest)
    {
        $purchaseRequest->delete();
        return redirect()->route('purchase-requests.index')->with('success', 'Deleted.');
    }

}