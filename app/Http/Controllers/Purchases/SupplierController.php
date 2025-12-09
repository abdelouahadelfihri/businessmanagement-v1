<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    // List suppliers (support popup mode)
    public function index(Request $request)
    {
        $suppliers = Supplier::orderBy('name')->get();
        return view('suppliers.index', compact('suppliers'));
    }

    // Show create form
    public function create()
    {
        return view('suppliers.create');
    }

    // Store supplier (standard)
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'=>'required|string|max:255',
            'email'=>'nullable|email|max:255',
            'phone'=>'nullable|string|max:50',
            'address'=>'nullable|string|max:500',
        ]);

        Supplier::create($validated);
        return redirect()->route('suppliers.index')->with('success','Supplier created successfully.');
    }

    // Store supplier quickly (AJAX from modal)
    public function storeQuick(Request $request)
    {
        $validated = $request->validate([
            'name'=>'required|string|max:255',
            'email'=>'nullable|email|max:255',
            'phone'=>'nullable|string|max:50',
            'address'=>'nullable|string|max:500',
        ]);

        $supplier = Supplier::create($validated);

        return response()->json([
            'success'=>true,
            'supplier'=>$supplier
        ]);
    }

    // Show edit form
    public function edit(Supplier $supplier)
    {
        return view('suppliers.edit', compact('supplier'));
    }

    // Update supplier
    public function update(Request $request, Supplier $supplier)
    {
        $validated = $request->validate([
            'name'=>'required|string|max:255',
            'email'=>'nullable|email|max:255',
            'phone'=>'nullable|string|max:50',
            'address'=>'nullable|string|max:500',
        ]);

        $supplier->update($validated);
        return redirect()->route('suppliers.index')->with('success','Supplier updated successfully.');
    }

    // Delete supplier
    public function destroy(Supplier $supplier)
    {
        $supplier->delete();
        return redirect()->route('suppliers.index')->with('success','Supplier deleted successfully.');
    }
}