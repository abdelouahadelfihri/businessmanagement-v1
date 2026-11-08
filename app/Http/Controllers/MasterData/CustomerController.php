<?php

namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;
use App\Models\CRM\Contact;
use App\Models\MasterData\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $customers = Customer::query()
            ->with('primaryContact')
            ->search($request->input('search'))
            ->when($request->filled('type'), fn($q) => $q->where('type', $request->input('type')))
            ->when($request->input('status') === 'active', fn($q) => $q->where('is_active', true))
            ->when($request->input('status') === 'inactive', fn($q) => $q->where('is_active', false))
            ->withCount('invoices')
            ->withSum(['invoices as outstanding' => fn($q) => $q->whereIn('status', ['issued', 'partial'])], 'balance_due')
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('masterdata.customers.index', compact('customers'));
    }

    public function create()
    {
        return view('masterdata.customers.create', ['customer' => new Customer()]);
    }

    public function store(Request $request)
    {
        $customer = DB::transaction(function () use ($request) {
            $customer = Customer::create($this->validated($request));
            Contact::createPrimaryFor($customer, $request->input('primary_contact'));

            return $customer;
        });

        return redirect()
            ->route('masterdata.customers.show', $customer)
            ->with('success', "Customer {$customer->name} created.");
    }

    public function show(Customer $customer)
    {
        $customer->load(['contacts', 'sourceProspect']);
        $customer->loadCount(['quotations', 'orders', 'invoices', 'returns']);

        $stats = [
            'invoiced' => (float) $customer->invoices()->whereIn('status', ['issued', 'partial', 'paid'])->sum('total'),
            'paid' => (float) $customer->invoices()->whereIn('status', ['issued', 'partial', 'paid'])->sum('amount_paid'),
            'outstanding' => $customer->outstandingBalance(),
            'overdue' => $customer->invoices()
                ->whereIn('status', ['issued', 'partial'])
                ->whereDate('due_date', '<', today())
                ->count(),
        ];

        $recentInvoices = $customer->invoices()->latest('date')->latest('id')->limit(5)->get();
        $recentOrders = $customer->orders()->latest('date')->latest('id')->limit(5)->get();

        return view('masterdata.customers.show', compact('customer', 'stats', 'recentInvoices', 'recentOrders'));
    }

    public function edit(Customer $customer)
    {
        return view('masterdata.customers.edit', compact('customer'));
    }

    public function update(Request $request, Customer $customer)
    {
        $customer->update($this->validated($request, $customer));

        return redirect()
            ->route('masterdata.customers.show', $customer)
            ->with('success', "Customer {$customer->name} updated.");
    }

    public function destroy(Customer $customer)
    {
        if ($customer->hasDocuments()) {
            return back()->with('error', "{$customer->name} has sales documents and cannot be deleted. Mark it as inactive instead.");
        }

        $customer->delete();

        return redirect()
            ->route('masterdata.customers.index')
            ->with('success', "Customer {$customer->name} deleted.");
    }

    /** Quick create from a picker modal (returns JSON). */
    public function ajaxStore(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:30',
        ]);

        return response()->json(Customer::create($data));
    }

    private function validated(Request $request, ?Customer $customer = null): array
    {
        $rules = [
            'code' => ['nullable', 'string', 'max:20', Rule::unique('customers', 'code')->ignore($customer)],
            'type' => ['required', Rule::in(array_keys(Customer::TYPES))],
            'name' => 'required|string|max:255',

            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:30',
            'mobile' => 'nullable|string|max:30',
            'website' => 'nullable|url|max:255',

            'address' => 'nullable|string|max:1000',
            'city' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:100',
            'shipping_address' => 'nullable|string|max:1000',

            'ice' => ['nullable', 'digits:15', Rule::unique('customers', 'ice')->ignore($customer)],
            'tax_id' => 'nullable|string|max:30',
            'trade_register' => 'nullable|string|max:30',
            'patente' => 'nullable|string|max:30',

            'payment_terms_days' => 'required|integer|min:0|max:365',
            'credit_limit' => 'nullable|numeric|min:0',
            'discount_percent' => 'nullable|numeric|min:0|max:100',
            'currency' => ['required', Rule::in(Customer::CURRENCIES)],

            'notes' => 'nullable|string|max:5000',
        ];

        if (! $customer) {
            $rules += Contact::quickRules();
        }

        $data = $request->validate($rules, [
            'ice.digits' => 'The ICE must be exactly 15 digits.',
        ], Contact::quickAttributes());

        unset($data['primary_contact']);
        $data['is_active'] = $request->boolean('is_active');
        $data['country'] = $data['country'] ?: 'Morocco';
        $data['credit_limit'] = $data['credit_limit'] ?? 0;
        $data['discount_percent'] = $data['discount_percent'] ?? 0;

        return $data;
    }
}
