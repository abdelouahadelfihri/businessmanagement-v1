<?php
namespace App\Http\Controllers\Purchases;

use App\Http\Controllers\Controller;
use App\Models\Purchases\PurchaseRequest;
use App\Models\MasterData\Supplier;
use Illuminate\Http\Request;

class PurchaseRequestController extends Controller
{
    public function index(Request $request)
    {
        $requests = PurchaseRequest::with('supplier')->paginate(12);

        $selectFor = $request->query('select_for');
        $returnUrl = $request->query('return_url');

        return view('purchasesrequests.index', compact('requests', 'selectFor', 'returnUrl'));
    }

    public function create(Request $request)
    {
        // Merge session + query params
        if (!$request->hasAny(['request_date', 'description', 'status', 'selected_supplier_id'])) {
            session()->forget('purchase_request_form');
        }

        $form = array_merge(
            session('purchase_request_form', []),
            $request->only(['request_date', 'description', 'status', 'selected_supplier_id'])
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
            'date' => $data['request_date'],
            'description' => $data['description'],
            'status' => $data['status'],
        ]);

        session()->forget('purchase_request_form');

        return redirect()->route('purchasesrequests.index')
            ->with('success', 'Purchase request created.');
    }

    public function edit(PurchaseRequest $purchasesrequest)
    {
        $selectedSupplier = $purchasesrequest->supplier;
        $form = [
            'request_date' => old('request_date', $purchasesrequest->date),
            'description' => old('description', $purchasesrequest->description),
            'status' => old('status', $purchasesrequest->status),
            'selected_supplier_id' => $purchasesrequest->supplier_id,
        ];

        session(['purchase_request_form' => $form]);

        return view('purchasesrequests.edit', compact('selectedSupplier', 'form', 'purchasesrequest'));
    }

    public function update(Request $request, PurchaseRequest $purchasesrequest)
    {
        $data = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'request_date' => 'required|date',
            'description' => 'nullable|string',
            'status' => 'required|in:draft,pending,approved',
        ]);

        $purchasesrequest->update([
            'supplier_id' => $data['supplier_id'],
            'date' => $data['request_date'],
            'description' => $data['description'],
            'status' => $data['status'],
        ]);

        session()->forget('purchase_request_form');

        return redirect()->route('purchasesrequests.index')
            ->with('success', 'Purchase request updated.');
    }

    public function destroy(PurchaseRequest $purchasesrequest)
    {
        $purchasesrequest->delete();
        return back()->with('success', 'Deleted successfully');
    }
}