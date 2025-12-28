@extends('layouts.app')

@section('content')
    <h3>Create Purchase Request</h3>

    <form method="POST" action="{{ route('salesquotations.store') }}">
        @csrf

        <div class="mb-3">
            <label>Supplier</label>
            <input type="hidden" name="customer_id" id="customer_id">
            <input type="text" id="customer_name" class="form-control" readonly>
            <button type="button" class="btn btn-secondary mt-2" data-bs-toggle="modal"
                data-bs-target="#customerModal">Select Supplier</button>
        </div>

        <div class="mb-3">
            <label>Quote Number</label>
            <input name="quote_number" class="form-control" value="{{ $preview }}" readonly>
        </div>
        <div class="mb-3">
            <label>Date</label>
            <input name="quotation_date" class="form-control">
        </div>
        <div class="mb-3">
            <label class="form-label">Status</label>
            <select name="status" class="form-select" required>
                <option value="draft" selected>Draft</option>
                <option value="sent">Sent</option>
                <option value="approved">Approved</option>
                <option value="rejected">Rejected</option>
                <option value="canceled">Canceled</option>
            </select>
        </div>

        <button class="btn btn-primary">Save</button>
    </form>

    @include('modals.customer-picker')
@endsection