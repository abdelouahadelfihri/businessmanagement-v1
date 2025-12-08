<?php

namespace App\Http\Controllers\MasterData;

use App\Models\Location;
use Illuminate\Http\Request;

class LocationController extends Controller
{
    // Display a listing of the resource
    public function index()
    {
        return response()->json(Location::all());
    }

    // Store a newly created resource in storage
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'required|string|max:255',
        ]);

        $location = Location::create($validated);

        return response()->json([
            'message' => 'Location created successfully',
            'data' => $location,
        ], 201);
    }

    // Display the specified resource
    public function show($location_id)
    {
        $location = Location::findOrFail($location_id);
        return response()->json($location);
    }

    // Update the specified resource in storage
    public function update(Request $request, $location_id)
    {
        $location = Location::findOrFail($location_id);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'address' => 'sometimes|string|max:255',
        ]);

        $location->update($validated);

        return response()->json([
            'message' => 'Location updated successfully',
            'data' => $location,
        ]);
    }

    // Remove the specified resource from storage
    public function destroy($location_id)
    {
        $location = Location::findOrFail($location_id);
        $location->delete();

        return response()->json([
            'message' => 'Location deleted successfully'
        ]);
    }
}