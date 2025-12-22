<?php
namespace App\Http\Controllers\MasterData;

use App\Models\MasterData\Supplier;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $selectFor = $request->query('select_for');
        $returnUrl = $request->query('return_url');

        // Save current form values in session if coming from a purchase request
        if ($selectFor === 'sale-quotation') {
            $existing = session('sale_quotation_form', []);
            session(['sale_quotation_form' => array_merge($existing, $request->except(['page', 'select_for', 'return_url']))]);
        }

        $suppliers = Supplier::paginate(10);

        return view('suppliers.index', compact('suppliers', 'selectFor', 'returnUrl'));
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