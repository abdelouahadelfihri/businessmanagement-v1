@extends('layouts.app')

@section('title', 'Edit ' . $location->name)

@section('content')
    <div class="container mt-4">
        <h1 class="mb-4">Edit Location {{ $location->code }}</h1>

        <form method="POST" action="{{ route('masterdata.locations.update', $location) }}">
            @csrf
            @method('PUT')
            @include('masterdata.locations._form')
        </form>
    </div>
@endsection
