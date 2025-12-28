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
        $last = PurchaseRequest::orderBy('id', 'desc')->first();
        $nextId = $last ? $last->id + 1 : 1;
        $preview = 'PR-' . date('Y') . '-' . str_pad($nextId, 4, '0', STR_PAD_LEFT);

        return view('purchaserequests.create', compact('preview'));
    }
    public function store(Request $request)
    {
        $request->validate([
            'supplier_id' => 'required',
            'request_date' => 'required',
            'status' => 'required'
        ]);

        // Generate purchase request number
        $last = PurchaseRequest::orderBy('id', 'desc')->first();
        $nextId = $last ? $last->id + 1 : 1;
        $prNumber = 'PR-' . date('Y') . '-' . str_pad($nextId, 4, '0', STR_PAD_LEFT);

        $purchaseRequest = PurchaseRequest::create([
            'supplier_id' => $request->supplier_id,
            'request_number' => $prNumber,
            'request_date' => $request->request_date,
            'status' => $request->status,
        ]);

        return redirect()->route('purchaserequests.index')->with('success', 'Purchase Request created!');
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