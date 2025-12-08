@extends('layouts.app')

@section('title', 'Suppliers')

@section('content')
<h1>Suppliers</h1>

@php
    // We capture where the user should return after selecting a supplier
    $returnTo = request()->get('returnTo');
@endphp

@if($returnTo)
    <p class="text-muted">You are selecting a supplier for: <strong>{{ $returnTo }}</strong></p>
@endif

<a href="{{ route('suppliers.add', ['returnTo' => $returnTo]) }}" class="btn btn-primary mb-3">
    Add Supplier
</a>

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
            <tr><td colspan="5" class="text-center text-muted">
                No suppliers found.<br>
                <a href="{{ route('suppliers.add') }}?returnTo={{ $returnTo }}" 
                   class="btn btn-success mt-2">
                   Add Supplier
                </a>
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
                    @if($returnTo)
                        <a href="/${'{{ $returnTo }}'}?supplier_id=${s.id}" 
                           class="btn btn-sm btn-info">
                           Select
                        </a>
                    @else
                        <a href="/suppliers/edit/${s.id}" class="btn btn-sm btn-warning">Edit</a>
                    @endif
                </td>
            </tr>
        `;
    });
}

loadSuppliers();
</script>
@endsection