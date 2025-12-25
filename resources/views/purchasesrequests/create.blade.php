@extends('layouts.app')

@section('content')
    <div class="container mt-4">
        <h1 class="mb-4 fw-semibold">Create Purchase Request</h1>

        <div class="card shadow-sm">
            <div class="card-body">

                {{-- ===== SUPPLIER PICKER (GET) ===== --}}
                <form id="supplierPickerForm" method="GET" action="{{ route('suppliers.index') }}">
                    <input type="hidden" name="select_for" value="purchase-request">
                    <input type="hidden" name="return_url" value="{{ route('purchasesrequests.create') }}">

                    {{-- keep form values --}}
                    <input type="hidden" id="request_date_hidden" name="request_date"
                        value="{{ old('request_date', $form['request_date'] ?? '') }}">
                    <input type="hidden" id="description_hidden" name="description"
                        value="{{ old('description', $form['description'] ?? '') }}">
                    <input type="hidden" id="status_hidden" name="status"
                        value="{{ old('status', $form['status'] ?? '') }}">

                    @if(!empty($selectedSupplier))
                        <input type="hidden" name="selected_supplier_id" value="{{ $selectedSupplier->id }}">
                    @endif
                </form>

                {{-- ===== MAIN SAVE FORM ===== --}}
                <form action="{{ route('purchasesrequests.store') }}" method="POST">
                    @csrf

                    {{-- Supplier --}}
                    <div class="mb-3">
                        <label class="form-label fw-medium">Supplier</label>
                        <div class="input-group" style="max-width: 420px;">
                            <input type="text" class="form-control" value="{{ $selectedSupplier?->name }}"
                                placeholder="No supplier selected" readonly>

                            <button type="button" class="btn btn-outline-secondary" onclick="submitSupplierPicker()">
                                Pick
                            </button>
                        </div>
                        <input type="hidden" name="supplier_id" value="{{ $selectedSupplier?->id }}">
                        @error('supplier_id')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Request Date --}}
                    <div class="mb-3" style="max-width: 260px;">
                        <label class="form-label fw-medium">Request Date</label>
                        <input type="date" id="request_date_input" name="request_date" class="form-control"
                            value="{{ old('request_date', $form['request_date'] ?? '') }}" required>
                        @error('request_date')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Description --}}
                    <div class="mb-3">
                        <label class="form-label fw-medium">Description</label>
                        <textarea id="description_input" name="description" class="form-control" rows="3"
                            placeholder="Enter a description...">{{ old('description', $form['description'] ?? '') }}</textarea>
                        @error('description')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Status --}}
                    <div class="mb-3" style="max-width: 260px;">
                        <label class="form-label fw-medium">Status</label>
                        <select id="status_input" name="status" class="form-select" required>
                            <option value="">— Select status —</option>
                            <option value="draft" @selected(($form['status'] ?? '') === 'draft')>Draft</option>
                            <option value="pending" @selected(($form['status'] ?? '') === 'pending')>Pending</option>
                            <option value="approved" @selected(($form['status'] ?? '') === 'approved')>Approved</option>
                        </select>
                        @error('status')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- ACTIONS --}}
                    <div class="d-flex gap-2 mt-4">
                        <button class="btn btn-primary px-4">
                            Save
                        </button>
                        <a href="{{ route('purchasesrequests.index') }}" class="btn btn-outline-secondary">
                            Cancel
                        </a>
                    </div>

                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function submitSupplierPicker() {
            document.getElementById('request_date_hidden').value = document.getElementById('request_date_input').value;
            document.getElementById('description_hidden').value = document.getElementById('description_input').value;
            document.getElementById('status_hidden').value = document.getElementById('status_input').value;
            document.getElementById('supplierPickerForm').submit();
        }
    </script>
@endpush