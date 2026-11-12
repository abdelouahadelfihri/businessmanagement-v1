<?php

namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;
use App\Models\MasterData\Location;
use App\Models\MasterData\WarehouseStock;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class LocationController extends Controller
{
    public function index(Request $request)
    {
        $locations = Location::query()
            ->withCount('warehouses')
            ->search($request->input('search'))
            ->when($request->filled('type'), fn($q) => $q->where('type', $request->input('type')))
            ->when($request->filled('city'), fn($q) => $q->where('city', $request->input('city')))
            ->when($request->input('status') === 'active', fn($q) => $q->where('is_active', true))
            ->when($request->input('status') === 'inactive', fn($q) => $q->where('is_active', false))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        $cities = Location::whereNotNull('city')->distinct()->orderBy('city')->pluck('city');

        return view('masterdata.locations.index', compact('locations', 'cities'));
    }

    public function create()
    {
        return view('masterdata.locations.create', ['location' => new Location(['type' => 'warehouse_site', 'country' => 'Morocco', 'is_active' => true])]);
    }

    public function store(Request $request)
    {
        $location = Location::create($this->validated($request));

        return redirect()->route('masterdata.locations.show', $location)->with('success', "Location {$location->name} created.");
    }

    public function show(Location $location)
    {
        $warehouses = $location->warehouses()
            ->withCount(['stocks as products_count' => fn($q) => $q->where('quantity', '>', 0)])
            ->withSum('stocks as total_quantity', 'quantity')
            ->ordered()
            ->get();

        $values = WarehouseStock::query()
            ->join('products', 'products.id', '=', 'warehouse_stocks.product_id')
            ->whereIn('warehouse_id', $warehouses->pluck('id'))
            ->groupBy('warehouse_id')
            ->selectRaw('warehouse_id, SUM(warehouse_stocks.quantity * products.purchase_price) as value')
            ->pluck('value', 'warehouse_id');

        $stats = [
            'warehouses' => $warehouses->count(),
            'active' => $warehouses->where('is_active', true)->count(),
            'capacity' => (float) $warehouses->sum('capacity_m2'),
            'stock_value' => (float) $values->sum(),
        ];

        return view('masterdata.locations.show', compact('location', 'warehouses', 'values', 'stats'));
    }

    public function edit(Location $location)
    {
        return view('masterdata.locations.edit', compact('location'));
    }

    public function update(Request $request, Location $location)
    {
        $location->update($this->validated($request, $location));

        return redirect()->route('masterdata.locations.show', $location)->with('success', "Location {$location->name} updated.");
    }

    public function destroy(Location $location)
    {
        if ($location->warehouses()->exists()) {
            return back()->with('error', "{$location->name} still has warehouses. Move them to another location first, or mark it as inactive.");
        }

        $location->delete();

        return redirect()->route('masterdata.locations.index')->with('success', "Location {$location->name} deleted.");
    }

    /** Quick create from the location picker modal (returns JSON). */
    public function ajaxStore(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255|unique:locations,name',
            'address' => 'nullable|string|max:255',
        ]);

        return response()->json(Location::create($data)->fresh());
    }

    private function validated(Request $request, ?Location $location = null): array
    {
        $data = $request->validate([
            'code' => ['nullable', 'string', 'max:20', Rule::unique('locations', 'code')->ignore($location?->location_id, 'location_id')],
            'name' => ['required', 'string', 'max:255', Rule::unique('locations', 'name')->ignore($location?->location_id, 'location_id')],
            'type' => ['required', Rule::in(array_keys(Location::TYPES))],
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:100',
            'latitude' => 'nullable|required_with:longitude|numeric|between:-90,90',
            'longitude' => 'nullable|required_with:latitude|numeric|between:-180,180',
            'contact_name' => 'nullable|string|max:100',
            'phone' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:255',
            'notes' => 'nullable|string|max:5000',
        ], [
            'latitude.required_with' => 'Enter both latitude and longitude, or leave both empty.',
            'longitude.required_with' => 'Enter both latitude and longitude, or leave both empty.',
        ]);

        $data['country'] = $data['country'] ?: 'Morocco';
        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }
}
