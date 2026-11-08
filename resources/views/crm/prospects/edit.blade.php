@extends('layouts.app')

@section('title', 'Edit ' . $prospect->name)

@section('content')
    <div class="container mt-4">
        <h1 class="mb-4">Edit Prospect {{ $prospect->code }}</h1>

        <form method="POST" action="{{ route('crm.prospects.update', $prospect) }}">
            @csrf
            @method('PUT')
            @include('crm.prospects._form')
        </form>
    </div>
@endsection
