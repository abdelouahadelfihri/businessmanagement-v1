@extends('layouts.app')

@section('title', 'Edit ' . $unit->name)

@section('content')
    <div class="container mt-4">
        <h1 class="mb-4">Edit Unit {{ $unit->label }}</h1>

        <form method="POST" action="{{ route('masterdata.units.update', $unit) }}">
            @csrf
            @method('PUT')
            @include('masterdata.units._form')
        </form>
    </div>
@endsection
