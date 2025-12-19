<?php
namespace App\Http\Controllers\MasterData;

use App\Models\MasterData\StockMovement;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class StockMovementController extends Controller
{
    public function index(Request $request)
    {
        $categories = Category::paginate(12); // paginate for big lists

        // selection mode params (if opened from PO)
        $selectFor = $request->query('select_for');    // e.g. 'purchase-order'
        $returnUrl = $request->query('return_url');    // e.g. /purchase-orders/create

        return view('categories.index', compact('categories', 'selectFor', 'returnUrl'));
    }

    public function create(Request $request)
    {
        // pass along selection params so create view can return to PO after saving
        $selectFor = $request->query('select_for');
        $returnUrl = $request->query('return_url');

        return view('categories.create', compact('selectFor', 'returnUrl'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
        ]);

        $category = Category::create($data);

        // If created from a selection flow, redirect back to caller with new id
        if ($request->filled('select_for') && $request->filled('return_url')) {
            // append query param and redirect to return_url
            $return = $request->input('return_url') . '?selected_category_id=' . $category->id;
            return redirect($return);
        }

        return redirect()->route('categories.index')->with('success', 'Category created.');
    }

    public function edit(Category $supplier)
    {
        return view('categories.edit', compact('unit'));
    }

    public function update(Request $request, Category $supplier)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
        ]);

        $supplier->update($data);

        return redirect()->route('categories.index')->with('success', 'Category updated.');
    }

    public function destroy(Category $category)
    {
        $category->delete();

        return redirect()
            ->route('categories.index')
            ->with('success', 'Category deleted.');
    }

}