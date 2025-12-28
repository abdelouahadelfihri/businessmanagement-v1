@extends('layouts.app')

@section('content')
    <h3>Create Purchase Request</h3>

    <form method="POST" action="{{ route('purchase-requests.store') }}">
        @csrf

        <div class="mb-3">
            <label>Supplier</label>
            <input type="hidden" name="supplier_id" id="supplier_id">
            <input type="text" id="supplier_name" class="form-control" readonly>
            <button type="button" class="btn btn-secondary mt-2" data-bs-toggle="modal"
                data-bs-target="#supplierModal">Select Supplier</button>
        </div>

        <div class="mb-3">
            <label>Purchase Request No.</label>
            <input type="text" class="form-control" value="{{ $preview }}" readonly>
        </div>

        <button class="btn btn-primary">Save</button>
    </form>

    @include('modals.supplier-picker')
@endsection