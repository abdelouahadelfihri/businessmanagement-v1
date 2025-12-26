<?php
namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;
use App\Models\MasterData\Supplier;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    // Normal CRUD

    public function index(Request $request)
    {
        $suppliers = Supplier::query();

        // Search
        if ($request->has('search')) {
            $suppliers->where('name', 'like', "%{$request->search}%")
                ->orWhere('email', 'like', "%{$request->search}%")
                ->orWhere('phone', 'like', "%{$request->search}%");
        }

        $suppliers = $suppliers->paginate(12)->withQueryString();

        // Picker mode
        $selectFor = $request->query('select_for');
        $returnUrl = $request->query('return_url');

        return view('suppliers.index', compact('suppliers', 'selectFor', 'returnUrl'));
    }

    public function picker(Request $request)
    {
        // simply redirect to index with select mode
        return redirect()->route('suppliers.index', [
            'select_for' => $request->query('select_for'),
            'return_url' => $request->query('return_url')
        ]);
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
            'phone' => 'nullable|string|max:20',
        ]);
        Supplier::create($data);
        return redirect()->route('suppliers.index')->with('success', 'Supplier created.');
    }

    public function edit(Supplier $supplier)
    {
        return view('suppliers.edit', compact('supplier'));
    }
    public function update(Request $request, Supplier $supplier)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
        ]);
        $supplier->update($data);
        return redirect()->route('suppliers.index')->with('success', 'Supplier updated.');
    }

    public function destroy(Supplier $supplier)
    {
        $supplier->delete();
        return back()->with('success', 'Deleted');
    }
}