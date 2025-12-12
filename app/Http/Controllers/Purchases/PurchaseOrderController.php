<?php
namespace App\Http\Controllers\Purchases;

use App\Models\Purchases\PurchaseOrder;
use App\Models\Purchases\Supplier;
use App\Models\Purchases\PurchaseRequest;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class PurchaseOrderController extends Controller
{
    public function index()
    {
        $orders = PurchaseOrder::with(['supplier','purchaseRequest'])->paginate(12);
        return view('purchase_orders.index', compact('orders'));
    }

    public function create(Request $request)
    {
        $suppliers = Supplier::all();
        $requests  = PurchaseRequest::all();

        // Optional preselected ids passed by supplier/request index create/select
        $selectedSupplierId = $request->query('selected_supplier_id') ?? null;
        $selectedRequestId  = $request->query('selected_request_id') ?? null;

        return view('purchase_orders.create', compact(
            'suppliers','requests','selectedSupplierId','selectedRequestId'
        ));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'purchase_request_id' => 'required|exists:purchase_requests,id',
            'order_date' => 'required|date',
            // add more rules as needed
        ]);

        PurchaseOrder::create($data);

        return redirect()->route('purchase-orders.index')->with('success', 'Purchase order created.');
    }

    public function edit(PurchaseOrder $purchaseOrder, Request $request)
    {
        $suppliers = Supplier::all();
        $requests  = PurchaseRequest::all();

        // If selection redirects back from index, read selected id from query
        $selectedSupplierId = $request->query('selected_supplier_id') ?? $purchaseOrder->supplier_id;
        $selectedRequestId  = $request->query('selected_request_id') ?? $purchaseOrder->purchase_request_id;

        return view('purchase_orders.edit', compact(
            'purchaseOrder','suppliers','requests','selectedSupplierId','selectedRequestId'
        ));
    }

    public function update(Request $request, PurchaseOrder $purchaseOrder)
    {
        $data = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'purchase_request_id' => 'required|exists:purchase_requests,id',
            'order_date' => 'required|date',
        ]);

        $purchaseOrder->update($data);

        return redirect()->route('purchase-orders.index')->with('success', 'Purchase order updated.');
    }
}