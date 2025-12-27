@extends('layout')
@section('content')
    <h3>Edit Purchase Order</h3>
    <form method="POST" action="{{ route('purchase-orders.update', $purchaseOrder->id) }}">
        @csrf @method('PUT')
        <div class="mb-3">
            <label>Order Number</label>
            <input type="text" name="order_number" value="{{ $purchaseOrder->order_number }}" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>Supplier</label>
            <div class="input-group">
                <input type="text" id="order_supplier_name" class="form-control"
                    value="{{ $purchaseOrder->supplier->name }}" readonly>
                <input type="hidden" name="supplier_id" id="order_supplier_id" value="{{ $purchaseOrder->supplier_id }}"
                    onchange="clearOrder()">
                <button type="button" class="btn btn-secondary" data-bs-toggle="modal"
                    data-bs-target="#supplierModal">Pick</button>
            </div>
        </div>
        <div class="mb-3">
            <label>Purchase Request</label>
            <div class="input-group">
                <input type="text" id="order_request_number" class="form-control"
                    value="{{ $purchaseOrder->purchaseRequest->request_number }}" readonly>
                <input type="hidden" name="purchase_request_id" id="order_request_id"
                    value="{{ $purchaseOrder->purchase_request_id }}">
                <button type="button" class="btn btn-secondary" data-bs-toggle="modal"
                    data-bs-target="#requestModal">Pick</button>
            </div>
        </div>
        <button class="btn btn-success">Update</button>
    </form>
    @include('modals.supplier-picker')
    @include('modals.request-picker')
    <script>
        function clearOrder() {
            document.getElementById('order_request_number').value = '';
            document.getElementById('order_request_id').value = '';
        }
    </script>
@endsection