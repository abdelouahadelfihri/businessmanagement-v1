<?php
namespace App\Http\Controllers\Purchases;

use App\Models\Purchases\PurchaseRequest;
use App\Models\MasterData\Supplier;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class PurchaseRequestController extends Controller
{
    public function index()
    {
        $requests = PurchaseRequest::paginate(12);
        return view('purchasesrequests.index', compact('requests'));
    }

    public function create(Request $request)
    {
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
        ]);

        PurchaseRequest::create($data);

        // Clear session form after saving
        session()->forget('purchase_request_form');

        return redirect()->route('purchasesrequests.index')
            ->with('success', 'Purchase request created successfully.');
    }
}