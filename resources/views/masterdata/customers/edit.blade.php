@extends('layouts.app')

@section('title', 'Edit ' . $customer->name)

@section('content')
    <div class="container mt-4">
        <h1 class="mb-4">Edit Customer {{ $customer->code }}</h1>

        <form method="POST" action="{{ route('masterdata.customers.update', $customer) }}">
            @csrf
            @method('PUT')
            @include('masterdata.customers._form')
        </form>
    </div>
@endsection
