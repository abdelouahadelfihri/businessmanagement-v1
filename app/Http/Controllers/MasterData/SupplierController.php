<?php

namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;
use App\Models\CRM\Contact;
use App\Models\MasterData\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class SupplierController extends Controller
{
    public function index(Request $request)
    {
        $suppliers = Supplier::query()
            ->with('primaryContact')
            ->search($request->input('search'))
            ->when($request->filled('type'), fn($q) => $q->where('type', $request->input('type')))
            ->when($request->input('status') === 'active', fn($q) => $q->where('is_active', true))
            ->when($request->input('status') === 'inactive', fn($q) => $q->where('is_active', false))
            ->when($request->filled('city'), fn($q) => $q->where('city', $request->input('city')))
            ->withCount(['requests', 'orders', 'receipts', 'invoices', 'returns'])
            ->withSum(['invoices as amount_due' => fn($q) => $q->whereNotNull('validated_at')], \DB::raw('total - COALESCE(amount_paid, 0)'))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        $cities = Supplier::whereNotNull('city')->distinct()->orderBy('city')->pluck('city');

        return view('masterdata.suppliers.index', compact('suppliers', 'cities'));
    }

    public function create()
    {
        return view('masterdata.suppliers.create', ['supplier' => new Supplier()]);
    }

    public function store(Request $request)
    {
        $supplier = DB::transaction(function () use ($request) {
            $supplier = Supplier::create($this->validated($request));
            Contact::createPrimaryFor($supplier, $request->input('primary_contact'));

            return $supplier;
        });

        return redirect()
            ->route('masterdata.suppliers.show', $supplier)
            ->with('success', "Supplier {$supplier->name} created.");
    }

    public function show(Supplier $supplier)
    {
        $supplier->load('contacts');
        $supplier->loadCount(['requests', 'orders', 'receipts', 'invoices', 'returns']);

        $validated = $supplier->invoices()->whereNotNull('validated_at');

        $stats = [
            'purchased' => (float) (clone $validated)->sum('total'),
            'paid' => (float) (clone $validated)->sum('amount_paid'),
            'due' => $supplier->amountDue(),
            'overdue' => (clone $validated)
                ->where(fn($q) => $q->whereNull('payment_status')->orWhere('payment_status', '!=', 'paid'))
                ->whereDate('due_date', '<', today())
                ->count(),
        ];

        $recentOrders = $supplier->orders()->latest('order_date')->latest('id')->limit(5)->get();
        $recentInvoices = $supplier->invoices()->latest('date')->latest('id')->limit(5)->get();

        return view('masterdata.suppliers.show', compact('supplier', 'stats', 'recentOrders', 'recentInvoices'));
    }

    public function edit(Supplier $supplier)
    {
        return view('masterdata.suppliers.edit', compact('supplier'));
    }

    public function update(Request $request, Supplier $supplier)
    {
        $supplier->update($this->validated($request, $supplier));

        return redirect()
            ->route('masterdata.suppliers.show', $supplier)
            ->with('success', "Supplier {$supplier->name} updated.");
    }

    public function destroy(Supplier $supplier)
    {
        if ($supplier->hasDocuments()) {
            return back()->with('error', "{$supplier->name} has purchase documents and cannot be deleted. Mark it as inactive instead.");
        }

        $supplier->delete();

        return redirect()
            ->route('masterdata.suppliers.index')
            ->with('success', "Supplier {$supplier->name} deleted.");
    }

    /** Quick create from the supplier picker modal (returns JSON). */
    public function ajaxStore(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:30',
            'address' => 'nullable|string|max:1000',
        ]);

        return response()->json(Supplier::create($data)->fresh());
    }

    private function validated(Request $request, ?Supplier $supplier = null): array
    {
        $rules = [
            'code' => ['nullable', 'string', 'max:20', Rule::unique('suppliers', 'code')->ignore($supplier)],
            'type' => ['required', Rule::in(array_keys(Supplier::TYPES))],
            'name' => 'required|string|max:255',

            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:30',
            'mobile' => 'nullable|string|max:30',
            'website' => 'nullable|url|max:255',

            'address' => 'nullable|string|max:1000',
            'city' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:100',

            'ice' => ['nullable', 'digits:15', Rule::unique('suppliers', 'ice')->ignore($supplier)],
            'tax_id' => 'nullable|string|max:30',
            'trade_register' => 'nullable|string|max:30',
            'patente' => 'nullable|string|max:30',

            'bank_name' => 'nullable|string|max:100',
            'rib' => 'nullable|digits:24',
            'swift' => ['nullable', 'regex:/^[A-Za-z]{6}[A-Za-z0-9]{2}([A-Za-z0-9]{3})?$/'],

            'payment_terms_days' => 'required|integer|min:0|max:365',
            'payment_method' => ['required', Rule::in(array_keys(Supplier::PAYMENT_METHODS))],
            'currency' => ['required', Rule::in(Supplier::CURRENCIES)],
            'lead_time_days' => 'nullable|integer|min:0|max:365',
            'minimum_order' => 'nullable|numeric|min:0',
            'discount_percent' => 'nullable|numeric|min:0|max:100',

            'rating' => 'nullable|integer|min:1|max:5',
            'notes' => 'nullable|string|max:5000',
        ];

        if (! $supplier) {
            $rules += Contact::quickRules();
        }

        $data = $request->validate($rules, [
            'ice.digits' => 'The ICE must be exactly 15 digits.',
            'rib.digits' => 'The RIB must be exactly 24 digits.',
            'swift.regex' => 'The SWIFT / BIC code must be 8 or 11 characters (e.g. BCMAMAMC).',
        ], Contact::quickAttributes());

        unset($data['primary_contact']);
        $data['is_active'] = $request->boolean('is_active');
        $data['country'] = $data['country'] ?: 'Morocco';
        $data['swift'] = isset($data['swift']) ? strtoupper($data['swift']) : null;
        $data['minimum_order'] = $data['minimum_order'] ?? 0;
        $data['discount_percent'] = $data['discount_percent'] ?? 0;

        return $data;
    }
}
