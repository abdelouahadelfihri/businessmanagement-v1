<?php
namespace App\Http\Controllers\Sales;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Sales\SaleOrder;

class SaleOrderController extends Controller
{
    public function index(Request $request)
    {
        $salesOrders = SaleOrder::paginate(12); // paginate for big lists

        // selection mode params (if opened from PO)
        $selectFor = $request->query('select_for');    // e.g. 'purchase-order'
        $returnUrl = $request->query('return_url');    // e.g. /purchase-orders/create

        return view('salesordersindex', compact('salesOrders','selectFor','returnUrl'));
    }

    public function create(Request $request)
    {
        // pass along selection params so create view can return to PO after saving
        $selectFor = $request->query('select_for');
        $returnUrl = $request->query('return_url');

        return view('salesorderscreate', compact('selectFor','returnUrl'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
        ]);

        $saleOrder = SaleOrder::create($data);

        // If created from a selection flow, redirect back to caller with new id
        if ($request->filled('select_for') && $request->filled('return_url')) {
            // append query param and redirect to return_url
            $return = $request->input('return_url') . '?selected_sale_order_id=' . $saleOrder->id;
            return redirect($return);
        }

        return redirect()->route('salesordersindex')->with('success','SaleOrder created.');
    }

    public function edit(SaleOrder $saleOrder)
    {
        return view('salesorders.edit', compact('saleOrder'));
    }

    public function update(Request $request, SaleOrder $saleOrder)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
        ]);

        $saleOrder->update($data);

        return redirect()->route('salesorders.index')->with('success','SaleOrder updated.');
    }
}