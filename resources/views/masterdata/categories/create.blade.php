@extends('layouts.app')

@section('title', 'New Category')

@section('content')
    <div class="container mt-4">
        <h1 class="mb-4">New Category</h1>

        <form method="POST" action="{{ route('masterdata.categories.store') }}">
            @csrf
            @include('masterdata.categories._form')
        </form>
    </div>
@endsection
