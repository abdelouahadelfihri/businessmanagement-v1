@extends('layouts.app')

@section('title', 'Edit ' . $warehouse->name)

@section('content')
    <div class="container mt-4">
        <h1 class="mb-4">Edit Warehouse {{ $warehouse->code }}</h1>

        <form method="POST" action="{{ route('masterdata.warehouses.update', $warehouse) }}">
            @csrf
            @method('PUT')
            @include('masterdata.warehouses._form')
        </form>
    </div>
@endsection
