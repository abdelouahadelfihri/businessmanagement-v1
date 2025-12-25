@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h1 class="mb-4">Edit Purchase Invoice #{{ $purchaseInvoice->id }}</h1>

    <div class="card shadow-sm">
        <div class="card-body">

            {{-- Supplier Picker --}}
            <form id="supplierPickerForm" method="GET" action="{{ route('suppliers.index') }}">
                <input type="hidden" name="select_for" value="purchase-invoice">
                <input type="hidden" name="return_url" value="{{ route('purchaseinvoices.edit', $purchaseInvoice) }}">
                <input type="hidden" id="purchase_order_id_hidden" name="purchase_order_id"
                    value="{{ old('purchase_order_id', $purchaseInvoice->purchase_order_id) }}">
                <input type="hidden" id="invoice_number_hidden" name="invoice_number"
                    value="{{ old('invoice_number', $purchaseInvoice->invoice_number) }}">
                <input type="hidden" id="subtotal_hidden" name="subtotal"
                    value="{{ old('subtotal', $purchaseInvoice->subtotal) }}">
                <input type="hidden" id="tax_hidden" name="tax" value="{{ old('tax', $purchaseInvoice->tax) }}">
                <input type="hidden" id="total_hidden" name="total" value="{{ old('total', $purchaseInvoice->total) }}">
                <input type="hidden" id="status_hidden" name="status"
                    value="{{ old('status', $purchaseInvoice->status) }}">
            </form>

            <form action="{{ route('purchaseinvoices.update', $purchaseInvoice) }}" method="POST">
                @csrf
                @method('PUT')

                {{-- Supplier --}}
                <div class="mb-3">
                    <label class="form-label">Supplier</label>
                    <div class="input-group">
                        <input type="text" class="form-control"
                            value="{{ old('supplier_name', $purchaseInvoice->supplier?->name) }}" readonly>
                        <button type="button" class="btn btn-outline-secondary"
                            onclick="submitSupplierPicker()">Pick</button>
                    </div>
                    <input type="hidden" name="supplier_id"
                        value="{{ old('supplier_id', $purchaseInvoice->supplier_id) }}">
                </div>

                {{-- Purchase Order --}}
                <div class="mb-3">
                    <label class="form-label">Purchase Order</label>
                    <input type="text" class="form-control"
                        value="{{ old('purchase_order_display', $purchaseInvoice->purchaseOrder?->id) }}" readonly>
                    <input type="hidden" name="purchase_order_id"
                        value="{{ old('purchase_order_id', $purchaseInvoice->purchase_order_id) }}">
                </div>

                {{-- Invoice Number --}}
                <div class="mb-3">
                    <label class="form-label">Invoice Number</label>
                    <input type="text" name="invoice_number" class="form-control"
                        value="{{ old('invoice_number', $purchaseInvoice->invoice_number) }}">
                </div>

                {{-- Subtotal, Tax, Total --}}
                <div class="mb-3">
                    <label class="form-label">Subtotal</label>
                    <input type="number" step="0.01" name="subtotal" class="form-control"
                        value="{{ old('subtotal', $purchaseInvoice->subtotal) }}">
                </div>

                <div class="mb-3">
                    <label class="form-label">Tax</label>
                    <input type="number" step="0.01" name="tax" class="form-control"
                        value="{{ old('tax', $purchaseInvoice->tax) }}">
                </div>

                <div class="mb-3">
                    <label class="form-label">Total</label>
                    <input type="number" step="0.01" name="total" class="form-control"
                        value="{{ old('total', $purchaseInvoice->total) }}">
                </div>

                {{-- Status --}}
                <div class="mb-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select" required>
                        <option value="">-- Select Status --</option>
                        <option value="draft" @selected(old('status', $purchaseInvoice->status) === 'draft')>Draft
                        </option>
                        <option value="pending" @selected(old('status', $purchaseInvoice->status) === 'pending')>Pending
                        </option>
                        <option value="approved" @selected(old('status', $purchaseInvoice->status) === 'approved')>
                            Approved</option>
                    </select>
                </div>

                <div class="d-flex gap-2">
                    <button class="btn btn-primary">Update</button>
                    <a href="{{ route('purchaseinvoices.index') }}" class="btn btn-outline-secondary">Cancel</a>
                </div>

            </form>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        function submitSupplierPicker() {
            document.getElementById('purchase_order_id_hidden').value = document.getElementById('purchase_order_id_hidden').value;
            document.getElementById('invoice_number_hidden').value = document.querySelector('[name="invoice_number"]').value;
            document.getElementById('subtotal_hidden').value = document.querySelector('[name="subtotal"]').value;
            document.getElementById('tax_hidden').value = document.querySelector('[name="tax"]').value;
            document.getElementById('total_hidden').value = document.querySelector('[name="total"]').value;
            document.getElementById('status_hidden').value = document.querySelector('[name="status"]').value;
            document.getElementById('supplierPickerForm').submit();
        }
    </script>
@endpush