@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h1 class="mb-4">Edit Purchase Receipt #{{ $purchaseReceipt->id }}</h1>

    <div class="card shadow-sm">
        <div class="card-body">

            {{-- Supplier Picker --}}
            <form id="supplierPickerForm" method="GET" action="{{ route('suppliers.index') }}">
                <input type="hidden" name="select_for" value="purchase-receipt">
                <input type="hidden" name="return_url" value="{{ route('purchasereceipts.edit', $purchaseReceipt) }}">
                <input type="hidden" id="purchase_order_id_hidden" name="purchase_order_id"
                    value="{{ old('purchase_order_id', $purchaseReceipt->purchase_order_id) }}">
                <input type="hidden" id="receipt_number_hidden" name="receipt_number"
                    value="{{ old('receipt_number', $purchaseReceipt->receipt_number) }}">
                <input type="hidden" id="receipt_date_hidden" name="receipt_date"
                    value="{{ old('receipt_date', $purchaseReceipt->date) }}">
                <input type="hidden" id="status_hidden" name="status"
                    value="{{ old('status', $purchaseReceipt->status) }}">
            </form>

            <form action="{{ route('purchasereceipts.update', $purchaseReceipt) }}" method="POST">
                @csrf
                @method('PUT')

                {{-- Supplier --}}
                <div class="mb-3">
                    <label class="form-label">Supplier</label>
                    <div class="input-group">
                        <input type="text" class="form-control"
                            value="{{ old('supplier_name', $purchaseReceipt->supplier?->name) }}" readonly>
                        <button type="button" class="btn btn-outline-secondary"
                            onclick="submitSupplierPicker()">Pick</button>
                    </div>
                    <input type="hidden" name="supplier_id"
                        value="{{ old('supplier_id', $purchaseReceipt->supplier_id) }}">
                </div>

                {{-- Purchase Order --}}
                <div class="mb-3">
                    <label class="form-label">Purchase Order</label>
                    <input type="text" class="form-control"
                        value="{{ old('purchase_order_display', $purchaseReceipt->purchaseOrder?->id) }}" readonly>
                    <input type="hidden" name="purchase_order_id"
                        value="{{ old('purchase_order_id', $purchaseReceipt->purchase_order_id) }}">
                </div>

                {{-- Receipt Number --}}
                <div class="mb-3">
                    <label class="form-label">Receipt Number</label>
                    <input type="text" name="receipt_number" class="form-control"
                        value="{{ old('receipt_number', $purchaseReceipt->receipt_number) }}">
                </div>

                {{-- Receipt Date --}}
                <div class="mb-3">
                    <label class="form-label">Receipt Date</label>
                    <input type="date" name="receipt_date" class="form-control"
                        value="{{ old('receipt_date', $purchaseReceipt->date) }}">
                </div>

                {{-- Status --}}
                <div class="mb-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select" required>
                        <option value="">-- Select Status --</option>
                        <option value="draft" @selected(old('status', $purchaseReceipt->status) === 'draft')>Draft
                        </option>
                        <option value="pending" @selected(old('status', $purchaseReceipt->status) === 'pending')>Pending
                        </option>
                        <option value="approved" @selected(old('status', $purchaseReceipt->status) === 'approved')>
                            Approved</option>
                    </select>
                </div>

                <div class="d-flex gap-2">
                    <button class="btn btn-primary">Update</button>
                    <a href="{{ route('purchasereceipts.index') }}" class="btn btn-outline-secondary">Cancel</a>
                </div>

            </form>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        function submitSupplierPicker() {
            document.getElementById('purchase_order_id_hidden').value = document.getElementById('purchase_order_id_hidden').value;
            document.getElementById('receipt_number_hidden').value = document.querySelector('[name="receipt_number"]').value;
            document.getElementById('receipt_date_hidden').value = document.querySelector('[name="receipt_date"]').value;
            document.getElementById('status_hidden').value = document.querySelector('[name="status"]').value;
            document.getElementById('supplierPickerForm').submit();
        }
    </script>
@endpush