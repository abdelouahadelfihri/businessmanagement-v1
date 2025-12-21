@extends('layouts.app')

@section('content')
    <div class="container mt-4">

        <h1 class="mb-4">Create Purchase Request</h1>

        <div class="card shadow-sm">
            <div class="card-body">

                {{-- ========================================================= --}}
                {{-- SUPPLIER PICKER (GET FORM – NO VALIDATION, NO POST) --}}
                {{-- ========================================================= --}}
                <form method="GET" action="{{ route('suppliers.index') }}" class="mb-4">
                    <input type="hidden" name="select_for" value="purchase-request">
                    <input type="hidden" name="return_url" value="{{ route('purchasesrequests.create') }}">

                    {{-- keep request date --}}
                    <input type="hidden" name="request_date" value="{{ old('request_date', $form['request_date'] ?? '') }}">

                    {{-- keep selected supplier if any --}}
                    @if(!empty($selectedSupplier))
                        <input type="hidden" name="selected_supplier_id" value="{{ $selectedSupplier->id }}">
                    @endif

                    <button type="submit" class="btn btn-secondary">
                        Pick Supplier
                    </button>
                </form>

                {{-- ========================================================= --}}
                {{-- MAIN SAVE FORM (POST → store) --}}
                {{-- ========================================================= --}}
                <form action="{{ route('purchasesrequests.store') }}" method="POST">
                    @csrf

                    {{-- Supplier --}}
                    <div class="mb-3">
                        <label class="form-label">Supplier</label>

                        <input type="text" class="form-control" value="{{ $selectedSupplier?->name }}"
                            placeholder="No supplier selected" readonly>

                        <input type="hidden" name="supplier_id" value="{{ $selectedSupplier?->id }}">

                        @error('supplier_id')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Request Date --}}
                    <div class="mb-3">
                        <label class="form-label">Request Date</label>

                        <input type="date" name="request_date" class="form-control"
                            value="{{ old('request_date', $form['request_date'] ?? '') }}" required>

                        @error('request_date')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Actions --}}
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
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