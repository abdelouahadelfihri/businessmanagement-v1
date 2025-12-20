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
                            <input type="text" class="form-control" value="{{ $selectedSupplier?-> }}" readonly>

                            <a class="btn btn-secondary" href="{{ route('suppliers.index', [
                            'select_for' => 'purchase-request',
                            'return_url' => url()->current()]) }}">
                                Pick Supplier
                            </a>
                        </div>

                        <input type="hidden" name="supplier_id" id="supplier_id" value="{{ $selectedSupplier?->id }}"
                            required>
                    </div>

                    {{-- Request date --}}
                    <div class="mb-3">
                        <label class="form-label">Request Date</label>
                        <input type="date" name="request_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                    </div>

                    <button class="btn btn-primary">Save</button>
                </form>

            </div>
        </div>

    </div>
@endsection