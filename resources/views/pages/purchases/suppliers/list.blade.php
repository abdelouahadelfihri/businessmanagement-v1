@extends('layouts.app')

@section('title', 'Suppliers')

@section('content')
<h1>Suppliers</h1>

<a href="{{ route('suppliers.add') }}" class="btn btn-primary mb-3">Add Supplier</a>

<table class="table" id="suppliersTable">
    <thead>
        <tr>
            <th>ID</th><th>Name</th><th>Email</th><th>Phone</th><th></th>
        </tr>
    </thead>
    <tbody></tbody>
</table>

<script>
async function loadSuppliers() {
    const res = await fetch('/api/suppliers');
    const data = await res.json();

    const tbody = document.querySelector('#suppliersTable tbody');
    tbody.innerHTML = '';

    if (data.length === 0) {
        tbody.innerHTML = `
            <tr><td colspan="5" class="text-center">
                No suppliers found.  
                <a href="{{ route('suppliers.add') }}" class="btn btn-sm btn-success">Add Supplier</a>
            </td></tr>
        `;
        return;
    }

    data.forEach(s => {
        tbody.innerHTML += `
            <tr>
                <td>${s.id}</td>
                <td>${s.name}</td>
                <td>${s.email}</td>
                <td>${s.phone}</td>
                <td>
                    <a href="{{ url('suppliers/edit') }}/${s.id}" class="btn btn-sm btn-warning">Edit</a>

                    <button class="btn btn-sm btn-info" onclick="selectSupplier(${s.id}, '{{ $returnTo ?? '' }}')">
                       Select
                    </button>
                </td>
            </tr>
        `;
    });
}

//
// When called from request form:
// /suppliers?returnTo=requestAdd
//
function selectSupplier(id, returnTo) {
    if (!returnTo) return alert("No return");
    window.location.href = `/${returnTo}?supplier_id=${id}`;
}

loadSuppliers();
</script>
@endsection