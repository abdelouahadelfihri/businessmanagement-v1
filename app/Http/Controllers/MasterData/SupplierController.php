<?php
namespace App\Http\Controllers\MasterData;

use App\Models\MasterData\Supplier;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class SupplierController extends Controller
{
    public function index(Request $request)
    {
        $suppliers = Supplier::paginate(10);

        return view('suppliers.index', [
            'suppliers' => $suppliers,
            'selectFor' => $request->query('select_for'),
            'returnUrl' => $request->query('return_url'),
        ]);
    }


    public function create(Request $request)
    {
        // pass along selection params so create view can return to PO after saving
        $selectFor = $request->query('select_for');
        $returnUrl = $request->query('return_url');

        return view('suppliers.create', compact('selectFor', 'returnUrl'));
    }


    public function store(Request $request)
    {
        $supplier = Supplier::create($request->only('name', 'email'));

        if ($request->filled('return_url')) {
            $returnUrl = $request->input('return_url');

            return redirect(
                $returnUrl .
                (str_contains($returnUrl, '?') ? '&' : '?') .
                'selected_supplier_id=' . $supplier->id
            );
        }

        return redirect()->route('suppliers.index');
    }


    /*public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
        ]);

        $supplier = Supplier::create($data);

        // If created from a selection flow, redirect back to caller with new id
        if ($request->filled('select_for') && $request->filled('return_url')) {
            // append query param and redirect to return_url
            $return = $request->input('return_url') . '?selected_supplier_id=' . $supplier->id;
            return redirect($return);
        }

        return redirect()->route('suppliers.index')->with('success', 'Supplier created.');
    }*/

    public function edit(Supplier $supplier)
    {
        return view('suppliers.edit', compact('supplier'));
    }

    public function update(Request $request, Supplier $supplier)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
        ]);

        $supplier->update($data);

        return redirect()->route('suppliers.index')->with('success', 'Supplier updated.');
    }
}