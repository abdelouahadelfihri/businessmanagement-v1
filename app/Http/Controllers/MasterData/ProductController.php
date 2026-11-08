<?php

namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;
use App\Models\MasterData\Category;
use App\Models\MasterData\Product;
use App\Models\MasterData\StockMovement;
use App\Models\MasterData\Supplier;
use App\Models\MasterData\Unit;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $stockSub = '(SELECT COALESCE(SUM(ws.quantity), 0) FROM warehouse_stocks ws WHERE ws.product_id = products.id)';

        $products = Product::query()
            ->with(['category', 'unit'])
            ->select('products.*')
            ->selectRaw("$stockSub as stock")
            ->search($request->input('search'))
            ->when($request->filled('category_id'), fn($q) => $q->where('category_id', $request->input('category_id')))
            ->when($request->filled('type'), fn($q) => $q->where('type', $request->input('type')))
            ->when($request->input('status') === 'active', fn($q) => $q->where('is_active', true))
            ->when($request->input('status') === 'inactive', fn($q) => $q->where('is_active', false))
            ->when($request->input('stock') === 'low', fn($q) => $q->where('track_stock', true)->where('reorder_level', '>', 0)
                ->whereRaw("$stockSub > 0 AND $stockSub <= products.reorder_level"))
            ->when($request->input('stock') === 'out', fn($q) => $q->where('track_stock', true)->whereRaw("$stockSub <= 0"))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        $categories = Category::orderBy('name')->get(['id', 'name']);

        return view('masterdata.products.index', compact('products', 'categories'));
    }

    public function create()
    {
        return view('masterdata.products.create', ['product' => new Product()] + $this->formData());
    }

    public function store(Request $request)
    {
        $product = Product::create($this->validated($request));

        return redirect()
            ->route('masterdata.products.show', $product)
            ->with('success', "Product {$product->name} created.");
    }

    public function show(Product $product)
    {
        $product->load(['category', 'unit', 'preferredSupplier', 'warehouseStocks.warehouse']);

        $stock = (float) $product->warehouseStocks->sum('quantity');

        $movements = StockMovement::with(['warehouse', 'sourceWarehouse'])
            ->where('product_id', $product->id)
            ->latest('date')->latest('id')
            ->limit(10)
            ->get();

        return view('masterdata.products.show', compact('product', 'stock', 'movements'));
    }

    public function edit(Product $product)
    {
        return view('masterdata.products.edit', compact('product') + $this->formData());
    }

    public function update(Request $request, Product $product)
    {
        $product->update($this->validated($request, $product));

        return redirect()
            ->route('masterdata.products.show', $product)
            ->with('success', "Product {$product->name} updated.");
    }

    public function destroy(Product $product)
    {
        if ($product->isUsed()) {
            return back()->with('error', "{$product->name} is used in documents or stock movements and cannot be deleted. Mark it as inactive instead.");
        }

        $product->warehouseStocks()->delete(); // only zero-quantity rows can remain here
        $product->delete();

        return redirect()
            ->route('masterdata.products.index')
            ->with('success', "Product {$product->name} deleted.");
    }

    private function formData(): array
    {
        return [
            'categories' => Category::orderBy('name')->get(['id', 'name']),
            'units' => Unit::orderBy('name')->get(),
            'suppliers' => Supplier::where('is_active', true)->orderBy('name')->get(['id', 'name', 'code']),
        ];
    }

    private function validated(Request $request, ?Product $product = null): array
    {
        $data = $request->validate([
            'code' => ['nullable', 'string', 'max:50', Rule::unique('products', 'code')->ignore($product)],
            'bar_code' => ['nullable', 'string', 'max:100', Rule::unique('products', 'bar_code')->ignore($product)],
            'type' => ['required', Rule::in(array_keys(Product::TYPES))],
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:5000',
            'brand' => 'nullable|string|max:100',

            'category_id' => 'nullable|exists:categories,id',
            'unit_id' => 'nullable|exists:units,unit_id',

            'purchase_price' => 'nullable|numeric|min:0',
            'sale_price' => 'nullable|numeric|min:0',
            'tax_rate' => ['required', 'numeric', Rule::in(Product::TAX_RATES)],

            'reorder_level' => 'nullable|integer|min:0',
            'max_stock' => 'nullable|integer|min:0|gte:reorder_level',

            'preferred_supplier_id' => 'nullable|exists:suppliers,id',
            'weight' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:5000',
        ], [
            'max_stock.gte' => 'The maximum stock must be greater than or equal to the reorder level.',
        ]);

        $data['is_active'] = $request->boolean('is_active');
        $data['track_stock'] = $data['type'] !== 'service' && $request->boolean('track_stock');
        $data['purchase_price'] = $data['purchase_price'] ?? 0;
        $data['sale_price'] = $data['sale_price'] ?? 0;
        $data['reorder_level'] = $data['reorder_level'] ?? 0;

        return $data;
    }
}
