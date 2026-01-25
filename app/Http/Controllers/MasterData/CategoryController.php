<?php
namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;
use App\Models\MasterData\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::all();
        return view('categories.index', compact('categories'));
    }

    public function create()
    {
        return view('categories.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:categories,name',
            'description' => 'nullable|string',
        ]);

        Category::create($request->all());

        return redirect()->route('categories.index');
    }
    public function edit(Category $category)
    {
        return view('categories.edit', compact('location'));
    }

    public function update(Request $request, Category $category)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:categories,name,' . $category->id,
            'description' => 'nullable|string',
        ]);

        $category->update($request->all());

        return redirect()->route('categories.index');
    }
    // AJAX store for modal
    public function ajaxStore(Request $request)
    {
        $category = Category::create(['name' => $request->name]);
        return response()->json($category);
    }

    public function destroy(Category $category)
    {
        $category->delete();

        // If table is empty, reset primary key counter
        if (Category::count() === 0) {
            DB::statement('ALTER TABLE categories AUTO_INCREMENT = 1');
        }

        return redirect()
            ->route('categories.index')
            ->with('success', 'Category deleted successfully.');
    }
}