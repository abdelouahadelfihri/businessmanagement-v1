@extends('layouts.app')

@section('title', 'Edit ' . $user->name)

@section('content')
    <div class="container mt-4">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
            <h1 class="mb-0">Edit user</h1>
            <div class="d-flex gap-2">
                @canroute('admin.users.reset_link')
                    @if($user->is_active)
                        <form action="{{ route('admin.users.reset_link', $user) }}" method="POST">
                            @csrf
                            <button class="btn btn-outline-secondary"><i class="bi bi-envelope-arrow-up"></i> Send reset link</button>
                        </form>
                    @endif
                @endcanroute
            </div>
        </div>
        @include('admin._nav')

        <form method="POST" action="{{ route('admin.users.update', $user) }}" novalidate>
            @csrf
            @method('PUT')
            @include('admin.users._form')

            <div class="d-flex gap-2 mb-4">
                <button class="btn btn-primary"><i class="bi bi-check2"></i> Save changes</button>
                <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>
@endsection
