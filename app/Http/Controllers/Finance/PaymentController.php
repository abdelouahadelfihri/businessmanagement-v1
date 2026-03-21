<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Payment;
use App\Models\Sales\SaleInvoice;
use App\Models\Purchases\PurchaseInvoice;

class PaymentController extends Controller
{
    // ✅ LIST ALL PAYMENTS
    public function index()
    {
        $payments = Payment::with('payable')->latest()->get();

        return view('payments.index', compact('payments'));
    }

    // ✅ SHOW CREATE FORM
    public function create()
    {
        $salesInvoices = SaleInvoice::all();
        $supplierInvoices = PurchaseInvoice::all();

        return view('payments.create', compact('salesInvoices', 'supplierInvoices'));
    }

    // ✅ STORE PAYMENT
    public function store(Request $request)
    {
        $validated = $request->validate([
            'payable_type' => 'required|in:App\Models\SalesInvoice,App\Models\SupplierInvoice',
            'payable_id' => 'required|integer',
            'payment_date' => 'required|date',
            'amount' => 'required|numeric|min:0',
            'payment_method' => 'nullable|string|max:255',
            'reference' => 'nullable|string|max:255',
        ]);

        Payment::create($validated);

        return redirect()->route('payments.index')
            ->with('success', 'Payment created successfully.');
    }

    // ✅ SHOW EDIT FORM
    public function edit(Payment $payment)
    {
        $salesInvoices = SaleInvoice::all();
        $supplierInvoices = PurchaseInvoice::all();

        return view('payments.edit', compact('payment', 'salesInvoices', 'supplierInvoices'));
    }

    // ✅ UPDATE PAYMENT
    public function update(Request $request, Payment $payment)
    {
        $validated = $request->validate([
            'payable_type' => 'required|in:App\Models\SalesInvoice,App\Models\SupplierInvoice',
            'payable_id' => 'required|integer',
            'payment_date' => 'required|date',
            'amount' => 'required|numeric|min:0',
            'payment_method' => 'nullable|string|max:255',
            'reference' => 'nullable|string|max:255',
        ]);

        $payment->update($validated);

        return redirect()->route('payments.index')
            ->with('success', 'Payment updated successfully.');
    }

    // ✅ DELETE PAYMENT
    public function destroy(Payment $payment)
    {
        $payment->delete();

        return redirect()->route('payments.index')
            ->with('success', 'Payment deleted successfully.');
    }
}