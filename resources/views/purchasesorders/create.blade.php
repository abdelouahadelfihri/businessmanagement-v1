@extends('layouts.app')

@section('content')
    <div class="container mt-4">

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3>Create Purchase Order</h3>
            <a href="{{ route('purchasesorders.index') }}" class="btn btn-secondary">Back</a>
        </div>

        <div class="card shadow-sm">
            <div class="card-body">

                <form method="POST" action="{{ route('purchasesorders.store') }}">
                    @csrf

                    {{-- HEADER --}}
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label>Supplier</label>
                            <div class="input-group">
                                <input type="hidden" name="supplier_id" id="supplier_id">
                                <input type="text" id="supplier_name" class="form-control" readonly>
                                <button type="button" class="btn btn-secondary" data-bs-toggle="modal"
                                    data-bs-target="#supplierModal">
                                    Select
                                </button>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <label>Order Date</label>
                            <input type="date" name="order_date" class="form-control" required>
                        </div>
                    </div>

                    {{-- ADD PRODUCT --}}
                    <button type="button" class="btn btn-secondary mb-2" id="add-line">
                        + Add Product
                    </button>

                    {{-- LINES --}}
                    <table class="table table-bordered" id="lines-table">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th width="120">Qty</th>
                                <th width="150">Unit Price</th>
                                <th width="60"></th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>

                    {{-- ACTIONS --}}
                    <div class="d-flex justify-content-end">
                        <a href="{{ route('purchasesorders.index') }}" class="btn btn-outline-secondary me-2">
                            Cancel
                        </a>
                        <button type="submit" class="btn btn-primary">
                            Save
                        </button>
                    </div>

                </form>

            </div>
        </div>
    </div>

    @include('modals.product-picker')
    @include('modals.supplier-picker')
@endsection