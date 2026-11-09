@extends('layouts.app')

@section('title', 'My profile')

@section('content')
    <div class="container mt-4" style="max-width: 900px;">
        <div class="d-flex align-items-center gap-3 mb-4">
            <span class="contact-avatar contact-avatar-lg">{{ $user->initials() }}</span>
            <div>
                <h1 class="h3 mb-0">{{ $user->name }}</h1>
                <span class="text-muted">{{ $user->role?->name ?? 'No role assigned' }}</span>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <div class="row g-3">
            <div class="col-md-6">
                <form method="POST" action="{{ route('profile.update') }}" class="card shadow-sm h-100" novalidate>
                    @csrf
                    @method('PUT')
                    <div class="card-header bg-white fw-semibold"><i class="bi bi-person"></i> Details</div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="name" class="form-label">Full name</label>
                            <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}"
                                class="form-control @error('name') is-invalid @enderror" required>
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}"
                                class="form-control @error('email') is-invalid @enderror" required>
                            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label for="phone" class="form-label">Phone</label>
                            <input type="text" name="phone" id="phone" value="{{ old('phone', $user->phone) }}"
                                class="form-control @error('phone') is-invalid @enderror">
                        </div>
                    </div>
                    <div class="card-footer bg-white">
                        <button class="btn btn-primary"><i class="bi bi-check2"></i> Save details</button>
                    </div>
                </form>
            </div>

            <div class="col-md-6">
                <form method="POST" action="{{ route('profile.password') }}" class="card shadow-sm h-100" novalidate>
                    @csrf
                    @method('PUT')
                    <div class="card-header bg-white fw-semibold"><i class="bi bi-key"></i> Change password</div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="current_password" class="form-label">Current password</label>
                            <input type="password" name="current_password" id="current_password" autocomplete="current-password"
                                class="form-control @error('current_password') is-invalid @enderror" required>
                            @error('current_password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">New password</label>
                            <input type="password" name="password" id="password" autocomplete="new-password"
                                class="form-control @error('password') is-invalid @enderror" required>
                            @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            <div class="form-text">At least 8 characters, letters and numbers.</div>
                        </div>
                        <div class="mb-3">
                            <label for="password_confirmation" class="form-label">Confirm new password</label>
                            <input type="password" name="password_confirmation" id="password_confirmation" autocomplete="new-password"
                                class="form-control" required>
                        </div>
                    </div>
                    <div class="card-footer bg-white">
                        <button class="btn btn-primary"><i class="bi bi-shield-lock"></i> Change password</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
