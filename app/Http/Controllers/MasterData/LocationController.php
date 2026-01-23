<?php
namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;
use App\Models\MasterData\Location;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LocationController extends Controller
{
    public function index()
    {
        $locations = Location::all();
        return view('locations.index', compact('locations'));
    }

    public function create()
    {
        return view('locations.create');
    }

    public function store(Request $request)
    {
        Location::create($request->all());
        return redirect()->route('locations.index');
    }
    public function edit(Location $location)
    {
        return view('locations.edit', compact('location'));
    }

    public function update(Request $request, Location $location)
    {
        $location->update($request->all());
        return redirect()->route('locations.index');
    }
    // AJAX store for modal
    public function ajaxStore(Request $request)
    {
        $location = Location::create(['name' => $request->name]);
        return response()->json($location);
    }

    public function destroy(Location $location)
    {
        $location->delete();

        // If table is empty, reset primary key counter
        if (Location::count() === 0) {
            DB::statement('ALTER TABLE categories AUTO_INCREMENT = 1');
        }

        return redirect()
            ->route('locations.index')
            ->with('success', 'Location deleted successfully.');
    }
}