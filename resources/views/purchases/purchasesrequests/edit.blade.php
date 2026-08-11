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

                <form action="{{ route('purchasesrequests.update', $purchaseRequest) }}" method="POST" enctype="multipart/form-data">
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
                        @error('supplier_id')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Requested By (read-only, informational) --}}
                    <div class="mb-3">
                        <label class="form-label">Requested By</label>
                        <input type="text" class="form-control"
                            value="{{ optional($purchaseRequest->requestedBy)->name }}" readonly>
                    </div>

                    {{-- Description --}}
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control"
                            rows="3">{{ old('description', $purchaseRequest->description) }}</textarea>
                    </div>

                    <div class="row">
                        {{-- Date --}}
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Date</label>
                            <input type="date" name="date" class="form-control"
                                value="{{ old('date', optional($purchaseRequest->date)->format('Y-m-d')) }}" required>
                        </div>

                        {{-- Expected Date --}}
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Expected Date</label>
                            <input type="date" name="expected_date" class="form-control"
                                value="{{ old('expected_date', optional($purchaseRequest->expected_date)->format('Y-m-d')) }}">
                        </div>
                    </div>

                    <div class="row">
                        {{-- Priority --}}
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Priority</label>
                            <select name="priority" class="form-control" required>
                                <option value="low" @selected(old('priority', $purchaseRequest->priority) == 'low')>Low</option>
                                <option value="medium" @selected(old('priority', $purchaseRequest->priority) == 'medium')>Medium</option>
                                <option value="high" @selected(old('priority', $purchaseRequest->priority) == 'high')>High</option>
                                <option value="urgent" @selected(old('priority', $purchaseRequest->priority) == 'urgent')>Urgent</option>
                            </select>
                        </div>

                        {{-- Status --}}
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-control" required>
                                <option value="draft" @selected(old('status', $purchaseRequest->status) == 'draft')>Draft</option>
                                <option value="pending" @selected(old('status', $purchaseRequest->status) == 'pending')>Pending</option>
                                <option value="approved" @selected(old('status', $purchaseRequest->status) == 'approved')>Approved</option>
                                <option value="rejected" @selected(old('status', $purchaseRequest->status) == 'rejected')>Rejected</option>
                                <option value="ordered" @selected(old('status', $purchaseRequest->status) == 'ordered')>Ordered</option>
                                <option value="completed" @selected(old('status', $purchaseRequest->status) == 'completed')>Completed</option>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        {{-- Total Amount --}}
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Total Amount</label>
                            <input type="number" step="0.01" min="0" name="total_amount" class="form-control"
                                value="{{ old('total_amount', $purchaseRequest->total_amount) }}">
                        </div>

                        {{-- Currency --}}
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Currency</label>
                            <input type="text" name="currency" class="form-control" maxlength="3"
                                value="{{ old('currency', $purchaseRequest->currency) }}">
                        </div>
                    </div>

                    {{-- Notes --}}
                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <textarea name="notes" class="form-control" rows="2">{{ old('notes', $purchaseRequest->notes) }}</textarea>
                    </div>

                    {{-- Attachment --}}
                    <div class="mb-3">
                        <label class="form-label">Attachment</label>
                        @if($purchaseRequest->attachment)
                            <div class="mb-2">
                                <a href="{{ Storage::url($purchaseRequest->attachment) }}" target="_blank">
                                    View current attachment
                                </a>
                            </div>
                        @endif
                        <input type="file" name="attachment" class="form-control">
                        <small class="text-muted">Leave empty to keep the current file.</small>
                    </div>

                    {{-- Rejection Reason (only relevant if status is rejected) --}}
                    <div class="mb-3">
                        <label class="form-label">Rejection Reason</label>
                        <textarea name="rejection_reason" class="form-control"
                            rows="2">{{ old('rejection_reason', $purchaseRequest->rejection_reason) }}</textarea>
                    </div>

                    {{-- Approval Info (read-only, informational) --}}
                    @if($purchaseRequest->approved_by)
                        <div class="alert alert-secondary">
                            <strong>Approved/Reviewed by:</strong> {{ optional($purchaseRequest->approvedBy)->name }} <br>
                            <strong>On:</strong> {{ optional($purchaseRequest->approved_at)->format('Y-m-d H:i') }}
                        </div>
                    @endif

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
    @include('modals.supplier-picker')
@endsection