@extends('layouts.app')

@section('content')
    <div class="container mt-4">

        <h1 class="mb-4">Create Purchase Request</h1>

        <div class="card shadow-sm">
            <div class="card-body">

                {{-- ================= Main Form ================= --}}
                <form action="{{ route('purchasesrequests.store') }}" method="POST">
                    @csrf

                    {{-- ================= Supplier Picker ================= --}}
                    <div class="mb-3">
                        <label class="form-label">Supplier</label>

                        <div class="d-flex gap-2 align-items-center">
                            <input type="text" class="form-control" value="{{ $selectedSupplier?->name }}"
                                placeholder="No supplier selected" readonly>

                            <a class="btn btn-secondary" href="{{ route('suppliers.index', array_merge([
        'select_for' => 'purchase-request',
        'return_url' => route('purchasesrequests.create')
    ], $form ?? [])) }}">
                                Pick Supplier
                            </a>
                        </div>

                        {{-- actual value submitted --}}
                        <input type="hidden" name="supplier_id" value="{{ $selectedSupplier?->id }}" required>

                        @error('supplier_id')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- ================= Request Date ================= --}}
                    <div class="mb-3">
                        <label class="form-label">Request Date</label>
                        <input type="date" name="request_date" class="form-control"
                            value="{{ old('request_date', $form['request_date'] ?? '') }}" required>

                        @error('request_date')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- ================= Actions ================= --}}
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