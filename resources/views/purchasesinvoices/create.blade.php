@extends('layouts.app')
@section('content')
    <div class="container mt-4">
        <h1 class="mb-4">Create Purchase Invoice</h1>

        <div class="card shadow-sm">
            <div class="card-body">

                {{-- ORDER PICKER --}}
                <form id="orderPickerForm" method="GET" action="{{ route('purchaseorders.index') }}">
                    <input type="hidden" name="select_for" value="purchase-invoice">
                    <input type="hidden" name="return_url" value="{{ route('purchaseinvoices.create') }}">

                    @foreach(['invoice_number', 'date', 'total', 'paid'] as $f)
                        <input type="hidden" id="h_{{ $f }}" name="{{ $f }}" value="{{ old($f, $form[$f] ?? '') }}">
                    @endforeach
                </form>

                {{-- MAIN FORM --}}
                <form action="{{ route('purchaseinvoices.store') }}" method="POST">
                    @csrf

                    {{-- ORDER --}}
                    <div class="mb-3">
                        <label class="form-label">Purchase Order</label>
                        <div class="input-group">
                            <input class="form-control" value="{{ $selectedOrder?->id }}" readonly placeholder="Pick order">
                            <button class="btn btn-outline-secondary" type="button"
                                onclick="submitOrderPicker()">Pick</button>
                        </div>
                        <input type="hidden" name="order_id" value="{{ $selectedOrder?->id }}">
                    </div>

                    {{-- SUPPLIER (readonly) --}}
                    <div class="mb-3">
                        <label class="form-label">Supplier</label>
                        <input class="form-control" value="{{ $selectedOrder?->supplier?->name }}" readonly>
                    </div>

                    {{-- INVOICE NUMBER --}}
                    <div class="mb-3">
                        <label class="form-label">Invoice Number</label>
                        <input type="text" name="invoice_number" class="form-control"
                            value="{{ old('invoice_number', $form['invoice_number'] ?? '') }}" required>
                    </div>

                    {{-- DATE --}}
                    <div class="mb-3">
                        <label class="form-label">Date</label>
                        <input type="date" id="date" name="date" class="form-control"
                            value="{{ old('date', $form['date'] ?? '') }}" required>
                    </div>

                    {{-- TOTAL --}}
                    <div class="mb-3">
                        <label class="form-label">Total</label>
                        <input type="number" name="total" step="0.01" class="form-control"
                            value="{{ old('total', $form['total'] ?? '') }}">
                    </div>

                    {{-- PAID --}}
                    <div class="mb-3">
                        <label class="form-label">Paid</label>
                        <input type="number" name="paid" step="0.01" class="form-control"
                            value="{{ old('paid', $form['paid'] ?? '') }}">
                    </div>

                    <div class="d-flex gap-2">
                        <button class="btn btn-primary">Save</button>
                        <a href="{{ route('purchaseinvoices.index') }}" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>

            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function submitOrderPicker() {
            ['invoice_number', 'date', 'total', 'paid'].forEach(f => {
                let inp = document.getElementById(f);
                if (inp) document.getElementById('h_' + f).value = inp.value;
            });
            document.getElementById('orderPickerForm').submit();
        }
    </script>
@endpush