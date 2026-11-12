@extends('layouts.app')

@section('title', 'New Unit')

@section('content')
    <div class="container mt-4">
        <h1 class="mb-4">New Unit</h1>

        <form method="POST" action="{{ route('masterdata.units.store') }}">
            @csrf
            @include('masterdata.units._form')
        </form>
    </div>
@endsection
