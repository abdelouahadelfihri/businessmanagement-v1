@extends('layouts.app')

@section('content')
<div class="container mt-4">

    <h2>Edit Supplier</h2>

    <div class="card mt-3">
        <div class="card-body">

            <form action="{{ route('suppliers.update', $supplier->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label class="form-label">Name</label>
                    <input type="text" name="name" class="form-control"
                           value="{{ $supplier->name }}" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control"
                           value="{{ $supplier->email }}">
                </div>

                <div class="mb-3">
                    <label class="form-label">Phone</label>
                    <input type="text" name="phone" class="form-control"
                           value="{{ $supplier->phone }}">
                </div>

                <div class="mb-3">
                    <label class="form-label">Address</label>
                    <textarea name="address" class="form-control">{{ $supplier->address }}</textarea>
                </div>

                <button class="btn btn-primary">Update</button>
            </form>

        </div>
    </div>

</div>
@endsection