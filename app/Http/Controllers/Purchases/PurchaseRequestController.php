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
    public function create()
    {
        return view('purchasesrequests.create');
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

        return redirect()->route('purchasesrequests.index')->with('success', 'Purchase request created.');
    }

    public function destroy(PurchaseRequest $purchasesrequest)
    {
        $purchasesrequest->delete();

        return redirect()
            ->route('purchasesrequests.index')
            ->with('success', 'Purchase request deleted successfully.');
    }

}