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
        // ✅ VALIDATION
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'is_refrigerated' => 'nullable|boolean',
            'location_owner_id' => 'required|integer|exists:locations,location_id',
        ]);

        // CREATE
        Warehouse::create($validated);

        return redirect()->route('warehouses.index')
            ->with('success', 'Warehouse created successfully');
    }
    public function edit(Warehouse $warehouse)
    {
        return view('warehouses.edit', compact('warehouse'));
    }

    public function update(Request $request, Warehouse $warehouse)
    {
        // ✅ VALIDATION
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'is_refrigerated' => 'nullable|boolean',
            'location_owner_id' => 'required|integer|exists:locations,location_id',
        ]);

        // UPDATE
        $warehouse->update($validated);

        return redirect()->route('warehouses.index')
            ->with('success', 'Warehouse updated successfully');
    }
    public function destroy(Warehouse $warehouse)
    {
        $warehouse->delete();
        return redirect()
            ->route('warehouses.index')
            ->with('success', 'Warehouse deleted successfully.');
    }
}