<?php

namespace App\Http\Controllers\Purchase;

use App\Http\Controllers\Controller;
use App\Models\Purchase\PurchaseRequest;
use Illuminate\Http\Request;

class PurchaseRequestController extends Controller
{
    // GET /purchase-requests
    public function index()
    {
        return response()->json(PurchaseRequest::all());
    }

    // POST /purchase-requests
    public function store(Request $request)
    {
        $validated = $request->validate([
            'description' => 'required|string',
            'date'        => 'required|date',
            'status'      => 'nullable|string'
        ]);

        $purchaseRequest = PurchaseRequest::create($validated);

        return response()->json($purchaseRequest, 201);
    }

    // GET /purchase-requests/{id}
    public function show($id)
    {
        return response()->json(PurchaseRequest::findOrFail($id));
    }

    // PUT/PATCH /purchase-requests/{id}
    public function update(Request $request, $id)
    {
        $purchaseRequest = PurchaseRequest::findOrFail($id);

        $validated = $request->validate([
            'description' => 'sometimes|string',
            'date'        => 'sometimes|date',
            'status'      => 'sometimes|string'
        ]);

        $purchaseRequest->update($validated);

        return response()->json($purchaseRequest);
    }

    // DELETE /purchase-requests/{id}
    public function destroy($id)
    {
        $purchaseRequest = PurchaseRequest::findOrFail($id);
        $purchaseRequest->delete();

        return response()->json(['message' => 'Deleted successfully']);
    }
}