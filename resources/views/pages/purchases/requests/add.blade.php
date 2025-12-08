@extends('layouts.app')

@section('title', 'Add Purchase Request')

@section('content')
<h1>Add Purchase Request</h1>

@php
$selectedSupplier = request()->get('supplier_id');
@endphp

<div class="mb-3">
    <label>Supplier</label><br>

    @if($selectedSupplier)
        <input type="hidden" name="supplier_id" id="supplierIdField" value="{{ $selectedSupplier }}">

        <div class="p-2 bg-light border rounded">
            Selected Supplier ID: <strong>{{ $selectedSupplier }}</strong>
        </div>

        <a href="{{ route('suppliers.list') }}?returnTo=purchases/requests/add"
           class="btn btn-sm btn-secondary mt-2">
            Change Supplier
        </a>
    @else
        <a href="{{ route('suppliers.list') }}?returnTo=purchases/requests/add"
           class="btn btn-secondary">
            Select Supplier
        </a>
    @endif
</div>

<form id="requestForm">
    @csrf
    ...
</form>
@endsection