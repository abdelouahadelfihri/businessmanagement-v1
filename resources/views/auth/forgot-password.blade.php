@extends('layouts.guest')

@section('title', 'Forgot password')

@section('content')
    <h1 class="h4 text-center mb-1">Forgot your password?</h1>
    <p class="text-muted text-center mb-4">Enter your email and we will send you a link to choose a new one.</p>

    @if(session('status'))
        <div class="alert alert-success py-2">{{ session('status') }}</div>
    @endif

    <form method="POST" action="{{ route('password.email') }}" novalidate>
        @csrf

        <div class="mb-4">
            <label for="email" class="form-label">Email</label>
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                <input type="email" name="email" id="email" value="{{ old('email') }}"
                    class="form-control @error('email') is-invalid @enderror" autocomplete="email" required autofocus>
                @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>

        <button type="submit" class="btn btn-primary w-100 mb-3">
            <i class="bi bi-send"></i> Email me a reset link
        </button>

        <div class="text-center">
            <a href="{{ route('login') }}" class="small"><i class="bi bi-arrow-left"></i> Back to sign in</a>
        </div>
    </form>
@endsection
