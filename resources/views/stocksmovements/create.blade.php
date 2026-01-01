@extends('layouts.app')

@section('content')
<div class="container">
    <h3 class="mb-3">Add Stock Movement</h3>

    <form action="{{ route('stocksmovements.store') }}" method="POST">
        @csrf

        <!-- Product -->
        <div class="mb-3">
            <label>Product</label>
            <div class="input-group">
                <input id="product_name" class="form-control" placeholder="Select product" readonly required>
                <input type="hidden" name="product_id" id="product_id" required>
                <button type="button" class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#productModal">
                    Pick Product
                </button>
            </div>
            <small class="form-text text-muted">Select the product for which stock is being updated.</small>
        </div>

        <!-- Warehouse (Destination) -->
        <div class="mb-3">
            <label>Warehouse (Destination)</label>
            <div class="input-group">
                <input id="warehouse_name" class="form-control" placeholder="Select warehouse" readonly required>
                <input type="hidden" name="warehouse_id" id="warehouse_id" required>
                <button type="button" class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#warehouseModal">
                    Pick Warehouse
                </button>
            </div>
            <small class="form-text text-muted">
                The warehouse receiving or adjusting the stock. For transfers, this is the warehouse where the stock will arrive.
            </small>
        </div>

        <!-- Source Warehouse -->
        <div class="mb-3">
            <label>Source Warehouse</label>
            <div class="input-group">
                <input id="source_warehouse_name" class="form-control" placeholder="Select source warehouse (optional)" readonly>
                <input type="hidden" name="source_warehouse_id" id="source_warehouse_id">
                <button type="button" class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#sourceWarehouseModal">
                    Pick Source
                </button>
            </div>
            <small class="form-text text-muted">
                Only used for transfers — the warehouse sending the items. Leave empty unless you selected Transfer.
            </small>
        </div>

        <!-- Movement Type -->
        <div class="mb-3">
            <label>Type</label>
            <select name="type" class="form-control" required>
                <option value="in">IN (Stock Increase)</option>
                <option value="out">OUT (Stock Decrease)</option>
                <option value="transfer_in">Transfer IN (Receiving)</option>
                <option value="transfer_out">Transfer OUT (Sending)</option>
                <option value="adjustment">Adjustment (Manual Correction)</option>
            </select>
            <small class="form-text text-muted">
                Choose the reason for this movement — transfers require both source and destination warehouses.
            </small>
        </div>

        <!-- Quantity -->
        <div class="mb-3">
            <label>Quantity</label>
            <input type="number" name="quantity" class="form-control" step="0.01" required>
        </div>

        <!-- Reason -->
        <div class="mb-3">
            <label>Reason</label>
            <input type="text" name="reason" class="form-control" placeholder="Ex: New shipment, damage, cost adjustment...">
        </div>

        <!-- Date -->
        <div class="mb-3">
            <label>Date</label>
            <input type="date" name="date" class="form-control" value="{{ date('Y-m-d') }}" required>
        </div>

        <button class="btn btn-success">Save</button>
    </form>
</div>
@include('modals.supplier-picker')
@include('modals.supplier-picker')
@endsection