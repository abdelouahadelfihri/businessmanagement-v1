@extends('layouts.app')

@section('title', 'New Product')

@section('content')
    <div class="container mt-4">
        <h1 class="mb-4">New Product</h1>

        <form method="POST" action="{{ route('masterdata.products.store') }}">
            @csrf
            @include('masterdata.products._form')
        </form>
    </div>
@endsection
