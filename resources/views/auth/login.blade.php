@extends('layouts.guest')

@section('title', 'Sign in')

@section('content')
    <h1 class="h4 text-center mb-1">Welcome back</h1>
    <p class="text-muted text-center mb-4">Administrators and staff sign in here with their own account.</p>

    @if(session('status'))
        <div class="alert alert-success py-2">{{ session('status') }}</div>
    @endif

    <form method="POST" action="{{ route('login.store') }}" novalidate>
        @csrf

        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                <input type="email" name="email" id="email" value="{{ old('email') }}"
                    class="form-control @error('email') is-invalid @enderror" autocomplete="username" required autofocus>
                @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>

        <div class="mb-3">
            <div class="d-flex justify-content-between align-items-baseline">
                <label for="password" class="form-label">Password</label>
                <a href="{{ route('password.request') }}" class="small">Forgot your password?</a>
            </div>
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-lock"></i></span>
                <input type="password" name="password" id="password"
                    class="form-control @error('password') is-invalid @enderror" autocomplete="current-password" required>
                <button type="button" class="btn btn-outline-secondary" data-toggle-password="password" aria-label="Show password">
                    <i class="bi bi-eye"></i>
                </button>
                @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>

        <div class="form-check mb-4">
            <input class="form-check-input" type="checkbox" name="remember" id="remember" @checked(old('remember'))>
            <label class="form-check-label" for="remember">Keep me signed in</label>
        </div>

        <button type="submit" class="btn btn-primary w-100">
            <i class="bi bi-box-arrow-in-right"></i> Sign in
        </button>
    </form>

    <p class="small text-muted text-center mt-4 mb-0">
        <i class="bi bi-info-circle"></i> No account yet? Ask an administrator to create one for you.
    </p>
@endsection
