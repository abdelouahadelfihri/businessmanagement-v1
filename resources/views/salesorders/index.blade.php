@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Purchase Orders</h1>
    <a href="{{ route('purchase-orders.create') }}" class="btn btn-primary mb-3">New Purchase Order</a>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>ID</th>
                <th>Supplier</th>
                <th>Date</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($orders as $order)
            <tr>
                <td>{{ $order->id }}</td>
                <td>{{ $order->supplier->name ?? '-' }}</td>
                <td>{{ $order->order_date }}</td>
                <td>{{ ucfirst($order->status) }}</td>
                <td>
                    @if($order->status === 'draft')
                        <a href="{{ route('purchase-orders.edit', $order->id) }}" class="btn btn-sm btn-warning">Edit</a>
                        <form action="{{ route('purchase-orders.post', $order->id) }}" method="POST" style="display:inline;">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-success">Post</button>
                        </form>
                    @elseif($order->status === 'posted')
                        <form action="{{ route('purchase-orders.cancel', $order->id) }}" method="POST" style="display:inline;">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-danger">Cancel</button>
                        </form>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection