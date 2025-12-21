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

        return view('index', compact('requests', 'selectFor', 'returnUrl'));
    }

    public function create(Request $request)
    {
        // Restore saved form state
        $form = array_merge(
            session('purchase_request_form', []),
            $request->only('request_date')
        );

        session(['purchase_request_form' => $form]);

        // If coming back from supplier selection, KEEP form state
        if ($request->has('selected_supplier_id')) {
            session(['purchase_request_form' => $form]);
        }

        $selectedSupplier = null;
        if ($request->filled('selected_supplier_id')) {
            $selectedSupplier = Supplier::find($request->selected_supplier_id);
        }

        return view('purchasesrequests.create', compact(
            'selectedSupplier',
            'form'
        ));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'request_date' => 'required|date',
        ]);

        PurchaseRequest::create($data);

        session()->forget('purchase_request_form');

        return redirect()->route('purchasesrequests.index')
            ->with('success', 'Request created.');
    }
    public function edit(PurchaseRequest $purchaseRequest)
    {
        return view('purchasesrequests.edit', ['request' => $purchaseRequest]);
    }

    public function update(Request $request, PurchaseRequest $purchaseRequest)
    {
        $data = $request->validate(['title' => 'required|string|max:255']);
        $purchaseRequest->update($data);
        return redirect()->route('purchase-requests.index')->with('success', 'Request updated.');
    }
}