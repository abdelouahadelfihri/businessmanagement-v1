@extends('layouts.app')

@section('content')
<div class="container mt-4">

    <h2>Add Supplier</h2>

    <div class="card mt-3">
        <div class="card-body">

            <form action="{{ route('suppliers.store') }}" method="POST">
                @csrf

                @if(isset($returnTo))
                    <input type="hidden" name="return_to" value="{{ $returnTo }}">
                @endif

                <div class="mb-3">
                    <label class="form-label">Name</label>
                    <input type="text" name="name" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control">
                </div>

                <div class="mb-3">
                    <label class="form-label">Phone</label>
                    <input type="text" name="phone" class="form-control">
                </div>

                <div class="mb-3">
                    <label class="form-label">Address</label>
                    <textarea name="address" class="form-control"></textarea>
                </div>

                <button class="btn btn-success">Save</button>
            </form>

        </div>
    </div>

</div>
@endsection