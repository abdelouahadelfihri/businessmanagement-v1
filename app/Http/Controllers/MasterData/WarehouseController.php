<?php
namespace App\Http\Controllers\MasterData;

use App\Models\MasterData\Warehouse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class WarehouseController extends Controller
{
    public function index(Request $request)
    {
        $warehouses = Warehouse::all();
        return view('warehouses.index', compact('$warehouses'));
    }

    public function create(Request $request)
    {
        return view('warehouses.create');
    }

    public function store(Request $request)
    {
        Warehouse::create($request->all());
        return redirect()->route('warehouses.index');
    }

    public function edit(Warehouse $warehouse)
    {
        return view('warehouses.edit', compact('warehouse'));
    }

    public function update(Request $request, Warehouse $warehouse)
    {
        $warehouse->update($request->all());
        return redirect()->route('warehouses.index');
    }
    public function destroy(Warehouse $warehouse)
    {
        $warehouse->delete();
        return redirect()
            ->route('warehouses.index')
            ->with('success', 'Warehouse deleted successfully.');
    }
}