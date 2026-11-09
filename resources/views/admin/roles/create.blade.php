@extends('layouts.app')

@section('title', 'New role')

@section('content')
    <div class="container mt-4">
        <h1 class="mb-3">New role</h1>
        @include('admin._nav')

        <form method="POST" action="{{ route('admin.roles.store') }}" novalidate>
            @csrf
            @include('admin.roles._form')

            <div class="d-flex gap-2 mb-4">
                <button class="btn btn-primary"><i class="bi bi-check2"></i> Create role</button>
                <a href="{{ route('admin.roles.index') }}" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>
@endsection
