<?php

namespace App\Http\Controllers\MasterData;

use App\Models\Models\MasterData\Inventory;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class InventoryController extends Controller
{
    /**
     * Display a listing of inventories.
     */
    public function index()
    {
        $inventories = Inventory::with(['product', 'warehouse'])->get();

        return view('inventories.index', compact('inventories'));
    }

    /**
     * Show the form for creating a new inventory record.
     */
    public function create()
    {
        $products = Product::all();
        $warehouses = Warehouse::all();

        return view('inventories.create', compact('products', 'warehouses'));
    }

    /**
     * Store a newly created inventory in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'product_id'            => 'required|exists:products,id',
            'warehouse_id'          => 'required|exists:warehouses,id',
            'quantity_available'    => 'required|integer|min:0',
            'minimum_stock_level'   => 'nullable|integer|min:0',
            'maximum_stock_level'   => 'nullable|integer|min:0',
            'reorder_point'         => 'nullable|integer|min:0',
        ]);

        Inventory::create($request->all());

        return redirect()
            ->route('inventories.index')
            ->with('success', 'Inventory created successfully.');
    }

    /**
     * Show the form for editing the specified inventory.
     */
    public function edit($inventory_id)
    {
        $inventory = Inventory::findOrFail($inventory_id);
        $products = Product::all();
        $warehouses = Warehouse::all();

        return view('inventories.edit', compact('inventory', 'products', 'warehouses'));
    }

    /**
     * Update the specified inventory in storage.
     */
    public function update(Request $request, $inventory_id)
    {
        $inventory = Inventory::findOrFail($inventory_id);

        $request->validate([
            'product_id'            => 'required|exists:products,id',
            'warehouse_id'          => 'required|exists:warehouses,id',
            'quantity_available'    => 'required|integer|min:0',
            'minimum_stock_level'   => 'nullable|integer|min:0',
            'maximum_stock_level'   => 'nullable|integer|min:0',
            'reorder_point'         => 'nullable|integer|min:0',
        ]);

        $inventory->update($request->all());

        return redirect()
            ->route('inventories.index')
            ->with('success', 'Inventory updated successfully.');
    }

    /**
     * Remove the specified inventory from storage.
     */
    public function destroy($inventory_id)
    {
        $inventory = Inventory::findOrFail($inventory_id);
        $inventory->delete();

        return redirect()
            ->route('inventories.index')
            ->with('success', 'Inventory deleted successfully.');
    }
}