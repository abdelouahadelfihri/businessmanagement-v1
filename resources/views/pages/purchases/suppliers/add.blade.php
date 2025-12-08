@extends('layouts.app')

@section('title', 'Add Supplier')

@section('content')
<h1>Add Supplier</h1>

@php
    $returnTo = request()->get('returnTo'); // where we return after saving
@endphp

<form id="supplierForm">
    @csrf

    <div class="mb-3">
        <label>Name</label>
        <input class="form-control" name="name" required>
    </div>

    <div class="mb-3">
        <label>Email</label>
        <input class="form-control" name="email">
    </div>

    <div class="mb-3">
        <label>Phone</label>
        <input class="form-control" name="phone">
    </div>

    <div class="mb-3">
        <label>Address</label>
        <textarea class="form-control" name="address"></textarea>
    </div>

    <button class="btn btn-primary">Save</button>
</form>

<script>
document.querySelector('#supplierForm').addEventListener('submit', async (e) => {
    e.preventDefault();

    const body = {
        name: e.target.name.value,
        email: e.target.email.value,
        phone: e.target.phone.value,
        address: e.target.address.value,
    };

    const res = await fetch('/api/suppliers', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify(body)
    });

    const created = await res.json();

    const returnTo = "{{ $returnTo }}";

    if (returnTo) {
        // Return to the request add/edit page WITH supplier_id
        window.location.href = "/" + returnTo + "?supplier_id=" + created.id;
    } else {
        window.location.href = "{{ route('suppliers.list') }}";
    }
});
</script>
@endsection