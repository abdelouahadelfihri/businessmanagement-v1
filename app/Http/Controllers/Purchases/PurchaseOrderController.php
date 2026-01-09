<?php

namespace App\Http\Controllers\Purchases;
use App\Models\Purchases\PurchaseOrder;
use App\Models\MasterData\Supplier;
use App\Models\MasterData\Product;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;


class PurchaseOrderController extends Controller
{
    public function index()
    {
        $orders = PurchaseOrder::latest()->paginate(15);
        return view('purchasesorders.index', compact('orders'));
    }
    public function create()
    {
        return view('purchasesorders.create', ['suppliers' => Supplier::all(), 'products' => Product::all()]);
    }
    public function store(Request $request)
    {
        $data = $request->validate([
            'supplier_id' => 'required',
            'lines.*.product_id' => 'required',
            'lines.*.quantity' => 'required|integer|min:1',
            'lines.*.unit_price' => 'required|numeric|min:0'
        ]);
        $po = PurchaseOrder::create(['supplier_id' => $data['supplier_id']]);
        foreach ($data['lines'] as $line)
            $po->lines()->create($line);
        return redirect()->route('purchase-orders.index');
    }
    public function edit(PurchaseOrder $purchaseOrder)
    {
        return view('purchasesorders.edit', ['po' => $purchaseOrder->load('lines'), 'suppliers' => Supplier::all(), 'products' => Product::all()]);
    }
    public function update(Request $request, PurchaseOrder $po)
    {
        $data = $request->validate(['supplier_id' => 'required', 'lines.*.product_id' => 'required', 'lines.*.quantity' => 'required|integer|min:1', 'lines.*.unit_price' => 'required|numeric|min:0']);
        $po->lines()->delete();
        $po->update(['supplier_id' => $data['supplier_id']]);
        foreach ($data['lines'] as $line)
            $po->lines()->create($line);
        return redirect()->route('purchase-orders.index');
    }
}