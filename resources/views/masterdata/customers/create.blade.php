@extends('layouts.app')

@section('content')
    <div class="container mt-4">

        <h1 class="mb-4">Create Customer</h1>

        <div class="card shadow-sm">
            <div class="card-body">
                <form action="{{ route('customers.store') }}" method="POST">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label">Name</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Email (optional)</label>
                        <input type="email" name="email" class="form-control">
                    </div>

                    <input type="hidden" name="select_for" value="{{ request('select_for') }}">
                    <input type="hidden" name="return_url" value="{{ request('return_url') }}">

                    <button class="btn btn-primary">Save</button>

                    @if(request('return_url'))
                        <a href="{{ request('return_url') }}" class="btn btn-secondary ms-2">Cancel & Return</a>
                    @endif

                </form>
            </div>
        </div>

    </div>
@endsection