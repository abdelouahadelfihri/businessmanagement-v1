<?php
namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;
use App\Models\MasterData\Location;
use Illuminate\Http\Request;

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

    public function edit(Location $supplier)
    {
        return view('locations.edit', compact('supplier'));
    }

    public function update(Request $request, Location $supplier)
    {
        $supplier->update($request->all());
        return redirect()->route('locations.index');
    }

    // AJAX store for modal
    public function ajaxStore(Request $request)
    {
        $supplier = Location::create(['name' => $request->name]);
        return response()->json($supplier);
    }
}