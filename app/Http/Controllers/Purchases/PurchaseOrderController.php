<?php

namespace App\Http\Controllers\Purchases;

use App\Http\Controllers\Controller;
use App\Models\Purchases\PurchaseOrder;
use App\Models\MasterData\Supplier;
use App\Models\Purchases\PurchaseRequest;
use Illuminate\Http\Request;
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\PurchaseRequest;

class PurchaseOrderController extends Controller
{
    public function index()
    {
        $orders = PurchaseOrder::with('supplier', 'purchaseRequest')->get();
        return view('purchase_orders.index', compact('orders'));
    }

    public function create()
    {
        return view('purchase_orders.create');
    }

    public function store(Request $request)
    {
        PurchaseOrder::create($request->all());
        return redirect()->route('purchase-orders.index');
    }

    public function edit(PurchaseOrder $purchaseOrder)
    {
        return view('purchase_orders.edit', compact('purchaseOrder'));
    }

    public function update(Request $request, PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->update($request->all());
        return redirect()->route('purchase-orders.index');
    }

    // AJAX method to get orders by supplier
    public function getBySupplier($supplier_id)
    {
        $orders = PurchaseOrder::where('supplier_id', $supplier_id)->with('supplier')->get();
        $result = $orders->map(function ($o) {
            return ['id' => $o->id, 'order_number' => $o->order_number, 'supplier_name' => $o->supplier->name];
        });
        return response()->json($result);
    }
}