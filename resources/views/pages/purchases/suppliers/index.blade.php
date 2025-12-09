@extends('layouts.app')

@section('content')
<div class="container mt-4">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>Suppliers</h2>
        <a href="{{ route('suppliers.create') }}" class="btn btn-primary">Add Supplier</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <table class="table table-hover table-bordered">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Address</th>
                <th style="width:150px">Actions</th>
            </tr>
        </thead>

        <tbody>
            @foreach ($suppliers as $s)
                <tr>
                    <td>{{ $s->id }}</td>
                    <td>{{ $s->name }}</td>
                    <td>{{ $s->email }}</td>
                    <td>{{ $s->phone }}</td>
                    <td>{{ $s->address }}</td>
                    <td>
                        <a href="{{ route('suppliers.edit', $s->id) }}" class="btn btn-sm btn-warning">Edit</a>

                        <form action="{{ route('suppliers.destroy', $s->id) }}" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')

                            <button class="btn btn-sm btn-danger"
                                    onclick="return confirm('Delete this supplier?')">
                                Delete
                            </button>
                        </form>

                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{ $suppliers->links() }}
</div>
@endsection