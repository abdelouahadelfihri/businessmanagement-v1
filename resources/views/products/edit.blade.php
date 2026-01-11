@extends('layouts.app')

@section('content')
    <div class="container">
        <h3>Edit Product</h3>

        <form method="POST" action="{{ route('products.update', $product->id) }}">
            @csrf
            @method('PUT')

            <div class="mb-3">
                <label>Product Name</label>
                <input type="text" name="name" value="{{ $product->name }}" class="form-control">
            </div>

            <div class="mb-3">
                <label>Product Code</label>
                <input type="text" name="code" value="{{ $product->code }}" class="form-control">
            </div>

            <div class="mb-3">
                <label>Barcode</label>
                <div class="input-group">
                    <input type="text" name="bar_code" id="barcode" value="{{ $product->bar_code }}" class="form-control">
                    <button type="button" class="btn btn-outline-secondary" onclick="startBarcodeScan()">📷 Scan</button>
                </div>
            </div>

            {{-- CATEGORY --}}

            <div class="mb-3">
                <label class="form-label">Category</label>
                <div class="input-group">
                    <input type="hidden" name="category_id" id="category_id" value="{{ $product->category_id }}">
                    <input type="text" name="category_name" id="category_name" class="form-control" readonly
                        value="{{ $product->category?->name }}">
                    <button type="button" class="btn btn-outline-primary" onclick="openCategoryModal()">
                        Pick
                    </button>
                </div>
            </div>

            {{-- UNIT --}}

            <div class="mb-3">
                <label class="form-label">Unit</label>
                <div class="input-group">
                    <input type="hidden" name="unit_id" id="unit_id" value="{{ $product->unit_id }}">
                    <input type="text" name="unit_name" id="unit_name" class="form-control" readonly
                        value="{{ $product->unit?->name }}">
                    <button type="button" class="btn btn-outline-primary" onclick="openUnitModal()">
                        Pick
                    </button>
                </div>
            </div>

            <div class="mb-3">
                <label>Reorder Level</label>
                <input type="number" name="reorder_level" value="{{ $product->reorder_level }}" class="form-control">
            </div>

            <div class="form-check mb-3">
                <input type="checkbox" name="is_active" value="1" class="form-check-input" {{ $product->is_active ? 'checked' : '' }}>
                <label class="form-check-label">Active</label>
            </div>

            <button class="btn btn-primary">Update Product</button>
        </form>
    </div>

    @include('modals.category-picker')
    @include('modals.unit-picker')
    @include('products.partials.barcode-script')
@endsection

@push('scripts')
    <script>
        // 🔹 Open Category Modal
        function openCategoryModal() {
            let modalEl = document.getElementById('categoryModal');
            let modal = new bootstrap.Modal(modalEl);
            modal.show();
        }

        // 🔹 Open Unit Modal
        function openUnitModal() {
            let modalEl = document.getElementById('unitModal');
            let modal = new bootstrap.Modal(modalEl);
            modal.show();
        }
    </script>
@endpush