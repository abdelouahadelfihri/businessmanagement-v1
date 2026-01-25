<?php
namespace App\Http\Controllers\Purchases;

use App\Http\Controllers\Controller;
use App\Models\Purchases\PurchaseRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
            'status' => 'required|in:draft,approved,cancelled',
            'description' => 'nullable|string',
        ]);

        PurchaseRequest::create($request->only([
            'supplier_id',
            'date',
            'status',
            'description',
        ]));

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
        $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'date' => 'required|date',
            'status' => 'required|in:draft,approved,cancelled',
            'description' => 'nullable|string',
        ]);

        $purchaseRequest->update($request->only([
            'supplier_id',
            'date',
            'status',
            'description',
        ]));

        return redirect()->route('purchasesrequests.index');

    }
    public function destroy(PurchaseRequest $purchaseRequest)
    {
        $purchaseRequest->delete();

        if (PurchaseRequest::count() === 0) {
            DB::statement('ALTER TABLE purchase_requests AUTO_INCREMENT = 1');
        }

        return redirect()
            ->route('purchasesrequests.index')
            ->with('success', 'Purchase request deleted successfully.');
    }

}