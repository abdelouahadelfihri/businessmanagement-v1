@extends('layouts.app')

@section('content')
<div class="container mt-4">

    <h2>Edit Purchase Request</h2>

    <div class="card mt-3">
        <div class="card-body">

            <form action="{{ route('purchase_requests.update', $purchase_request->id) }}" method="POST">
                @csrf
                @method('PUT')

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
                        @foreach($suppliers as $s)
                            <option value="{{ $s->id }}"
                                {{ $purchase_request->supplier_id == $s->id ? 'selected' : '' }}>
                                {{ $s->name }}
                            </option>
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
                    <textarea name="description" class="form-control" required>
                        {{ $purchase_request->description }}
                    </textarea>
                </div>

                {{-- Date --}}
                <div class="mb-3">
                    <label class="form-label">Date</label>
                    <input type="date" name="date" class="form-control"
                           value="{{ $purchase_request->date }}" required>
                </div>

                {{-- Status --}}
                <div class="mb-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select" required>
                        <option value="pending"   {{ $purchase_request->status == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="approved"  {{ $purchase_request->status == 'approved' ? 'selected' : '' }}>Approved</option>
                    </select>
                </div>

                <button class="btn btn-primary mt-2">Update</button>

            </form>

        </div>
    </div>

</div>
@endsection