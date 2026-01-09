<?php

namespace App\Http\Controllers\MasterData;

use App\Models\MasterData\StockMovement;
use App\Models\MasterData\Product;
use App\Models\MasterData\Warehouse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
class StockMovementController extends Controller
{
    public function index()
    {
        $movements = StockMovement::with(['product', 'warehouse', 'sourceWarehouse'])->get();
        return view('stocksmovements.index', compact('movements'));
    }

    public function create()
    {
        $products = Product::all();
        $warehouses = Warehouse::all();
        return view('stocksmovements.create', compact('products', 'warehouses'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'type' => 'required|in:in,out,transfer_in,transfer_out,adjustment',
            'quantity' => 'required|numeric|min:0.01',
            'date' => 'required|date',
        ]);

        // Example: If movement comes from a PurchaseOrder, link it automatically
        $source_type = $request->source_type ?? null;
        $source_id = $request->source_id ?? null;

        StockMovement::create(array_merge($request->all(), [
            'source_type' => $source_type,
            'source_id' => $source_id
        ]));

        return redirect()->route('stocksmovements.index')->with('success', 'Movement created successfully.');
    }

    public function edit(StockMovement $stock_movement)
    {
        $products = Product::all();
        $warehouses = Warehouse::all();
        return view('stocksmovements.edit', [
            'movement' => $stock_movement,
            'products' => $products,
            'warehouses' => $warehouses
        ]);
    }

    public function update(Request $request, StockMovement $stock_movement)
    {
        $request->validate([
            'warehouse_id' => 'required|exists:warehouses,id',
            'type' => 'required|in:in,out,transfer_in,transfer_out,adjustment',
            'quantity' => 'required|numeric|min:0.01',
            'date' => 'required|date',
        ]);

        $stock_movement->update($request->all());

        return redirect()->route('stocksmovements.index')->with('success', 'Movement updated successfully.');
    }
    public function transferForm()
    {
        return view('stockmovements.transfer', [
            'products' => Product::all(),
            'warehouses' => Warehouse::all(),
        ]);
    }

    public function destroy(StockMovement $stockMovement)
    {
        $stockMovement->delete();
        return redirect()->route('stocksmovements.index')->with('success', 'Purchase invoice deleted successfully.');
    }
}