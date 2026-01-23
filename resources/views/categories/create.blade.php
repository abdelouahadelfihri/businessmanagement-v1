@extends('layouts.app')

@section('content')
    <div class="container mt-4">

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h1>Create Category</h1>
            <a href="{{ route('categories.index') }}" class="btn btn-secondary">
                Back
            </a>
        </div>

        <div class="card shadow-sm">
            <div class="card-body">

                <form action="{{ route('categories.store') }}" method="POST">
                    @csrf

                    <!-- Name -->
                    <div class="mb-3">
                        <label class="form-label">Name</label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                            value="{{ old('name') }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Address -->
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <input type="text" name="description"
                            class="form-control @error('description') is-invalid @enderror"
                            value="{{ old('description') }}">
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Actions -->
                    <div class="d-flex justify-content-end">
                        <a href="{{ route('categories.index') }}" class="btn btn-outline-secondary me-2">
                            Cancel
                        </a>
                        <button type="submit" class="btn btn-primary">
                            Save Category
                        </button>
                    </div>

                </form>

            </div>
        </div>

    </div>
@endsection