@extends('layouts.app')

@section('content')
<div class="container">
    <h3 class="mb-3">Edit Stock Movement #{{ $movement->id }}</h3>

    <form action="{{ route('stocksmovements.update', $movement) }}" method="POST">
        @csrf
        @method('PUT')

        <!-- Product (readonly) -->
        <div class="mb-3">
            <label>Product</label>
            <div class="input-group">
                <input id="product_name" class="form-control" value="{{ $movement->product->name }}" readonly>
                <input type="hidden" name="product_id" id="product_id" value="{{ $movement->product->id }}">
                <button type="button" class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#productModal" disabled>
                    Pick Product
                </button>
            </div>
            <small class="form-text text-muted">Product cannot be changed once the movement is created.</small>
        </div>

        <!-- Warehouse (Destination) -->
        <div class="mb-3">
            <label>Warehouse (Destination)</label>
            <div class="input-group">
                <input id="warehouse_name" class="form-control" value="{{ $movement->warehouse->name }}" readonly>
                <input type="hidden" name="warehouse_id" id="warehouse_id" value="{{ $movement->warehouse_id }}">
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
                <input id="source_warehouse_name" class="form-control" 
                       value="{{ $movement->sourceWarehouse ? $movement->sourceWarehouse->name : '' }}" readonly>
                <input type="hidden" name="source_warehouse_id" id="source_warehouse_id" 
                       value="{{ $movement->source_warehouse_id }}">
                <button type="button" class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#sourceWarehouseModal">
                    Pick Source
                </button>
            </div>
            <small class="form-text text-muted">
                Only used for transfers — the warehouse sending the items.
            </small>
        </div>

        <!-- Movement Type -->
        <div class="mb-3">
            <label>Type</label>
            <select name="type" class="form-control" required>
                @foreach(['in', 'out', 'transfer_in', 'transfer_out', 'adjustment'] as $t)
                    <option value="{{ $t }}" {{ $movement->type == $t ? 'selected' : '' }}>{{ ucfirst($t) }}</option>
                @endforeach
            </select>
            <small class="form-text text-muted">
                Choose the reason for this movement — transfers require both source and destination warehouses.
            </small>
        </div>

        <!-- Quantity -->
        <div class="mb-3">
            <label>Quantity</label>
            <input type="number" name="quantity" class="form-control" step="0.01" value="{{ $movement->quantity }}" required>
        </div>

        <!-- Reason -->
        <div class="mb-3">
            <label>Reason</label>
            <input type="text" name="reason" class="form-control" value="{{ $movement->reason }}">
        </div>

        <!-- Date -->
        <div class="mb-3">
            <label>Date</label>
            <input type="date" name="date" class="form-control" value="{{ $movement->date->format('Y-m-d') }}" required>
        </div>

        <button class="btn btn-primary">Update</button>
    </form>
</div>
@endsection