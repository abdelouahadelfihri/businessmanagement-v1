@extends('layouts.app')

@section('content')
    <div class="container mt-4">

        <h1 class="mb-4">Create Sale Quotation</h1>

        <div class="card shadow-sm">
            <div class="card-body">

                {{-- ========================================================= --}}
                {{-- CUSTOMER PICKER (GET FORM) --}}
                {{-- ========================================================= --}}
                <form method="GET" action="{{ route('customers.index') }}" class="mb-4">
                    <input type="hidden" name="select_for" value="purchase-request">
                    <input type="hidden" name="return_url" value="{{ route('salesquotations.create') }}">

                    {{-- keep request date --}}
                    <input type="hidden" id="request_date_hidden" name="request_date"
                        value="{{ old('request_date', $form['request_date'] ?? '') }}">

                    {{-- keep description --}}
                    <input type="hidden" id="description_hidden" name="description"
                        value="{{ old('description', $form['description'] ?? '') }}">

                    {{-- keep status --}}
                    <input type="hidden" id="status_hidden" name="status"
                        value="{{ old('status', $form['status'] ?? 'draft') }}">

                    {{-- keep selected supplier --}}
                    @if(!empty($selectedCustomer))
                        <input type="hidden" name="selected_customer_id" value="{{ $selectedCustomer->id }}">
                    @endif

                    <button type="submit" class="btn btn-secondary">
                        Pick Customer
                    </button>
                </form>

                {{-- ========================================================= --}}
                {{-- MAIN SAVE FORM --}}
                {{-- ========================================================= --}}
                <form action="{{ route('salesquotations.store') }}" method="POST">
                    @csrf

                    {{-- Supplier --}}
                    <div class="mb-3">
                        <label class="form-label">Supplier</label>

                        <input type="text" class="form-control" value="{{ $selectedCustomer?->name }}"
                            placeholder="No customer selected" readonly>

                        <input type="hidden" name="customer_id" value="{{ $selectedCustomer?->id }}">

                        @error('customer_id')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Request Date --}}
                    <div class="mb-3">
                        <label class="form-label">Request Date</label>

                        <input type="date" id="request_date_input" name="request_date" class="form-control"
                            value="{{ old('request_date', $form['request_date'] ?? '') }}" required>

                        @error('request_date')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Description --}}
                    <div class="mb-3">
                        <label class="form-label">Description</label>

                        <textarea id="description_input" name="description" class="form-control" rows="3"
                            placeholder="Optional description">{{ old('description', $form['description'] ?? '') }}</textarea>

                        @error('description')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Status --}}
                    <div class="mb-3">
                        <label class="form-label">Status</label>

                        <select id="status_input" name="status" class="form-select">
                            <option value="draft" {{ old('status', $form['status'] ?? 'draft') === 'draft' ? 'selected' : '' }}>
                                Draft
                            </option>
                            <option value="pending" {{ old('status', $form['status'] ?? '') === 'pending' ? 'selected' : '' }}>
                                Pending
                            </option>
                            <option value="approved" {{ old('status', $form['status'] ?? '') === 'approved' ? 'selected' : '' }}>
                                Approved
                            </option>
                        </select>

                        @error('status')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Actions --}}
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            Save
                        </button>

                        <a href="{{ route('salesquotations.index') }}" class="btn btn-outline-secondary">
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
        document.addEventListener('DOMContentLoaded', function () {
            const pickerForm = document.querySelector('form[action="{{ route('customers.index') }}"]');

            pickerForm.addEventListener('submit', function () {
                document.getElementById('request_date_hidden').value =
                    document.getElementById('request_date_input').value;

                document.getElementById('description_hidden').value =
                    document.getElementById('description_input').value;

                document.getElementById('status_hidden').value =
                    document.getElementById('status_input').value;
            });
        });
    </script>
@endpush