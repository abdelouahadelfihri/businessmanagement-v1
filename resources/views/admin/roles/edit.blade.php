@extends('layouts.app')

@section('title', 'Edit role ' . $role->name)

@section('content')
    <div class="container mt-4">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
            <h1 class="mb-0">Edit role</h1>
            <span class="badge bg-light text-dark border fs-6"><i class="bi bi-people"></i> {{ $role->users_count }} user(s)</span>
        </div>
        @include('admin._nav')

        <form method="POST" action="{{ route('admin.roles.update', $role) }}" novalidate>
            @csrf
            @method('PUT')
            @include('admin.roles._form')

            <div class="d-flex gap-2 mb-4">
                <button class="btn btn-primary"><i class="bi bi-check2"></i> Save role</button>
                <a href="{{ route('admin.roles.index') }}" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>
@endsection
