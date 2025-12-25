@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h1 class="mb-4">Edit Purchase Order #{{ $purchaseOrder->id }}</h1>

    <div class="card shadow-sm">
        <div class="card-body">

            {{-- Supplier Picker --}}
            <form id="supplierPickerForm" method="GET" action="{{ route('suppliers.index') }}">
                <input type="hidden" name="select_for" value="purchase-order">
                <input type="hidden" name="return_url" value="{{ route('purchaseorders.edit', $purchaseOrder) }}">
                <input type="hidden" id="request_id_hidden" name="request_id"
                    value="{{ old('request_id', $purchaseOrder->request_id) }}">
                <input type="hidden" id="order_date_hidden" name="order_date"
                    value="{{ old('order_date', $purchaseOrder->order_date) }}">
                <input type="hidden" id="status_hidden" name="status"
                    value="{{ old('status', $purchaseOrder->status) }}">
            </form>

            <form action="{{ route('purchaseorders.update', $purchaseOrder) }}" method="POST">
                @csrf
                @method('PUT')

                {{-- Supplier --}}
                <div class="mb-3">
                    <label class="form-label">Supplier</label>
                    <div class="input-group">
                        <input type="text" class="form-control"
                            value="{{ old('supplier_name', $purchaseOrder->supplier?->name) }}" readonly>
                        <button type="button" class="btn btn-outline-secondary"
                            onclick="submitSupplierPicker()">Pick</button>
                    </div>
                    <input type="hidden" name="supplier_id"
                        value="{{ old('supplier_id', $purchaseOrder->supplier_id) }}">
                </div>

                {{-- Purchase Request --}}
                <div class="mb-3">
                    <label class="form-label">Purchase Request</label>
                    <input type="text" class="form-control"
                        value="{{ old('request_display', $purchaseOrder->request?->id) }}" readonly>
                    <input type="hidden" name="request_id" value="{{ old('request_id', $purchaseOrder->request_id) }}">
                </div>

                {{-- Order Date --}}
                <div class="mb-3">
                    <label class="form-label">Order Date</label>
                    <input type="date" name="order_date" id="order_date_input" class="form-control"
                        value="{{ old('order_date', $purchaseOrder->order_date) }}" required>
                </div>

                {{-- Status --}}
                <div class="mb-3">
                    <label class="form-label">Status</label>
                    <select name="status" id="status_input" class="form-select" required>
                        <option value="">-- Select Status --</option>
                        <option value="draft" @selected(old('status', $purchaseOrder->status) === 'draft')>Draft</option>
                        <option value="pending" @selected(old('status', $purchaseOrder->status) === 'pending')>Pending
                        </option>
                        <option value="approved" @selected(old('status', $purchaseOrder->status) === 'approved')>Approved
                        </option>
                    </select>
                </div>

                <div class="d-flex gap-2">
                    <button class="btn btn-primary">Update</button>
                    <a href="{{ route('purchaseorders.index') }}" class="btn btn-outline-secondary">Cancel</a>
                </div>

            </form>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        function submitSupplierPicker() {
            document.getElementById('request_id_hidden').value = document.getElementById('request_id_hidden').value;
            document.getElementById('order_date_hidden').value = document.getElementById('order_date_input').value;
            document.getElementById('status_hidden').value = document.getElementById('status_input').value;
            document.getElementById('supplierPickerForm').submit();
        }
    </script>
@endpush