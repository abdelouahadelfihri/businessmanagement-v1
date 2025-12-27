<?php
namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;
use App\Models\MasterData\Supplier;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    public function index()
    {
        $suppliers = Supplier::all();
        return view('suppliers.index', compact('suppliers'));
    }

    public function create()
    {
        return view('suppliers.create');
    }

    public function store(Request $request)
    {
        Supplier::create($request->all());
        return redirect()->route('suppliers.index');
    }

    public function edit(Supplier $supplier)
    {
        return view('suppliers.edit', compact('supplier'));
    }

    public function update(Request $request, Supplier $supplier)
    {
        $supplier->update($request->all());
        return redirect()->route('suppliers.index');
    }

    // AJAX store for modal
    public function ajaxStore(Request $request)
    {
        $supplier = Supplier::create(['name' => $request->name]);
        return response()->json($supplier);
    }
}