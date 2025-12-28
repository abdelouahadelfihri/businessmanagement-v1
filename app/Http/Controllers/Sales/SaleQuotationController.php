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
        $last = SaleQuotation::orderBy('id', 'desc')->first();
        $nextId = $last ? $last->id + 1 : 1;
        $preview = 'QT-' . date('Y') . '-' . str_pad($nextId, 4, '0', STR_PAD_LEFT);

        return view('salesquotations.create', compact('preview'));
    }


    public function store(Request $request)
    {
        // Validate
        $request->validate([
            'customer_id' => 'required',
            'quotation_date' => 'required',
            'status' => 'required',
        ]);

        // Generate quote number
        $last = SaleQuotation::orderBy('id', 'desc')->first();
        $nextId = $last ? $last->id + 1 : 1;
        $quoteNumber = 'QT-' . date('Y') . '-' . str_pad($nextId, 4, '0', STR_PAD_LEFT);

        // Save quotation
        $quotation = SaleQuotation::create([
            'customer_id' => $request->customer_id,
            'quote_number' => $quoteNumber,
            'quotation_date' => $request->quotation_date,
            'status' => $request->status,
        ]);

        return redirect()->route('salesquotations.index')->with('success', 'Quotation created!');
    }


}