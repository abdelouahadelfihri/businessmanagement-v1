// ---------------------------
// resources/views/purchase_requests/edit.blade.php
// ---------------------------
@extends('layout')
@section('content')
    <h3>Edit Purchase Request</h3>
    <form method="POST" action="{{ route('purchase-requests.update', $purchaseRequest->id) }}">
        @csrf @method('PUT')
        <div class="mb-3">
            <label>Request Number</label>
            <input type="text" name="request_number" value="{{ $purchaseRequest->request_number }}" class="form-control"
                required>
        </div>
        <div class="mb-3">
            <label>Supplier</label>
            <div class="input-group">
                <input type="text" id="supplier_name" class="form-control" value="{{ $purchaseRequest->supplier->name }}"
                    readonly>
                <input type="hidden" name="supplier_id" id="supplier_id" value="{{ $purchaseRequest->supplier_id }}">
                <button type="button" class="btn btn-secondary" data-bs-toggle="modal"
                    data-bs-target="#supplierModal">Pick</button>
            </div>
        </div>
        <button class="btn btn-success">Update</button>
    </form>
    @include('modals.supplier-picker')
@endsection