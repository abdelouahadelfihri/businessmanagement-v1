<?php
namespace App\Http\Controllers\Purchases;

use App\Http\Controllers\Controller;
use App\Models\Purchases\PurchaseRequest;
use Illuminate\Http\Request;
class PurchaseRequestController extends Controller
{
    public function index()
    {
        $requests = PurchaseRequest::with('supplier')->get();
        return view('purchasesrequests.index', compact('requests'));
    }
    public function create()
    {
        return view('purchaserequests.create');
    }
    public function store(Request $request)
    {
        $request->validate([
            'supplier_id' => 'required',
            'request_date' => 'required|date',
            'status' => 'required'
        ]);

        PurchaseRequest::create($request->all());

        return redirect()->route('purchaserequests.index')
            ->with('success', 'Purchase Request Created');
    }
    public function edit(PurchaseRequest $purchaseRequest)
    {
        return view('purchasesrequests.edit', compact('purchaseRequest'));
    }
    public function update(Request $request, PurchaseRequest $purchaseRequest)
    {
        $purchaseRequest->update($request->all());
        return redirect()->route('purchasesrequests.index');
    }
    // AJAX store for modal
    public function ajaxStore(Request $request)
    {
        $purchaseRequest = PurchaseRequest::create([
            'request_number' => $request->request_number,
            'supplier_id' => $request->supplier_id
        ]);
        return response()->json($purchaseRequest);
    }
}