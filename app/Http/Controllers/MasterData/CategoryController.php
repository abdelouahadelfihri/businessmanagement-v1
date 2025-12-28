<?php
namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;
use App\Models\MasterData\Category;
use Illuminate\Http\Request;

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
        Category::create($request->all());
        return redirect()->route('categories.index');
    }

    public function edit(Category $supplier)
    {
        return view('categories.edit', compact('supplier'));
    }

    public function update(Request $request, Category $supplier)
    {
        $supplier->update($request->all());
        return redirect()->route('categories.index');
    }

    // AJAX store for modal
    public function ajaxStore(Request $request)
    {
        $supplier = Category::create(['name' => $request->name]);
        return response()->json($supplier);
    }
}