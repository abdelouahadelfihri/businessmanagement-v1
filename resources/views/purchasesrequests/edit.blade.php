@extends('layouts.app')

@section('content')
    <div class="container mt-4">

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h1>Edit a Purchase Request</h1>
            <a href="{{ route('purchasesrequests.index') }}" class="btn btn-secondary">
                Back
            </a>
        </div>

        <div class="card shadow-sm">
            <div class="card-body">
                {{-- 🔹 Display Purchase Request ID --}}
                <div class="alert alert-info">
                    <strong>PR ID:</strong> {{ $purchaseRequest->id }} <br>
                    <strong>PR Number:</strong> {{ $purchaseRequest->pr_number }}
                </div>

                <form action="{{ route('purchasesrequests.update', $purchaseRequest) }}" method="POST">
                    @csrf
                    @method('PUT')

                    {{-- Supplier --}}
                    <div class="mb-3">
                        <label class="form-label">Supplier</label>

                        {{-- hidden supplier_id --}}
                        <input type="hidden" name="supplier_id" id="supplier_id"
                            value="{{ old('supplier_id', $purchaseRequest->supplier_id) }}">

                        {{-- visible name --}}
                        <div class="input-group">
                            <input type="text" id="supplier_name" class="form-control"
                                value="{{ optional($purchaseRequest->supplier)->name }}" readonly>
                            <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal"
                                data-bs-target="#supplierModal">
                                Pick Supplier
                            </button>
                        </div>
                    </div>

                    {{-- PR Number (readonly or editable, depending on your logic) --}}
                    <div class="mb-3">
                        <label class="form-label">PR Number</label>
                        <input type="text" name="pr_number" class="form-control"
                            value="{{ old('pr_number', $purchaseRequest->pr_number) }}" readonly>
                    </div>

                    {{-- Description --}}
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control"
                            rows="3">{{ old('description', $purchaseRequest->description) }}</textarea>
                    </div>

                    {{-- Date --}}
                    <div class="mb-3">
                        <label class="form-label">Date</label>
                        <input type="date" name="date" class="form-control"
                            value="{{ old('date', $purchaseRequest->date) }}" required>
                    </div>

                    {{-- Status --}}
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-control">
                            <option value="pending" {{ $purchaseRequest->status == 'pending' ? 'selected' : '' }}>Pending
                            </option>
                            <option value="approved" {{ $purchaseRequest->status == 'approved' ? 'selected' : '' }}>Approved
                            </option>
                            <option value="rejected" {{ $purchaseRequest->status == 'rejected' ? 'selected' : '' }}>Rejected
                            </option>
                        </select>
                    </div>

                    <!-- Actions -->
                    <div class="d-flex justify-content-end">
                        <a href="{{ route('purchasesrequests.index') }}" class="btn btn-outline-secondary me-2">
                            Cancel
                        </a>
                        <button type="submit" class="btn btn-primary">
                            Update
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection