<?php
namespace App\Http\Controllers\MasterData;

use App\Models\MasterData\Warehouse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class WarehouseController extends Controller
{
    public function index(Request $request)
    {
        $warehouses = Warehouse::paginate(12); // paginate for big lists

        // selection mode params (if opened from PO)
        $selectFor = $request->query('select_for');    // e.g. 'purchase-order'
        $returnUrl = $request->query('return_url');    // e.g. /purchase-orders/create

        return view('warehouses.index', compact('warehouses','selectFor','returnUrl'));
    }

    public function create(Request $request)
    {
        // pass along selection params so create view can return to PO after saving
        $selectFor = $request->query('select_for');
        $returnUrl = $request->query('return_url');

        return view('warehouses.create', compact('selectFor','returnUrl'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
        ]);

        $category = Warehouse::create($data);

        // If created from a selection flow, redirect back to caller with new id
        if ($request->filled('select_for') && $request->filled('return_url')) {
            // append query param and redirect to return_url
            $return = $request->input('return_url') . '?selected_product_id=' . $category->id;
            return redirect($return);
        }

        return redirect()->route('warehouses.index')->with('success','Warehouse created.');
    }

    public function edit(Warehouse $supplier)
    {
        return view('warehouses.edit', compact('unit'));
    }

    public function update(Request $request, Warehouse $supplier)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
        ]);

        $supplier->update($data);

        return redirect()->route('warehouses.index')->with('success','Warehouse updated.');
    }
}