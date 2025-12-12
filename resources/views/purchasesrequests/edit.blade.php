@extends('layouts.app')

@section('content')
<div class="container mt-4">

    <h1 class="mb-4">Edit Purchase Request</h1>

    <div class="card shadow-sm">
        <div class="card-body">

            <form action="{{ route('purchase-requests.update', $request) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label class="form-label">Title</label>
                    <input type="text" name="title" 
                           class="form-control" 
                           value="{{ $request->title }}" required>
                </div>

                <button class="btn btn-primary">Update</button>
                <a href="{{ route('purchase-requests.index') }}" class="btn btn-secondary ms-2">Back</a>

            </form>

        </div>
    </div>

</div>
@endsection