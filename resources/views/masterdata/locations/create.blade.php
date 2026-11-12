@extends('layouts.app')

@section('title', 'New Location')

@section('content')
    <div class="container mt-4">
        <h1 class="mb-4">New Location</h1>

        <form method="POST" action="{{ route('masterdata.locations.store') }}">
            @csrf
            @include('masterdata.locations._form')
        </form>
    </div>
@endsection
