@extends('layouts.app')

@section('title', 'New user')

@section('content')
    <div class="container mt-4">
        <h1 class="mb-3">New user</h1>
        @include('admin._nav')

        <form method="POST" action="{{ route('admin.users.store') }}" novalidate>
            @csrf
            @include('admin.users._form')

            <div class="d-flex gap-2 mb-4">
                <button class="btn btn-primary"><i class="bi bi-check2"></i> Create user</button>
                <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>
@endsection
