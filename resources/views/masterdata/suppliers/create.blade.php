@extends('layouts.app')

@section('title', 'New Supplier')

@section('content')
    <div class="container mt-4">
        <h1 class="mb-4">New Supplier</h1>

        <form method="POST" action="{{ route('masterdata.suppliers.store') }}">
            @csrf
            @include('masterdata.suppliers._form')
        </form>
    </div>
@endsection
