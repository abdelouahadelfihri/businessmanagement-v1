@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h1 class="mb-4">Create Purchase Request</h1>

    <div class="card shadow-sm">
        <div class="card-body">

            <form action="{{ route('purchase-requests.store') }}" method="POST">
                @csrf

                <div class="mb-3">
                    <label class="form-label">Title</label>
                    <input 
                        type="text" 
                        name="title" 
                        class="form-control @error('title') is-invalid @enderror" 
                        required
                        value="{{ old('title') }}"
                    >
                    @error('title')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Hidden fields for selection flow --}}
                <input type="hidden" name="select_for" value="{{ request('select_for') }}">
                <input type="hidden" name="return_url" value="{{ request('return_url') }}">

                <button type="submit" class="btn btn-primary">
                    Save
                </button>

                @if(request('return_url'))
                <a href="{{ request('return_url') }}" class="btn btn-secondary ms-2">
                    Cancel & Return
                </a>
                @endif

            </form>

        </div>
    </div>
</div>
@endsection