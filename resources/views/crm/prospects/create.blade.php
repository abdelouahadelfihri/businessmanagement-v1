@extends('layouts.app')

@section('title', 'New Prospect')

@section('content')
    <div class="container mt-4">
        <h1 class="mb-4">New Prospect</h1>

        <form method="POST" action="{{ route('crm.prospects.store') }}">
            @csrf
            @include('crm.prospects._form')
        </form>
    </div>
@endsection
