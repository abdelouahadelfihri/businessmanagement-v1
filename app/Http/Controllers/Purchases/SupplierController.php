<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SupplierController extends Controller
{
    public function index(Request $request)
    {
        $q = $request->query('q');
        $query = Supplier::query();

        if ($q) {
            $query->where(function ($q2) use ($q) {
                $q2->where('name', 'like', "%{$q}%")
                    ->orWhere('email', 'like', "%{$q}%")
                    ->orWhere('phone', 'like', "%{$q}%");
            });
        }

        $suppliers = $query->orderBy('name')->paginate(10)->withQueryString();

        return view('suppliers.index', compact('suppliers', 'q'));
    }

    public function create()
    {
        return view('suppliers.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:1000',
        ]);

        $supplier = Supplier::create($data);

        return redirect()->route('suppliers.index')->with('success', 'Supplier added.');
    }

    public function show(Supplier $supplier)
    {
        return view('suppliers.show', compact('supplier'));
    }

    public function edit(Supplier $supplier)
    {
        return view('suppliers.edit', compact('supplier'));
    }

    public function update(Request $request, Supplier $supplier)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('suppliers')->ignore($supplier->id)],
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:1000',
        ]);

        $supplier->update($data);

        return redirect()->route('suppliers.index')->with('success', 'Supplier updated.');
    }

    public function destroy(Supplier $supplier)
    {
        $supplier->delete();

        return redirect()->route('suppliers.index')->with('success', 'Supplier deleted.');
    }

    public function storeQuick(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:500',
        ]);

        $supplier = \App\Models\Supplier::create($validated);

        return response()->json([
            'success' => true,
            'supplier' => $supplier
        ]);
    }

}