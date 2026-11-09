@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Transfers</h1>
    @canroute('transfers.create')
    <a href="{{ route('transfers.create') }}" class="btn btn-primary mb-3">New Transfer</a>
    @endcanroute

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>ID</th>
                <th>From Warehouse</th>
                <th>To Warehouse</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($transfers as $transfer)
            <tr>
                <td>{{ $transfer->id }}</td>
                <td>{{ $transfer->fromWarehouse->name }}</td>
                <td>{{ $transfer->toWarehouse->name }}</td>
                <td>{{ ucfirst($transfer->status) }}</td>
                <td>
                    @if($transfer->status === 'draft')
                        @canroute('transfers.edit')
                        <a href="{{ route('transfers.edit', $transfer->id) }}" class="btn btn-sm btn-warning">Edit</a>
                        @endcanroute
                        @canroute('transfers.complete')
                        <form action="{{ route('transfers.complete', $transfer->id) }}" method="POST" style="display:inline;">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-success">Complete</button>
                        </form>
                        @endcanroute
                    @elseif($transfer->status === 'completed')
                        @canroute('transfers.cancel')
                        <form action="{{ route('transfers.cancel', $transfer->id) }}" method="POST" style="display:inline;">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-danger">Cancel</button>
                        </form>
                        @endcanroute
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection