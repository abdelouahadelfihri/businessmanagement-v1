<?php
namespace App\Http\Controllers\MasterData;

use App\Models\MasterData\Supplier;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class SupplierController extends Controller
{
    public function index(Request $request)
    {
        $suppliers = Supplier::paginate(12);

        $selectFor = $request->query('select_for');   // purchase-request | purchase-order | receipt
        $returnUrl = $request->query('return_url');   // url to go back to form
        $extra = $request->except(['page']);      // keep all other form values

        return view('suppliers.index', compact('suppliers', 'selectFor', 'returnUrl', 'extra'));
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
        $supplier = Supplier::create(
            $request->only('name', 'email')
        );

        if ($request->filled('return_url')) {
            return redirect()->to(
                $request->return_url .
                (str_contains($request->return_url, '?') ? '&' : '?') .
                'selected_supplier_id=' . $supplier->id
            );
        }

        return redirect()->route('suppliers.index');
    }

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

    public function ajaxCreate(Request $request)
    {
        $data = $request->validate([
            'name' => 'required',
            'email' => 'nullable|email',
            'phone' => 'nullable'
        ]);

        $supplier = Supplier::create($data);

        return response()->json([
            'success' => true,
            'supplier' => $supplier
        ]);
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
        ]);

        $supplier->update($data);

        return redirect()->route('suppliers.index')->with('success', 'Supplier updated.');
    }
}