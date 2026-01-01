@extends('layouts.app')
@section('content')
<div class="container">
    <h3>Add Stock Movement</h3>
    <form action="{{ route('stock_movements.store') }}" method="POST">
        @csrf
        <div class="mb-3">
            <label>Product</label>
            <select name="product_id" class="form-control" required>
                @foreach($products as $p)
                    <option value="{{ $p->id }}">{{ $p->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label>Warehouse</label>
            <select name="warehouse_id" class="form-control" required>
                @foreach($warehouses as $w)
                    <option value="{{ $w->id }}">{{ $w->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label>Type</label>
            <select name="type" class="form-control" required>
                <option value="in">IN</option>
                <option value="out">OUT</option>
                <option value="transfer_in">Transfer In</option>
                <option value="transfer_out">Transfer Out</option>
                <option value="adjustment">Adjustment</option>
            </select>
        </div>

        <div class="mb-3">
            <label>Quantity</label>
            <input type="number" name="quantity" class="form-control" step="0.01" required>
        </div>

        <div class="mb-3">
            <label>Reason</label>
            <input type="text" name="reason" class="form-control">
        </div>

        <div class="mb-3">
            <label>Date</label>
            <input type="date" name="date" class="form-control" value="{{ date('Y-m-d') }}" required>
        </div>

        <button class="btn btn-success">Save</button>
    </form>
</div>
@endsection