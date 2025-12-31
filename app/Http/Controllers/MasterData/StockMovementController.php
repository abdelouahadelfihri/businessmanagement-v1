<?php
namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;
use App\Models\MasterData\StockMovement;
use Illuminate\Http\Request;

class StockMovementController extends Controller
{
    public function index()
    {
        $stockMovements = StockMovement::all();
        return view('stocksmovements.index', compact('stockMovements'));
    }

    public function create()
    {
        return view('stocksmovements.create');
    }

    public function store(Request $request)
    {
        StockMovement::create($request->all());
        return redirect()->route('stocksmovements.index');
    }

    public function edit(StockMovement $supplier)
    {
        return view('stocksmovements.edit', compact('supplier'));
    }

    public function update(Request $request, StockMovement $supplier)
    {
        $supplier->update($request->all());
        return redirect()->route('stocksmovements.index');
    }

    // AJAX store for modal
    public function ajaxStore(Request $request)
    {
        $supplier = StockMovement::create(['name' => $request->name]);
        return response()->json($supplier);
    }
}