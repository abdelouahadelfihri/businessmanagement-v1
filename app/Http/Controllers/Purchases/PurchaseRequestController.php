<?php
namespace App\Http\Controllers\Purchases;

use App\Models\Purchases\PurchaseRequest;
use App\Models\MasterData\Supplier;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class PurchaseRequestController extends Controller
{
    public function index(Request $request)
    {
        $requests = PurchaseRequest::paginate(12);

        $selectFor = $request->query('select_for');
        $returnUrl = $request->query('return_url');

        return view('purchasesrequests.index', compact('requests', 'selectFor', 'returnUrl'));
    }
    public function create(Request $request)
    {
        // If this is a fresh create (no picker return, no form data)
        if (
            !$request->hasAny([
                'request_date',
                'description',
                'status',
                'selected_supplier_id'
            ])
        ) {
            session()->forget('purchase_request_form');
        }

        // Merge session + incoming values
        $form = array_merge(
            session('purchase_request_form', []),
            $request->only([
                'request_date',
                'description',
                'status',
                'selected_supplier_id'
            ])
        );

        session(['purchase_request_form' => $form]);

        $selectedSupplier = null;
        if (!empty($form['selected_supplier_id'])) {
            $selectedSupplier = Supplier::find($form['selected_supplier_id']);
        }

        return view('purchasesrequests.create', compact('selectedSupplier', 'form'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'request_date' => 'required|date',
            'description' => 'nullable|string',
            'status' => 'required|in:draft,pending,approved',
        ]);

        PurchaseRequest::create([
            'supplier_id' => $data['supplier_id'],
            'date' => $data['request_date'],  // <-- map it!
            'description' => $data['description'],
            'status' => $data['status'],
        ]);

        session()->forget('purchase_request_form');

        return redirect()
            ->route('purchasesrequests.index')
            ->with('success', 'Purchase request created successfully.');
    }

}