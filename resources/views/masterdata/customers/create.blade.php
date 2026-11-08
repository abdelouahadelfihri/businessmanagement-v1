@extends('layouts.app')

@section('title', 'New Customer')

@section('content')
    <div class="container mt-4">
        <h1 class="mb-4">New Customer</h1>

        <form method="POST" action="{{ route('masterdata.customers.store') }}">
            @csrf
            @include('masterdata.customers._form')
        </form>
    </div>
@endsection
