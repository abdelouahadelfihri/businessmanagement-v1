@extends('layouts.app')

@section('content')
<div class="container mt-4">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>Purchase Requests</h2>
        <a href="{{ route('purchase_requests.create') }}" class="btn btn-primary">Add Purchase Request</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <table class="table table-bordered table-striped">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Supplier</th>
                <th>Description</th>
                <th>Date</th>
                <th>Status</th>
                <th style="width: 150px;">Actions</th>
            </tr>
        </thead>

        <tbody>
            @foreach($requests as $req)
                <tr>
                    <td>{{ $req->id }}</td>
                    <td>{{ $req->supplier->name ?? '—' }}</td>
                    <td>{{ $req->description }}</td>
                    <td>{{ $req->date }}</td>
                    <td>{{ ucfirst($req->status) }}</td>
                    <td>
                        <a href="{{ route('purchase_requests.edit', $req->id) }}" class="btn btn-sm btn-warning">Edit</a>

                        <form action="{{ route('purchase_requests.destroy', $req->id) }}"
                              method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')

                            <button type="submit" onclick="return confirm('Delete this?')"
                                    class="btn btn-sm btn-danger">
                                Delete
                            </button>
                        </form>

                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{ $requests->links() }}
</div>
@endsection