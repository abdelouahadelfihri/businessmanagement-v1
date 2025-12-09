@extends('layouts.app')

@section('content')
<div class="container mt-4">

    <h2>Create Purchase Request</h2>

    <div class="card mt-3">
        <div class="card-body">

            <form action="{{ route('purchase_requests.store') }}" method="POST">
                @csrf

                {{-- Supplier --}}
                <div class="mb-3 d-flex justify-content-between">
                    <label class="form-label">Supplier</label>

                    <a href="{{ route('suppliers.create', ['return_to' => url()->current()]) }}"
                       class="btn btn-sm btn-outline-primary">
                        Add Supplier
                    </a>
                </div>

                @if($suppliers->count() > 0)
                    <select name="supplier_id" class="form-select mb-3" required>
                        <option value="">-- Choose Supplier --</option>
                        @foreach($suppliers as $s)
                            <option value="{{ $s->id }}">{{ $s->name }}</option>
                        @endforeach
                    </select>
                @else
                    <div class="alert alert-warning">
                        No suppliers available. Please add one.
                    </div>
                @endif

                {{-- Description --}}
                <div class="mb-3">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control" required></textarea>
                </div>

                {{-- Date --}}
                <div class="mb-3">
                    <label class="form-label">Date</label>
                    <input type="date" name="date" class="form-control" required>
                </div>

                {{-- Status --}}
                <div class="mb-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select" required>
                        <option value="pending">Pending</option>
                        <option value="approved">Approved</option>
                    </select>
                </div>

                <button class="btn btn-success mt-2">Save</button>

            </form>

        </div>
    </div>

</div>
@endsection