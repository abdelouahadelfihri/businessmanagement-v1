<?php
namespace App\Http\Controllers\MasterData;

use App\Models\MasterData\Customer;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $customers = Customer::paginate(12);

        $selectFor = $request->query('select_for');   // purchase-request | purchase-order | receipt
        $returnUrl = $request->query('return_url');   // url to go back to form
        $extra = $request->except(['page']);      // keep all other form values

        return view('customers.index', compact('customers', 'selectFor', 'returnUrl', 'extra'));
    }

    public function create(Request $request)
    {
        // pass along selection params so create view can return to PO after saving
        $selectFor = $request->query('select_for');
        $returnUrl = $request->query('return_url');

        return view('customers.create', compact('selectFor', 'returnUrl'));
    }
    public function store(Request $request)
    {
        $customer = Customer::create(
            $request->only('name', 'email')
        );

        if ($request->filled('return_url')) {
            return redirect()->to(
                $request->return_url .
                (str_contains($request->return_url, '?') ? '&' : '?') .
                'selected_customer_id=' . $customer->id
            );
        }

        return redirect()->route('customers.index');
    }
    public function edit(Customer $supplier)
    {
        return view('customers.edit', compact('supplier'));
    }

    public function update(Request $request, Customer $supplier)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
        ]);

        $supplier->update($data);

        return redirect()->route('customers.index')->with('success', 'Customer updated.');
    }

    // AJAX store for modal
    public function ajaxStore(Request $request)
    {
        $customer = Customer::create(['name' => $request->name]);
        return response()->json($customer);
    }

    public function destroy(Customer $customer)
    {
        $customer->delete();

        // If table is empty, reset primary key counter
        if (Customer::count() === 0) {
            DB::statement('ALTER TABLE categories AUTO_INCREMENT = 1');
        }

        return redirect()
            ->route('categories.index')
            ->with('success', 'Customer deleted successfully.');
    }

}