@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Purchase Receipts</h1>
        <a href="{{ route('purchase-receipts.create') }}" class="btn btn-primary mb-3">New Receipt</a>

        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Warehouse</th>
                    <th>Supplier</th>
                    <th>Date</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($receipts as $receipt)
                    <tr>
                        <td>{{ $receipt->id }}</td>
                        <td>{{ $receipt->warehouse->name }}</td>
                        <td>{{ $receipt->supplier->name ?? '-' }}</td>
                        <td>{{ $receipt->receipt_date }}</td>
                        <td>{{ ucfirst($receipt->status) }}</td>
                        <td class="text-center">
                            <div class="d-flex justify-content-center gap-1">
                                @if($receipt->status === 'draft')

                                    <!-- Edit -->
                                    <a href="{{ route('purchase-receipts.edit', $receipt->id) }}" class="btn btn-sm btn-warning"
                                        title="Edit">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>

                                    <!-- Post -->
                                    <form action="{{ route('purchase-receipts.post', $receipt->id) }}" method="POST"
                                        style="display:inline;">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-success" title="Post Receipt">
                                            <i class="bi bi-check-circle"></i>
                                        </button>
                                    </form>

                                    <!-- Delete (draft only) -->
                                    <form action="{{ route('purchase-receipts.destroy', $receipt->id) }}" method="POST"
                                        style="display:inline;" onsubmit="return confirm('Delete this receipt?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" title="Delete">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>

                                @elseif($receipt->status === 'posted')

                                    <!-- Cancel -->
                                    <form action="{{ route('purchase-receipts.cancel', $receipt->id) }}" method="POST"
                                        style="display:inline;" onsubmit="return confirm('Cancel this receipt?');">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-danger" title="Cancel Receipt">
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
@endsection