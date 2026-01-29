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

                                        @if($invoice->status === 'draft')

                                            <!-- Edit -->
                                            <a href="{{ route('purchase-invoices.edit', $invoice->id) }}"
                                                class="btn btn-sm btn-warning" title="Edit Invoice">
                                                <i class="bi bi-pencil-square"></i>
                                            </a>

                                            <!-- Post -->
                                            <form action="{{ route('purchase-invoices.post', $invoice->id) }}" method="POST"
                                                style="display:inline;">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-success" title="Post Invoice">
                                                    <i class="bi bi-check-circle"></i>
                                                </button>
                                            </form>

                                            <!-- Delete (draft only) -->
                                            <form action="{{ route('purchase-invoices.destroy', $invoice->id) }}" method="POST"
                                                style="display:inline;" onsubmit="return confirm('Delete this invoice?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger" title="Delete Invoice">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>

                                        @elseif($invoice->status === 'posted')

                                            <!-- Cancel -->
                                            <form action="{{ route('purchase-invoices.cancel', $invoice->id) }}" method="POST"
                                                style="display:inline;" onsubmit="return confirm('Cancel this invoice?');">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-danger" title="Cancel Invoice">
                                                    <i class="bi bi-x-circle"></i>
                                                </button>
                                            </form>

                                        @endif

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