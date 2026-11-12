@extends('layouts.app')

@section('title', 'New Warehouse')

@section('content')
    <div class="container mt-4">
        <h1 class="mb-4">New Warehouse</h1>

        <form method="POST" action="{{ route('masterdata.warehouses.store') }}">
            @csrf
            @include('masterdata.warehouses._form')
        </form>
    </div>
@endsection
