<?php
namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;
use App\Models\MasterData\Unit;
use Illuminate\Http\Request;

class UnitController extends Controller
{
    public function index()
    {
        $units = Unit::all();
        return view('units.index', compact('units'));
    }

    public function create()
    {
        return view('units.create');
    }

    public function store(Request $request)
    {
        Unit::create($request->all());
        return redirect()->route('units.index');
    }

    public function edit(Unit $supplier)
    {
        return view('units.edit', compact('supplier'));
    }

    public function update(Request $request, Unit $supplier)
    {
        $supplier->update($request->all());
        return redirect()->route('units.index');
    }

    // AJAX store for modal
    public function ajaxStore(Request $request)
    {
        $supplier = Unit::create(['name' => $request->name]);
        return response()->json($supplier);
    }
}