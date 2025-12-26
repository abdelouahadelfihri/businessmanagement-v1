<?php

namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;
use App\Models\MasterData\Supplier;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    /**
     * Show list of suppliers (index)
     */
    public function index(Request $request)
    {
        $suppliers = Supplier::paginate(12);

        return view('suppliers.index', compact('suppliers'));
    }

    /**
     * Show create form
     */
    public function create()
    {
        return view('suppliers.create');
    }

    /**
     * Store Supplier
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email',
            'phone' => 'nullable|string|max:50',
        ]);

        Supplier::create($data);

        return redirect()->route('suppliers.index')
            ->with('success', 'Supplier created successfully.');
    }

    /**
     * Show edit form
     */
    public function edit(Supplier $supplier)
    {
        return view('suppliers.edit', compact('supplier'));
    }

    /**
     * Update Supplier
     */
    public function update(Request $request, Supplier $supplier)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email',
            'phone' => 'nullable|string|max:50',
        ]);

        $supplier->update($data);

        return redirect()->route('suppliers.index')
            ->with('success', 'Supplier updated successfully.');
    }

    /**
     * Delete Supplier
     */
    public function destroy(Supplier $supplier)
    {
        $supplier->delete();

        return redirect()->route('suppliers.index')
            ->with('success', 'Supplier deleted successfully.');
    }

    /* =========================================================================
       AJAX API ENDPOINTS FOR MODAL PICKER (Option A)
       ========================================================================= */

    /**
     * Returns paginated supplier list in JSON for modal picker
     */
    public function ajaxList(Request $request)
    {
        $search = $request->query('search');

        $query = Supplier::query();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%$search%")
                    ->orWhere('email', 'like', "%$search%")
                    ->orWhere('phone', 'like', "%$search%");
            });
        }

        return response()->json([
            'data' => $query->orderBy('name')->paginate(10)
        ]);
    }

    /**
     * Creates a supplier via AJAX from within modal
     */
    public function ajaxCreate(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email',
            'phone' => 'nullable|string|max:50',
        ]);

        $supplier = Supplier::create($data);

        return response()->json([
            'success' => true,
            'supplier' => $supplier
        ]);
    }
}