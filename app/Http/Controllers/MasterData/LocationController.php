<?php
namespace App\Http\Controllers\MasterData;

use App\Models\MasterData\Location;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class LocationController extends Controller
{
    public function index(Request $request)
    {
        $locations = Location::paginate(12); // paginate for big lists

        // selection mode params (if opened from PO)
        $selectFor = $request->query('select_for');    // e.g. 'purchase-order'
        $returnUrl = $request->query('return_url');    // e.g. /purchase-orders/create

        return view('locations.index', compact('locations','selectFor','returnUrl'));
    }

    public function create(Request $request)
    {
        // pass along selection params so create view can return to PO after saving
        $selectFor = $request->query('select_for');
        $returnUrl = $request->query('return_url');

        return view('locations.create', compact('selectFor','returnUrl'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
        ]);

        $category = Location::create($data);

        // If created from a selection flow, redirect back to caller with new id
        if ($request->filled('select_for') && $request->filled('return_url')) {
            // append query param and redirect to return_url
            $return = $request->input('return_url') . '?selected_category_id=' . $category->id;
            return redirect($return);
        }

        return redirect()->route('locations.index')->with('success','Location created.');
    }

    public function edit(Location $supplier)
    {
        return view('locations.edit', compact('unit'));
    }

    public function update(Request $request, Location $supplier)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
        ]);

        $supplier->update($data);

        return redirect()->route('locations.index')->with('success','Location updated.');
    }
}