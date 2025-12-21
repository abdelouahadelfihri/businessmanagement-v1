@extends('layouts.app')

@section('content')
    <div class="container mt-4">

        <h1 class="mb-4">Create Purchase Request</h1>

        <div class="card shadow-sm">
            <div class="card-body">

                <form action="{{ route('purchasesrequests.store') }}" method="POST">
                    @csrf

                    {{-- Supplier picker --}}
                    <div class="mb-3">
                        <label class="form-label">Supplier</label>

                        <div class="d-flex gap-2">
                            <input type="text" class="form-control" value="{{ $selectedSupplier?->name }}" readonly>

                            <div class="d-flex gap-2">
                                <form method="GET" action="{{ route('suppliers.index') }}">
                                    <input type="hidden" name="select_for" value="purchase-request">
                                    <input type="hidden" name="return_url" value="{{ route('purchasesrequests.create') }}">
                                    <input type="hidden" name="request_date"
                                        value="{{ old('request_date', request('request_date')) }}">
                                    <button class="btn btn-secondary">Pick Supplier</button>
                                </form>
                            </div>
                        </div>

                        <input type="hidden" name="supplier_id" id="supplier_id" value="{{ $selectedSupplier?->id }}"
                            required>
                    </div>

                    {{-- Request date --}}
                    <div class="mb-3">
                        <label class="form-label">Request Date</label>
                        <input type="date" name="request_date"
                            value="{{ old('request_date', $formData['request_date'] ?? date('Y-m-d')) }}">
                    </div>

                    <button class="btn btn-primary">Save</button>
                </form>

            </div>
        </div>

    </div>
@endsection