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
                <td>
                    @if($receipt->status === 'draft')
                        <a href="{{ route('purchase-receipts.edit', $receipt->id) }}" class="btn btn-sm btn-warning">Edit</a>
                        <form action="{{ route('purchase-receipts.post', $receipt->id) }}" method="POST" style="display:inline;">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-success">Post</button>
                        </form>
                    @elseif($receipt->status === 'posted')
                        <form action="{{ route('purchase-receipts.cancel', $receipt->id) }}" method="POST" style="display:inline;">
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