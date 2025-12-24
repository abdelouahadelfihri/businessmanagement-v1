@extends('layouts.app')

@section('content')
    <div class="container mt-4">

        <h1 class="mb-4">Edit Purchase Request</h1>

        <div class="card shadow-sm">
            <div class="card-body">

                {{-- ================= SUPPLIER PICKER FORM (GET) ================= --}}
                <form id="supplierPickerForm" method="GET" action="{{ route('suppliers.index') }}">
                    <input type="hidden" name="select_for" value="purchase-request-edit">
                    <input type="hidden" name="return_url"
                        value="{{ route('purchasesrequests.edit', $purchaseRequest->id) }}">

                    {{-- keep edited values --}}
                    <input type="hidden" id="request_date_hidden" name="request_date"
                        value="{{ old('request_date', $purchaseRequest->request_date) }}">
                    <input type="hidden" id="description_hidden" name="description"
                        value="{{ old('description', $purchaseRequest->description) }}">
                    <input type="hidden" id="status_hidden" name="status"
                        value="{{ old('status', $purchaseRequest->status) }}">

                    {{-- keep existing supplier if none chosen --}}
                    <input type="hidden" name="selected_supplier_id"
                        value="{{ $selectedSupplier?->id ?? $purchaseRequest->supplier_id }}">
                </form>

                {{-- ================= MAIN EDIT FORM ================= --}}
                <form action="{{ route('purchasesrequests.update', $purchaseRequest->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    {{-- ID --}}
                    <div class="mb-3">
                        <label class="form-label">Request ID</label>
                        <input type="text" class="form-control" value="{{ $purchaseRequest->id }}" disabled>
                    </div>

                    {{-- Supplier --}}
                    <div class="mb-3">
                        <label class="form-label">Supplier</label>

                        <div class="input-group">
                            <input type="text" class="form-control"
                                value="{{ $selectedSupplier?->name ?? $purchaseRequest->supplier->name }}" readonly>

                            <button type="button" class="btn btn-outline-secondary" onclick="submitSupplierPicker()">
                                Change
                            </button>
                        </div>

                        <input type="hidden" name="supplier_id"
                            value="{{ $selectedSupplier?->id ?? $purchaseRequest->supplier_id }}">

                        @error('supplier_id')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Request Date --}}
                    <div class="mb-3">
                        <label class="form-label">Request Date</label>
                        <input type="date" id="request_date_input" name="request_date" class="form-control"
                            value="{{ old('request_date', $purchaseRequest->request_date) }}" required>

                        @error('request_date')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Description --}}
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea id="description_input" name="description" class="form-control"
                            rows="3">{{ old('description', $purchaseRequest->description) }}</textarea>

                        @error('description')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Status --}}
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select id="status_input" name="status" class="form-select" required>
                            <option value="">-- Select status --</option>
                            <option value="draft" @selected(old('status', $purchaseRequest->status) === 'draft')>Draft
                            </option>
                            <option value="pending" @selected(old('status', $purchaseRequest->status) === 'pending')>Pending
                            </option>
                            <option value="approved" @selected(old('status', $purchaseRequest->status) === 'approved')>
                                Approved</option>
                        </select>

                        @error('status')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Actions --}}
                    <div class="d-flex gap-2">
                        <button class="btn btn-primary">Update</button>
                        <a href="{{ route('purchasesrequests.index') }}" class="btn btn-outline-secondary">Cancel</a>
                    </div>

                </form>

            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function submitSupplierPicker() {
            document.getElementById('request_date_hidden').value =
                document.getElementById('request_date_input').value;

            document.getElementById('description_hidden').value =
                document.getElementById('description_input').value;

            document.getElementById('status_hidden').value =
                document.getElementById('status_input').value;

            document.getElementById('supplierPickerForm').submit();
        }
    </script>
@endpush