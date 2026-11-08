<?php

namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;
use App\Models\MasterData\Category;
use App\Models\MasterData\Product;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $categories = Category::query()
            ->with('parent')
            ->withCount(['products', 'children'])
            ->search($request->input('search'))
            ->when($request->input('level') === 'root', fn($q) => $q->whereNull('parent_id'))
            ->when($request->filled('parent_id'), fn($q) => $q->where('parent_id', $request->input('parent_id')))
            ->when($request->input('status') === 'active', fn($q) => $q->where('is_active', true))
            ->when($request->input('status') === 'inactive', fn($q) => $q->where('is_active', false))
            ->ordered()
            ->paginate(20)
            ->withQueryString();

        $parents = Category::whereHas('children')->ordered()->get(['id', 'name']);

        return view('masterdata.categories.index', compact('categories', 'parents'));
    }

    public function create(Request $request)
    {
        $category = new Category(['parent_id' => $request->query('parent_id')]);

        return view('masterdata.categories.create', compact('category') + $this->formData());
    }

    public function store(Request $request)
    {
        $category = Category::create($this->validated($request));

        return redirect()
            ->route('masterdata.categories.show', $category)
            ->with('success', "Category {$category->name} created.");
    }

    public function show(Category $category)
    {
        $category->load(['parent', 'children' => fn($q) => $q->withCount('products')]);

        $ids = $category->descendantIds();
        $stockSub = '(SELECT COALESCE(SUM(ws.quantity), 0) FROM warehouse_stocks ws WHERE ws.product_id = products.id)';

        $products = Product::with('unit')
            ->select('products.*')
            ->selectRaw("$stockSub as stock")
            ->where('category_id', $category->id)
            ->orderBy('name')
            ->paginate(15);

        $stats = Product::whereIn('category_id', $ids)
            ->selectRaw('COUNT(*) as total')
            ->selectRaw('SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) as active')
            ->selectRaw("COALESCE(SUM(CASE WHEN track_stock = 1 THEN $stockSub * purchase_price ELSE 0 END), 0) as stock_value")
            ->selectRaw('AVG(NULLIF(sale_price, 0)) as avg_price')
            ->first();

        return view('masterdata.categories.show', compact('category', 'products', 'stats'));
    }

    public function edit(Category $category)
    {
        return view('masterdata.categories.edit', compact('category') + $this->formData($category));
    }

    public function update(Request $request, Category $category)
    {
        $category->update($this->validated($request, $category));

        return redirect()
            ->route('masterdata.categories.show', $category)
            ->with('success', "Category {$category->name} updated.");
    }

    public function destroy(Category $category)
    {
        if ($category->children()->exists()) {
            return back()->with('error', "{$category->name} has sub-categories. Move or delete them first.");
        }
        if ($category->products()->exists()) {
            return back()->with('error', "{$category->name} still contains products. Move them to another category first, or mark the category as inactive.");
        }

        $category->delete();

        return redirect()
            ->route('masterdata.categories.index')
            ->with('success', "Category {$category->name} deleted.");
    }

    /** Quick create from the category picker modal (returns JSON). */
    public function ajaxStore(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255|unique:categories,name',
            'description' => 'nullable|string|max:1000',
        ]);

        return response()->json(Category::create($data)->fresh());
    }

    private function formData(?Category $category = null): array
    {
        // A category cannot be moved under itself or one of its own sub-categories
        $excluded = $category ? $category->descendantIds() : [];

        return [
            'parents' => Category::whereNotIn('id', $excluded)->ordered()->get(['id', 'name', 'parent_id']),
        ];
    }

    private function validated(Request $request, ?Category $category = null): array
    {
        $excluded = $category ? $category->descendantIds() : [];

        $data = $request->validate([
            'code' => ['nullable', 'string', 'max:20', Rule::unique('categories', 'code')->ignore($category)],
            'name' => ['required', 'string', 'max:255', Rule::unique('categories', 'name')->ignore($category)],
            'parent_id' => ['nullable', 'exists:categories,id', Rule::notIn($excluded)],
            'description' => 'nullable|string|max:1000',
            'color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'icon' => ['required', Rule::in(Category::ICONS)],
            'default_tax_rate' => ['nullable', Rule::in(\App\Models\MasterData\Product::TAX_RATES)],
            'sort_order' => 'nullable|integer|min:0|max:9999',
        ], [
            'parent_id.not_in' => 'A category cannot be placed under itself or one of its sub-categories.',
        ]);

        $data['is_active'] = $request->boolean('is_active');
        $data['sort_order'] = $data['sort_order'] ?? 0;
        $data['default_tax_rate'] = $request->filled('default_tax_rate') ? $data['default_tax_rate'] : null;

        return $data;
    }
}
