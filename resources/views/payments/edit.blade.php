@extends('layouts.app')

@section('content')
<div class="container mt-4">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1>Edit Payment</h1>
        <a href="{{ route('payments.index') }}" class="btn btn-secondary">
            Back
        </a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">

            <form action="{{ route('payments.update', $payment) }}" method="POST">
                @csrf
                @method('PUT')

                <!-- Invoice Type -->
                <div class="mb-3">
                    <label class="form-label">Invoice Type</label>
                    <select name="payable_type" class="form-control @error('payable_type') is-invalid @enderror" required>
                        <option value="App\Models\SalesInvoice"
                            {{ $payment->payable_type == 'App\Models\SalesInvoice' ? 'selected' : '' }}>
                            Sales Invoice
                        </option>

                        <option value="App\Models\SupplierInvoice"
                            {{ $payment->payable_type == 'App\Models\SupplierInvoice' ? 'selected' : '' }}>
                            Supplier Invoice
                        </option>
                    </select>
                    @error('payable_type')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Invoice -->
                <div class="mb-3">
                    <label class="form-label">Invoice</label>
                    <select name="payable_id" class="form-control @error('payable_id') is-invalid @enderror" required>

                        <optgroup label="Sales Invoices">
                            @foreach($salesInvoices as $invoice)
                                <option value="{{ $invoice->id }}"
                                    {{ $payment->payable_id == $invoice->id && $payment->payable_type == 'App\Models\SalesInvoice' ? 'selected' : '' }}>
                                    #{{ $invoice->invoice_number }}
                                </option>
                            @endforeach
                        </optgroup>

                        <optgroup label="Supplier Invoices">
                            @foreach($supplierInvoices as $invoice)
                                <option value="{{ $invoice->id }}"
                                    {{ $payment->payable_id == $invoice->id && $payment->payable_type == 'App\Models\SupplierInvoice' ? 'selected' : '' }}>
                                    #{{ $invoice->invoice_number }}
                                </option>
                            @endforeach
                        </optgroup>

                    </select>
                    @error('payable_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Payment Date -->
                <div class="mb-3">
                    <label class="form-label">Payment Date</label>
                    <input type="date" name="payment_date"
                        class="form-control @error('payment_date') is-invalid @enderror"
                        value="{{ old('payment_date', $payment->payment_date) }}" required>
                    @error('payment_date')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Amount -->
                <div class="mb-3">
                    <label class="form-label">Amount</label>
                    <input type="number" step="0.01" name="amount"
                        class="form-control @error('amount') is-invalid @enderror"
                        value="{{ old('amount', $payment->amount) }}" required>
                    @error('amount')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Payment Method -->
                <div class="mb-3">
                    <label class="form-label">Payment Method</label>
                    <input type="text" name="payment_method"
                        class="form-control @error('payment_method') is-invalid @enderror"
                        value="{{ old('payment_method', $payment->payment_method) }}">
                    @error('payment_method')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Reference -->
                <div class="mb-3">
                    <label class="form-label">Reference</label>
                    <input type="text" name="reference"
                        class="form-control @error('reference') is-invalid @enderror"
                        value="{{ old('reference', $payment->reference) }}">
                    @error('reference')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Actions -->
                <div class="d-flex justify-content-end">
                    <a href="{{ route('payments.index') }}" class="btn btn-outline-secondary me-2">
                        Cancel
                    </a>
                    <button type="submit" class="btn btn-primary">
                        Update Payment
                    </button>
                </div>

            </form>

        </div>
    </div>

</div>
@endsection