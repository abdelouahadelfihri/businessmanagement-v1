<?php
namespace App\Http\Controllers\Sales;

use App\Models\Sales\SaleQuotation;
use App\Models\MasterData\Customer;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class SaleQuotationController extends Controller
{
    public function index()
    {
        $requests = SaleQuotation::paginate(12);
        return view('salesquotations.index', compact('requests'));
    }
    public function create()
    {
        return view('salesquotations.create');
    }
    public function store(Request $request)
    {
        $request->validate([
            'customer_id' => 'required',
            'quotation_date' => 'required|date',
            'status' => 'required'
        ]);

        SaleQuotation::create($request->all());

        return redirect()->route('salesquotations.index')
            ->with('success', 'Sales Quotation Created');
    }

}