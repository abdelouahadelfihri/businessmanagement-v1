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
        return view('purchasesrequests.create'); // ✅ FIXED
    }

    public function store(Request $request)
    {
        $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'date' => 'required|date',
            'status' => 'required|string',
        ]);

        PurchaseRequest::create($request->all());

        return redirect()
            ->route('purchasesrequests.index') // ✅ FIXED
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

}