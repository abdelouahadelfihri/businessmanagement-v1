@extends('layouts.app')

@section('content')
    <div class="container mt-4">

        <h1 class="mb-4">Purchase Orders</h1>

        <a class="btn btn-primary mb-3" href="{{ route('purchase-orders.create') }}">
            Create Purchase Order
        </a>

        <div class="card shadow-sm">
            <div class="card-body p-0">

                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Supplier</th>
                            <th>Request</th>
                            <th>Order Date</th>
                            <th width="150">Actions</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach($orders as $o)
                            <tr>
                                <td>{{ $o->id }}</td>
                                <td>{{ $o->supplier->name }}</td>
                                <td>{{ $o->purchaseRequest->title }}</td>
                                <td>{{ $o->order_date }}</td>
                                <td class="text-center">
                                    <div class="d-flex justify-content-center gap-1">
                                        <a href="{{ route('sale-orders.edit', $order->id) }}" class="btn btn-sm btn-warning">
                                            <i class="bi bi-pencil-square"></i>
                                        </a>
                                        <form action="{{ route('sale-orders.destroy', $order->id) }}" method="POST"
                                            style="display:inline;">
                                            @csrf @method('DELETE')
                                            <button class="btn btn-sm btn-danger">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-3">
            {{ $orders->links() }}
        </div>

    </div>
@endsection