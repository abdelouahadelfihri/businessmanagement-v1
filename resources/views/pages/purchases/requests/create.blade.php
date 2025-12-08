@extends('layouts.app')

@section('content')

<h2>Create Purchase Request</h2>

<form id="requestForm">
    <input type="hidden" name="supplier_id" id="supplier_id" value="{{ request('supplier_id') }}">

    <div class="mb-3">
        <label>Supplier:</label>
        <div class="d-flex">
            <input type="text"
                   id="supplier_name"
                   class="form-control"
                   disabled
                   value="{{ request('supplier_name') }}">
            <a href="{{ route('suppliers.select', ['redirect' => 'requests.create']) }}"
               class="btn btn-primary ms-2">
                Select Supplier
            </a>
        </div>
    </div>

    <div class="mb-3">
        <label>Description</label>
        <input name="description" class="form-control">
    </div>

    <button class="btn btn-success">Save</button>
</form>

@endsection