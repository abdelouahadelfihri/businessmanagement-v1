@extends('layouts.app')
@section('content')
    <div class="container mt-4">
        <h1 class="mb-4">Create Purchase Receipt</h1>

        <div class="card shadow-sm">
            <div class="card-body">

                {{-- ORDER PICKER --}}
                <form id="orderPickerForm" method="GET" action="{{ route('purchaseorders.index') }}">
                    <input type="hidden" name="select_for" value="purchase-receipt">
                    <input type="hidden" name="return_url" value="{{ route('purchasereceipts.create') }}">

                    @foreach(['receipt_date', 'receipt_number', 'status'] as $f)
                        <input type="hidden" id="h_{{ $f }}" name="{{ $f }}" value="{{ old($f, $form[$f] ?? '') }}">
                    @endforeach
                </form>

                {{-- MAIN FORM --}}
                <form action="{{ route('purchasereceipts.store') }}" method="POST">
                    @csrf

                    {{-- ORDER --}}
                    <div class="mb-3">
                        <label class="form-label">Purchase Order</label>
                        <div class="input-group">
                            <input class="form-control" value="{{ $selectedOrder?->id }}" readonly
                                placeholder="No order selected">
                            <button class="btn btn-outline-secondary" type="button"
                                onclick="submitOrderPicker()">Pick</button>
                        </div>
                        <input type="hidden" name="order_id" value="{{ $selectedOrder?->id }}">
                    </div>

                    {{-- SUPPLIER (readonly auto from order) --}}
                    <div class="mb-3">
                        <label class="form-label">Supplier</label>
                        <input class="form-control" value="{{ $selectedOrder?->supplier?->name }}" readonly>
                    </div>

                    {{-- RECEIPT NUMBER --}}
                    <div class="mb-3">
                        <label class="form-label">Receipt Number</label>
                        <input type="text" name="receipt_number" class="form-control"
                            value="{{ old('receipt_number', $form['receipt_number'] ?? '') }}" required>
                    </div>

                    {{-- DATE --}}
                    <div class="mb-3">
                        <label class="form-label">Receipt Date</label>
                        <input type="date" id="receipt_date" name="receipt_date" class="form-control"
                            value="{{ old('receipt_date', $form['receipt_date'] ?? '') }}" required>
                    </div>

                    {{-- STATUS --}}
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select id="status" name="status" class="form-select">
                            @foreach(['draft', 'received', 'closed'] as $s)
                                <option value="{{ $s }}" @selected(($form['status'] ?? '') === $s)>{{ ucfirst($s) }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="d-flex gap-2">
                        <button class="btn btn-primary">Save</button>
                        <a href="{{ route('purchasereceipts.index') }}" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>

            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function submitOrderPicker() {
            ['receipt_date', 'receipt_number', 'status'].forEach(f => {
                let inp = document.getElementById(f);
                if (inp) document.getElementById('h_' + f).value = inp.value;
            });
            document.getElementById('orderPickerForm').submit();
        }
    </script>
@endpush