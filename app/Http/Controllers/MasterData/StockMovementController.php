<?php

namespace App\Http\Controllers\MasterData;

use App\Models\StockMovement;
use App\Models\MasterData\Product;
use App\Models\MasterData\Warehouse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
class StockMovementController extends Controller
{
    public function index()
    {
        $movements = StockMovement::with(['product', 'warehouse', 'sourceWarehouse'])->get();
        return view('stock_movements.index', compact('movements'));
    }

    public function create()
    {
        $products = Product::all();
        $warehouses = Warehouse::all();
        return view('stock_movements.create', compact('products', 'warehouses'));
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

        return redirect()->route('stock_movements.index')->with('success', 'Movement created successfully.');
    }

    public function edit(StockMovement $stock_movement)
    {
        $products = Product::all();
        $warehouses = Warehouse::all();
        return view('stock_movements.edit', [
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

        return redirect()->route('stock_movements.index')->with('success', 'Movement updated successfully.');
    }
    public function transferForm()
    {
        return view('stockmovements.transfer', [
            'products' => Product::all(),
            'warehouses' => Warehouse::all(),
        ]);
    }

    // Transfer between warehouses
    public function transfer(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'from_warehouse' => 'required|exists:warehouses,id',
            'to_warehouse' => 'required|exists:warehouses,id|different:from_warehouse',
            'quantity' => 'required|numeric|min:0.01',
        ]);

        // OUT movement
        StockMovement::create([
            'product_id' => $request->product_id,
            'warehouse_id' => $request->from_warehouse,
            'source_warehouse_id' => $request->from_warehouse,
            'type' => 'transfer_out',
            'quantity' => -$request->quantity,
            'reason' => 'Transfer to warehouse ' . $request->to_warehouse,
            'date' => now(),
        ]);

        // IN movement
        StockMovement::create([
            'product_id' => $request->product_id,
            'warehouse_id' => $request->to_warehouse,
            'source_warehouse_id' => $request->from_warehouse,
            'type' => 'transfer_in',
            'quantity' => $request->quantity,
            'reason' => 'Transfer from warehouse ' . $request->from_warehouse,
            'date' => now(),
        ]);

        return back()->with('success', 'Transfer completed successfully.');
    }
}