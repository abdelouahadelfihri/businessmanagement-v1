@extends('layouts.app')

@section('content')
    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3>Create Purchase Request</h3>
            <a href="{{ route('categories.index') }}" class="btn btn-secondary">
                Back
            </a>
        </div>
    </div>

    <form method="POST" action="{{ route('purchase-requests.store') }}">
        @csrf

        <!-- Supplier -->
        <div class="mb-3">
            <label class="form-label">Supplier</label>
            <div class="input-group">
                <input type="hidden" name="supplier_id" id="supplier_id">
                <input type="text" id="supplier_name" class="form-control" readonly>
                <button type="button" class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#supplierModal">
                    Select Supplier
                </button>
            </div>
        </div>

        <!-- Description -->
        <div class="mb-3">
            <label class="form-label">Description</label>
            <textarea name="description" class="form-control" rows="3">{{ old('description') }}</textarea>
        </div>

        <!-- Date -->
        <div class="mb-3">
            <label class="form-label">Date</label>
            <input type="date" name="date" class="form-control" value="{{ old('date') }}" required>
        </div>

        <!-- Status -->
        <div class="mb-3">
            <label class="form-label">Status</label>
            <select name="status" class="form-control">
                <option value="pending">Pending</option>
                <option value="approved">Approved</option>
                <option value="rejected">Rejected</option>
            </select>
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
    @include('modals.supplier-picker')
@endsection