<?php
namespace App\Http\Controllers\Purchases;

use App\Http\Controllers\Controller;
use App\Models\Purchases\PurchaseRequest;
use App\Models\MasterData\Supplier;
use Illuminate\Http\Request;
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\PurchaseRequest;
use App\Models\Supplier;

class PurchaseRequestController extends Controller
{
    public function index()
    {
        $requests = PurchaseRequest::with('supplier')->get();
        return view('purchase_requests.index', compact('requests'));
    }

    public function create()
    {
        return view('purchase_requests.create');
    }

    public function store(Request $request)
    {
        PurchaseRequest::create($request->all());
        return redirect()->route('purchase-requests.index');
    }

    public function edit(PurchaseRequest $purchaseRequest)
    {
        return view('purchase_requests.edit', compact('purchaseRequest'));
    }

    public function update(Request $request, PurchaseRequest $purchaseRequest)
    {
        $purchaseRequest->update($request->all());
        return redirect()->route('purchase-requests.index');
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