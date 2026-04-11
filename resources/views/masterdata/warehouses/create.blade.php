@extends('layouts.app')

@section('content')
    <h3>Create Warehouse</h3>

    <form method="POST" action="{{ route('warehouses.store') }}">
        @csrf

        <!-- Warehouse Name -->
        <div class="mb-3">
            <label class="form-label">Warehouse Name</label>
            <input
                type="text"
                name="name"
                class="form-control"
                value="{{ old('name') }}"
                required
            >
        </div>

        <!-- Location Owner -->
        <div class="mb-3">
            <label class="form-label">Location Owner</label>
            <div class="input-group">
                <input type="hidden" name="location_owner_id" id="location_owner_id">
                <input type="text" id="location_owner_name" class="form-control" readonly>
                <button
                    type="button"
                    class="btn btn-secondary"
                    data-bs-toggle="modal"
                    data-bs-target="#locationOwnerModal"
                >
                    Select Location Owner
                </button>
            </div>
        </div>

        <!-- Is Refrigerated -->
        <div class="mb-3 form-check">
            <input
                type="checkbox"
                name="is_refrigerated"
                value="1"
                class="form-check-input"
                id="is_refrigerated"
                {{ old('is_refrigerated') ? 'checked' : '' }}
            >
            <label class="form-check-label" for="is_refrigerated">
                Refrigerated Warehouse
            </label>
        </div>

        <button class="btn btn-primary">Save</button>
    </form>

    @include('modals.location-picker')
@endsection