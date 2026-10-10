@extends('layouts.app')

@section('content')
    <div class="container mt-4">
        <h1 class="mb-4">New Purchase Invoice</h1>

        <form method="POST" action="{{ route('purchases.purchasesinvoices.store') }}">
            @csrf
            @include('purchases.purchasesinvoices._form')
        </form>
    </div>
@endsection

@push('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
@endpush
