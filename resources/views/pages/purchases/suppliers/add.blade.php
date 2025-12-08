@extends('layouts.app')

@section('title', 'Add Supplier')

@section('content')
<h1>Add Supplier</h1>

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

    let data = {
        name: e.target.name.value,
        email: e.target.email.value,
        phone: e.target.phone.value,
        address: e.target.address.value,
    };

    await fetch('/api/suppliers', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
    });

    // Return back to previous form if set
    const params = new URLSearchParams(window.location.search);
    const returnTo = params.get("returnTo");

    if (returnTo) window.location.href = `/${returnTo}`;
    else window.location.href = "{{ route('suppliers.list') }}";
});
</script>
@endsection