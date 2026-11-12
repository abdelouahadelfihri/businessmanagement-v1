<?php

namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;
use App\Models\MasterData\Location;
use App\Models\MasterData\StockMovement;
use App\Models\MasterData\Warehouse;
use App\Models\MasterData\WarehouseStock;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class WarehouseController extends Controller
{
    public function index(Request $request)
    {
        $warehouses = Warehouse::query()
            ->with('location')
            ->withCount(['stocks as products_count' => fn($q) => $q->where('quantity', '>', 0)])
            ->withSum('stocks as total_quantity', 'quantity')
            ->search($request->input('search'))
            ->when($request->filled('type'), fn($q) => $q->where('type', $request->input('type')))
            ->when($request->filled('location_id'), fn($q) => $q->where('location_id', $request->input('location_id')))
            ->when($request->input('status') === 'active', fn($q) => $q->where('is_active', true))
            ->when($request->input('status') === 'inactive', fn($q) => $q->where('is_active', false))
            ->ordered()
            ->paginate(15)
            ->withQueryString();

        // Stock value at cost per warehouse (one query for the page)
        $values = WarehouseStock::query()
            ->join('products', 'products.id', '=', 'warehouse_stocks.product_id')
            ->whereIn('warehouse_id', $warehouses->pluck('id'))
            ->groupBy('warehouse_id')
            ->selectRaw('warehouse_id, SUM(warehouse_stocks.quantity * products.purchase_price) as value')
            ->pluck('value', 'warehouse_id');

        $locations = Location::orderBy('name')->get(['location_id', 'name']);

        return view('masterdata.warehouses.index', compact('warehouses', 'values', 'locations'));
    }

    public function create()
    {
        return view('masterdata.warehouses.create', ['warehouse' => new Warehouse(['type' => 'main', 'is_active' => true])] + $this->formData());
    }

    public function store(Request $request)
    {
        $warehouse = Warehouse::create($this->validated($request));

        return redirect()->route('masterdata.warehouses.show', $warehouse)->with('success', "Warehouse {$warehouse->name} created.");
    }

    public function show(Request $request, Warehouse $warehouse)
    {
        $warehouse->load('location');

        $stockQuery = WarehouseStock::query()
            ->join('products', 'products.id', '=', 'warehouse_stocks.product_id')
            ->where('warehouse_stocks.warehouse_id', $warehouse->id);

        $stats = (clone $stockQuery)
            ->selectRaw('SUM(CASE WHEN warehouse_stocks.quantity > 0 THEN 1 ELSE 0 END) as products')
            ->selectRaw('COALESCE(SUM(warehouse_stocks.quantity), 0) as quantity')
            ->selectRaw('COALESCE(SUM(warehouse_stocks.quantity * products.purchase_price), 0) as cost_value')
            ->selectRaw('COALESCE(SUM(warehouse_stocks.quantity * products.sale_price), 0) as sale_value')
            ->first();

        $stocks = WarehouseStock::with(['product.unit', 'product.category'])
            ->where('warehouse_id', $warehouse->id)
            ->where('quantity', '!=', 0)
            ->when($request->filled('q'), fn($q) => $q->whereHas('product', fn($p) => $p->search($request->input('q'))))
            ->orderByDesc('quantity')
            ->paginate(15, ['*'], 'stock_page')
            ->withQueryString();

        $movements = StockMovement::with(['product', 'warehouse', 'sourceWarehouse'])
            ->where(fn($q) => $q->where('warehouse_id', $warehouse->id)->orWhere('source_warehouse_id', $warehouse->id))
            ->latest('date')->latest('id')
            ->limit(10)
            ->get();

        $canDelete = ! $warehouse->isUsed();

        return view('masterdata.warehouses.show', compact('warehouse', 'stats', 'stocks', 'movements', 'canDelete'));
    }

    public function edit(Warehouse $warehouse)
    {
        return view('masterdata.warehouses.edit', compact('warehouse') + $this->formData());
    }

    public function update(Request $request, Warehouse $warehouse)
    {
        $data = $this->validated($request, $warehouse);

        // Keep a default warehouse: the current default cannot simply be unset
        if ($warehouse->is_default && ! $data['is_default']) {
            $data['is_default'] = true;
            session()->flash('info', 'This warehouse stays the default. Make another warehouse the default to change it.');
        }

        $warehouse->update($data);

        return redirect()->route('masterdata.warehouses.show', $warehouse)->with('success', "Warehouse {$warehouse->name} updated.");
    }

    public function destroy(Warehouse $warehouse)
    {
        if ($warehouse->isUsed()) {
            return back()->with('error', "{$warehouse->name} holds stock or is used in movements and documents. Mark it as inactive instead.");
        }

        $wasDefault = $warehouse->is_default;
        $warehouse->stocks()->delete(); // only zero-quantity rows can remain
        $warehouse->delete();

        if ($wasDefault && ($next = Warehouse::active()->ordered()->first())) {
            $next->update(['is_default' => true]);
        }

        return redirect()->route('masterdata.warehouses.index')->with('success', "Warehouse {$warehouse->name} deleted.");
    }

    /** Make this warehouse the default one. */
    public function makeDefault(Warehouse $warehouse)
    {
        if (! $warehouse->is_active) {
            return back()->with('error', 'An inactive warehouse cannot be the default.');
        }

        $warehouse->update(['is_default' => true]);

        return back()->with('success', "{$warehouse->name} is now the default warehouse.");
    }

    /* ------------------------------------------------------------------ */

    private function formData(): array
    {
        return ['locations' => Location::orderBy('name')->get()];
    }

    private function validated(Request $request, ?Warehouse $warehouse = null): array
    {
        $data = $request->validate([
            'code' => ['nullable', 'string', 'max:20', Rule::unique('warehouses', 'code')->ignore($warehouse)],
            'name' => ['required', 'string', 'max:255', Rule::unique('warehouses', 'name')->ignore($warehouse)],
            'type' => ['required', Rule::in(array_keys(Warehouse::TYPES))],
            'location_id' => 'nullable|exists:locations,location_id',
            'manager_name' => 'nullable|string|max:100',
            'phone' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string|max:1000',
            'capacity_m2' => 'nullable|numeric|min:0|max:99999999',
            'min_temperature' => 'nullable|numeric|between:-50,50',
            'max_temperature' => 'nullable|numeric|between:-50,50|gte:min_temperature',
            'notes' => 'nullable|string|max:5000',
        ], [
            'max_temperature.gte' => 'The maximum temperature must be greater than or equal to the minimum.',
        ]);

        $data['is_refrigerated'] = $request->boolean('is_refrigerated');
        $data['is_default'] = $request->boolean('is_default');
        $data['is_active'] = $request->boolean('is_active');

        if ($data['is_default'] && ! $data['is_active']) {
            $data['is_default'] = false;
        }

        return $data;
    }
}
