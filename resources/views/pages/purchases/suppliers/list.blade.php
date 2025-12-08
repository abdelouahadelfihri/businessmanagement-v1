@extends('layouts.app')

@section('title', 'Purchase Orders List')

@section('content')
<h1>Purchase Orders</h1>
<p>This page will show the list of purchase orders.</p>

<!-- Example Table -->
<table class="table table-bordered">
    <thead>
        <tr>
            <th>ID</th>
            <th>Supplier</th>
            <th>Request</th>
            <th>Date</th>
            <th>Status</th>
            <th>Total</th>
        </tr>
    </thead>
    <tbody>
        <!-- Loop from API or DB here -->
    </tbody>
</table>
@endsection