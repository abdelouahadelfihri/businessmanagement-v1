@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Create Purchase Receipt</h1>

        <form action="{{ route('purchase-receipts.store') }}" method="POST">
            @csrf

            <div class="mb-3">
                <label>Warehouse</label>
                <select name="warehouse_id" class="form-control" required>
                    @foreach($warehouses as $warehouse)
                        <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="mb-3">
                <label>Supplier</label>
                <div class="input-group">
                    <input type="hidden" name="supplier_id" id="supplier_id">
                    <input type="text" id="supplier_name" class="form-control" readonly>
                    <button type="button" class="btn btn-secondary" data-bs-toggle="modal"
                        data-bs-target="#supplierModal">Select</button>
                </div>
            </div>

            <div class="mb-3">
                <label>Receipt Date</label>
                <input type="date" name="receipt_date" class="form-control" required>
            </div>

            <h4>Products</h4>
            <table class="table" id="lines-table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Quantity</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>

            <button type="button" class="btn btn-secondary" id="add-line">Add Product</button>
            <button type="submit" class="btn btn-primary">Save Receipt</button>
        </form>
    </div>
    {{-- PRODUCT MODAL --}}
    @include('modals.product-picker')
    {{-- SUPPLIER MODAL --}}
    @include('modals.supplier-picker')
@endsection