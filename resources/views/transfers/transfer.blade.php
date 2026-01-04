@extends('layouts.app')
@section('content')
    <div class="container">
        <h3>Transfer Stock</h3>
        <form action="{{ route('stock_movements.transfer') }}" method="POST">
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
                <label>From Warehouse</label>
                <select name="from_warehouse" class="form-control" required>
                    @foreach($warehouses as $w)
                        <option value="{{ $w->id }}">{{ $w->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="mb-3">
                <label>To Warehouse</label>
                <select name="to_warehouse" class="form-control" required>
                    @foreach($warehouses as $w)
                        <option value="{{ $w->id }}">{{ $w->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="mb-3">
                <label>Quantity</label>
                <input type="number" step="0.01" class="form-control" name="quantity" required>
            </div>

            <button class="btn btn-success">Transfer</button>
        </form>
    </div>
@endsection