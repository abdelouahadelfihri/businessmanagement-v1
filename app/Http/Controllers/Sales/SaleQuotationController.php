<?php
namespace App\Http\Controllers\Purchases;

use App\Models\Sales\SaleQuotation;
use App\Models\MasterData\Customer;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class SaleQuotationController extends Controller
{
    public function index()
    {
        $requests = SaleQuotation::paginate(12);
        return view('salesquotations.index', compact('requests'));
    }

    public function create(Request $request)
    {
        // If this is a fresh create (no picker return, no form data)
        if (
            !$request->hasAny([
                'quotation_date',
                'description',
                'status',
                'selected_customer_id'
            ])
        ) {
            session()->forget('purchase_request_form');
        }

        // Merge session + incoming values
        $form = array_merge(
            session('purchase_request_form', []),
            $request->only([
                'quotation_date',
                'description',
                'status',
                'selected_customer_id'
            ])
        );

        session(['purchase_request_form' => $form]);

        $selectedCustomer = null;
        if (!empty($form['selected_customer_id'])) {
            $selectedCustomer = Customer::find($form['selected_customer_id']);
        }

        return view('salesquotations.create', compact('selectedCustomer', 'form'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'customer_id' => 'required|exists:suppliers,id',
            'quotation_date' => 'required|date',
            'description' => 'nullable|string',
            'status' => 'required|in:draft,pending,approved',
        ]);

        SaleQuotation::create($data);

        session()->forget('purchase_request_form');

        return redirect()
            ->route('salesquotations.index')
            ->with('success', 'Purchase request created successfully.');
    }

}