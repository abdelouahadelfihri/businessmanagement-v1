@extends('layouts.guest')

@section('title', 'Choose a new password')

@section('content')
    <h1 class="h4 text-center mb-1">Choose a new password</h1>
    <p class="text-muted text-center mb-4">At least 8 characters, with letters and numbers.</p>

    <form method="POST" action="{{ route('password.update') }}" novalidate>
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">

        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input type="email" name="email" id="email" value="{{ old('email', $email) }}"
                class="form-control @error('email') is-invalid @enderror" autocomplete="username" required>
            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="mb-3">
            <label for="password" class="form-label">New password</label>
            <div class="input-group">
                <input type="password" name="password" id="password"
                    class="form-control @error('password') is-invalid @enderror" autocomplete="new-password" required autofocus>
                <button type="button" class="btn btn-outline-secondary" data-toggle-password="password" aria-label="Show password">
                    <i class="bi bi-eye"></i>
                </button>
                @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>

        <div class="mb-4">
            <label for="password_confirmation" class="form-label">Confirm new password</label>
            <input type="password" name="password_confirmation" id="password_confirmation" class="form-control"
                autocomplete="new-password" required>
        </div>

        <button type="submit" class="btn btn-primary w-100 mb-3">
            <i class="bi bi-check2-circle"></i> Reset password
        </button>

        <div class="text-center">
            <a href="{{ route('login') }}" class="small"><i class="bi bi-arrow-left"></i> Back to sign in</a>
        </div>
    </form>
@endsection
