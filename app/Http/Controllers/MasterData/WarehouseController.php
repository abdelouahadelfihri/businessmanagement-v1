<?php

namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;
use App\Models\MasterData\Warehouse;
use Illuminate\Http\Request;

class WarehouseController extends Controller
{
    public function index()
    {
        return Warehouse::with(['location', 'originTransfers', 'destinationTransfers'])->get();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'is_refrigerated' => 'boolean',
            'location_owner_id' => 'required|exists:locations,location_id',
        ]);

        $warehouse = Warehouse::create($validated);

        return response()->json($warehouse, 201);
    }

    public function show($id)
    {
        $warehouse = Warehouse::with(['location', 'originTransfers', 'destinationTransfers'])->findOrFail($id);
        return response()->json($warehouse);
    }

    public function update(Request $request, $id)
    {
        $warehouse = Warehouse::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'is_refrigerated' => 'boolean',
            'location_owner_id' => 'sometimes|required|exists:locations,location_id',
        ]);

        $warehouse->update($validated);

        return response()->json($warehouse);
    }

    public function destroy($id)
    {
        $warehouse = Warehouse::findOrFail($id);
        $warehouse->delete();

        return response()->json(['message' => 'Warehouse deleted successfully']);
    }
}