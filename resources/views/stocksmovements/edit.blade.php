@extends('layouts.app')
@section('content')
<div class="container">
    <h3>Edit Movement #{{ $movement->id }}</h3>
    <form action="{{ route('stock_movements.update', $movement) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label>Product</label>
            <input class="form-control" value="{{ $movement->product->name }}" disabled>
        </div>

        <div class="mb-3">
            <label>Warehouse</label>
            <select name="warehouse_id" class="form-control">
                @foreach($warehouses as $w)
                    <option value="{{ $w->id }}" {{ $movement->warehouse_id==$w->id?'selected':'' }}>{{ $w->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label>Movement Type</label>
            <select name="type" class="form-control">
                @foreach(['in','out','transfer_in','transfer_out','adjustment'] as $t)
                    <option value="{{ $t }}" {{ $movement->type==$t?'selected':'' }}>{{ ucfirst($t) }}</option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label>Quantity</label>
            <input type="number" name="quantity" class="form-control" value="{{ $movement->quantity }}" step="0.01">
        </div>

        <div class="mb-3">
            <label>Reason</label>
            <input type="text" name="reason" class="form-control" value="{{ $movement->reason }}">
        </div>

        <div class="mb-3">
            <label>Source Warehouse</label>
            <input class="form-control" value="{{ $movement->sourceWarehouse ? $movement->sourceWarehouse->name : '-' }}" disabled>
        </div>

        <div class="mb-3">
            <label>Source Document</label>
            <input class="form-control" value="{{ $movement->source_type ? class_basename($movement->source_type).' #'.$movement->source_id : '-' }}" disabled>
        </div>

        <button class="btn btn-primary">Update</button>
    </form>
</div>
@endsection