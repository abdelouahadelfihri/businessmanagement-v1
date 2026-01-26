@extends('layouts.app')

@section('content')
    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3>Create Purchase Receipt</h3>
            <a href="{{ route('purchasesreceipts.index') }}" class="btn btn-secondary">
                Back
            </a>
        </div>
        <div class="card shadow-sm">
            <div class="card-body">

                <form action="{{ route('purchasesreceipts.store') }}" method="POST">
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

                    <div class="d-flex justify-content-end">
                        <a href="{{ route('purchasesrequests.index') }}" class="btn btn-outline-secondary me-2">
                            Cancel
                        </a>
                        <button type="button" class="btn btn-secondary" id="add-line">
                            Add Product
                        </button>
                        <button type="submit" class="btn btn-primary">
                            Save
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    {{-- PRODUCT MODAL --}}
    @include('modals.product-picker')
    {{-- SUPPLIER MODAL --}}
    @include('modals.supplier-picker')
@endsection