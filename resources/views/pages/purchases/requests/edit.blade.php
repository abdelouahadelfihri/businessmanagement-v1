@extends('layouts.app')

@section('content')

<h2>Edit Purchase Request</h2>

<form id="requestForm">
    <input type="hidden" name="supplier_id" id="supplier_id" value="{{ $request->supplier_id }}">

    <div class="mb-3">
        <label>Supplier:</label>
        <div class="d-flex">
            <input type="text"
                   id="supplier_name"
                   class="form-control"
                   disabled
                   value="{{ $request->supplier->name ?? '' }}">
            <a href="{{ route('suppliers.select', ['redirect' => 'requests.edit', 'id' => $request->id]) }}"
               class="btn btn-primary ms-2">
                Select Supplier
            </a>
        </div>
    </div>

    <div class="mb-3">
        <label>Description</label>
        <input name="description" value="{{ $request->description }}" class="form-control">
    </div>

    <button class="btn btn-success">Update</button>
</form>

@endsection