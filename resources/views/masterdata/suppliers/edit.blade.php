@extends('layouts.app')

@section('title', 'Edit ' . $supplier->name)

@section('content')
    <div class="container mt-4">
        <h1 class="mb-4">Edit Supplier {{ $supplier->code }}</h1>

        <form method="POST" action="{{ route('masterdata.suppliers.update', $supplier) }}">
            @csrf
            @method('PUT')
            @include('masterdata.suppliers._form')
        </form>
    </div>
@endsection
