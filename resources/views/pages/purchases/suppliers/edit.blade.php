@extends('layouts.app')

@section('content')
<div class="container mt-4">

    <h1 class="mb-4">Edit Supplier</h1>

    <div class="card shadow-sm">
        <div class="card-body">

            <form action="{{ route('suppliers.update', $supplier) }}" method="POST">
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

                <button class="btn btn-primary">Update</button>
                <a href="{{ route('suppliers.index') }}" class="btn btn-secondary ms-2">Back</a>

            </form>

        </div>
    </div>

</div>
@endsection