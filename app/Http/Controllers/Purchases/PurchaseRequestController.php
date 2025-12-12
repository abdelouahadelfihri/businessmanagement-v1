<?php
namespace App\Http\Controllers\Purchases;

use App\Models\Purchases\PurchaseRequest;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class PurchaseRequestController extends Controller
{
    public function index(Request $request)
    {
        $requests = PurchaseRequest::paginate(12);
        $selectFor = $request->query('select_for');
        $returnUrl = $request->query('return_url');

        return view('index', compact('requests','selectFor','returnUrl'));
    }

    public function create(Request $request)
    {
        $selectFor = $request->query('select_for');
        $returnUrl = $request->query('return_url');

        return view('purchasesrequests.create', compact('selectFor','returnUrl'));
    }

    public function store(Request $request)
    {
        $data = $request->validate(['title'=>'required|string|max:255']);
        $req = PurchaseRequest::create($data);

        if ($request->filled('select_for') && $request->filled('return_url')) {
            $return = $request->input('return_url') . '?selected_request_id=' . $req->id;
            return redirect($return);
        }

        return redirect()->route('purchase-requests.index')->with('success','Request created.');
    }

    public function edit(PurchaseRequest $purchaseRequest)
    {
        return view('purchasesrequests.edit', ['request' => $purchaseRequest]);
    }

    public function update(Request $request, PurchaseRequest $purchaseRequest)
    {
        $data = $request->validate(['title'=>'required|string|max:255']);
        $purchaseRequest->update($data);
        return redirect()->route('purchase-requests.index')->with('success','Request updated.');
    }
}