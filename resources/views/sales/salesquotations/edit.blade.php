@extends('layouts.app')

@section('content')
    <div class="container mt-4">
        <h1 class="mb-4">Edit Sales Quotation {{ $saleQuotation->quote_number }}</h1>

        <form method="POST" action="{{ route('sales.salesquotations.update', $saleQuotation) }}">
            @csrf
            @method('PUT')
            @include('sales.salesquotations._form')
        </form>
    </div>
@endsection

@push('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
@endpush
