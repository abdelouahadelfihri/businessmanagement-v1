@extends('layouts.app')

@section('content')
<div class="container">
    <h3>Create Product</h3>

    <form method="POST" action="{{ route('products.store') }}">
        @csrf

        {{-- NAME --}}
        <div class="mb-3">
            <label class="form-label">Product Name</label>
            <input type="text" name="name" class="form-control" required>
        </div>

        {{-- CODE --}}
        <div class="mb-3">
            <label class="form-label">Product Code</label>
            <input type="text" name="code" class="form-control">
        </div>

        {{-- BARCODE --}}
        <div class="mb-3">
            <label class="form-label">Barcode</label>
            <div class="input-group">
                <input type="text" name="bar_code" id="barcode" class="form-control">
                <button type="button" class="btn btn-outline-secondary" onclick="startBarcodeScan()">
                    📷 Scan
                </button>
            </div>
        </div>

        {{-- CATEGORY --}}
        <div class="mb-3">
            <label class="form-label">Category</label>
            <div class="input-group">
                <input type="text" name="category" id="category" class="form-control" readonly>
                <button type="button" class="btn btn-outline-primary" onclick="openCategoryModal()">
                    Pick
                </button>
            </div>
        </div>

        {{-- UNIT --}}
        <div class="mb-3">
            <label class="form-label">Unit</label>
            <div class="input-group">
                <input type="text" name="unit" id="unit" class="form-control" readonly>
                <button type="button" class="btn btn-outline-primary" onclick="openUnitModal()">
                    Pick
                </button>
            </div>
        </div>

        {{-- REORDER LEVEL --}}
        <div class="mb-3">
            <label class="form-label">Reorder Level</label>
            <input type="number" name="reorder_level" class="form-control">
        </div>

        {{-- ACTIVE --}}
        <div class="mb-3 form-check">
            <input type="checkbox" name="is_active" value="1" class="form-check-input" checked>
            <label class="form-check-label">Active</label>
        </div>

        <button class="btn btn-success">Save Product</button>
    </form>
</div>

@include('modals.category-picker')
@include('modals.unit-picker')
@include('products.partials.barcode-script')
@endsection
