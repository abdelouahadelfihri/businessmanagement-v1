@extends(auth()->check() ? 'layouts.app' : 'layouts.guest')

@section('title', 'Access denied')

@section('content')
    <div class="container mt-5 text-center" style="max-width: 560px;">
        <div class="display-1 text-danger mb-3"><i class="bi bi-shield-lock"></i></div>
        <h1 class="h3">Access denied</h1>
        <p class="text-muted">
            {{ $exception->getMessage() ?: 'You do not have permission to perform this action.' }}
            If you think you need it, ask an administrator to update your role.
        </p>
        <div class="d-flex justify-content-center gap-2 mt-4">
            <a href="{{ url()->previous() !== url()->current() ? url()->previous() : route('dashboard') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Go back
            </a>
            <a href="{{ route('dashboard') }}" class="btn btn-primary"><i class="bi bi-speedometer2"></i> Dashboard</a>
        </div>
    </div>
@endsection
