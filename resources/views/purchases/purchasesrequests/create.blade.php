@extends('layouts.app')

@section('content')
    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3>Create Purchase Request</h3>
            <a href="{{ route('purchasesrequests.index') }}" class="btn btn-secondary">
                Back
            </a>
        </div>
        <div class="card shadow-sm">
            <div class="card-body">
                <form method="POST" action="{{ route('purchasesrequests.store') }}" enctype="multipart/form-data">
                    @csrf

                    <!-- Supplier -->
                    <div class="mb-3">
                        <label class="form-label">Supplier</label>
                        <div class="input-group">
                            <input type="hidden" name="supplier_id" id="supplier_id" value="{{ old('supplier_id') }}">
                            <input type="text" id="supplier_name" class="form-control" readonly>
                            <button type="button" class="btn btn-secondary" data-bs-toggle="modal"
                                data-bs-target="#supplierModal">
                                Select Supplier
                            </button>
                        </div>
                        @error('supplier_id')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Description -->
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="3">{{ old('description') }}</textarea>
                    </div>

                    <div class="row">
                        <!-- Date -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Date</label>
                            <input type="date" name="date" class="form-control" value="{{ old('date') }}" required>
                        </div>

                        <!-- Expected Date -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Expected Date</label>
                            <input type="date" name="expected_date" class="form-control" value="{{ old('expected_date') }}">
                        </div>
                    </div>

                    <div class="row">
                        <!-- Priority -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Priority</label>
                            <select name="priority" class="form-control" required>
                                <option value="low" @selected(old('priority') == 'low')>Low</option>
                                <option value="medium" @selected(old('priority', 'medium') == 'medium')>Medium</option>
                                <option value="high" @selected(old('priority') == 'high')>High</option>
                                <option value="urgent" @selected(old('priority') == 'urgent')>Urgent</option>
                            </select>
                        </div>

                        <!-- Status -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-control" required>
                                <option value="draft" @selected(old('status', 'draft') == 'draft')>Draft</option>
                                <option value="pending" @selected(old('status') == 'pending')>Pending</option>
                                <option value="approved" @selected(old('status') == 'approved')>Approved</option>
                                <option value="rejected" @selected(old('status') == 'rejected')>Rejected</option>
                                <option value="ordered" @selected(old('status') == 'ordered')>Ordered</option>
                                <option value="completed" @selected(old('status') == 'completed')>Completed</option>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <!-- Total Amount -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Total Amount</label>
                            <input type="number" step="0.01" min="0" name="total_amount" class="form-control"
                                value="{{ old('total_amount') }}">
                        </div>

                        <!-- Currency -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Currency</label>
                            <input type="text" name="currency" class="form-control" maxlength="3"
                                value="{{ old('currency', 'MAD') }}">
                        </div>
                    </div>

                    <!-- Notes -->
                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <textarea name="notes" class="form-control" rows="2">{{ old('notes') }}</textarea>
                    </div>

                    <!-- Attachment -->
                    <div class="mb-3">
                        <label class="form-label">Attachment</label>
                        <input type="file" name="attachment" class="form-control">
                    </div>

                    <!-- Actions -->
                    <div class="d-flex justify-content-end">
                        <a href="{{ route('purchasesrequests.index') }}" class="btn btn-outline-secondary me-2">
                            Cancel
                        </a>
                        <button type="submit" class="btn btn-primary">
                            Save
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @include('modals.supplier-picker')
@endsection