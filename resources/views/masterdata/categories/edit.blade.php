@extends('layouts.app')

@section('title', 'Edit ' . $category->name)

@section('content')
    <div class="container mt-4">
        <h1 class="mb-4">Edit Category {{ $category->code }}</h1>

        <form method="POST" action="{{ route('masterdata.categories.update', $category) }}">
            @csrf
            @method('PUT')
            @include('masterdata.categories._form')
        </form>
    </div>
@endsection
