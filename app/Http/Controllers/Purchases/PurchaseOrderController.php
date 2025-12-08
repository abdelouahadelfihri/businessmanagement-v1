<?php

namespace App\Http\Controllers\Purchases;

use App\Http\Controllers\Controller;
use App\Models\Purchases\PurchaseOrder;
use Illuminate\Http\Request;

class PurchaseOrderController extends Controller
{
    // GET /api/purchase-orders
    public function index()
    {
        return PurchaseOrder::with(['supplier', 'request'])->get();
    }

    // POST /api/purchase-orders
    public function store(Request $request)
    {
        $data = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'request_id' => 'nullable|exists:purchase_requests,id',
            'order_date' => 'required|integer',
            'status' => 'required|string',
            'total_amount' => 'required|numeric',
        ]);

        return PurchaseOrder::create($data);
    }

    // GET /api/purchase-orders/{id}
    public function show($id)
    {
        return PurchaseOrder::with(['supplier', 'request'])->findOrFail($id);
    }

    // PUT /api/purchase-orders/{id}
    public function update(Request $request, $id)
    {
        $order = PurchaseOrder::findOrFail($id);

        $data = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'request_id' => 'nullable|exists:purchase_requests,id',
            'order_date' => 'required|integer',
            'status' => 'required|string',
            'total_amount' => 'required|numeric',
        ]);

        $order->update($data);

        return $order;
    }

    // DELETE /api/purchase-orders/{id}
    public function destroy($id)
    {
        $order = PurchaseOrder::findOrFail($id);
        $order->delete();

        return response()->json(['message' => 'Purchase order deleted']);
    }
}