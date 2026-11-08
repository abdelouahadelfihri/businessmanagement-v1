@extends('layouts.app')

@section('title', 'Edit ' . $product->name)

@section('content')
    <div class="container mt-4">
        <h1 class="mb-4">Edit Product {{ $product->code }}</h1>

        <form method="POST" action="{{ route('masterdata.products.update', $product) }}">
            @csrf
            @method('PUT')
            @include('masterdata.products._form')
        </form>
    </div>
@endsection
