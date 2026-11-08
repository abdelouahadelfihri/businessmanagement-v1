@php
    $c = $category;
    $val = fn($field, $default = null) => old($field, $c->$field ?? $default);
    $err = fn($field) => $errors->has($field) ? 'is-invalid' : '';
    $icon = $val('icon', 'bi-tag');
    $color = $val('color', '#0d6efd');
@endphp

@if($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="row g-3 mb-3">
    {{-- General information --}}
    <div class="col-lg-8">
        <div class="card shadow-sm h-100">
            <div class="card-header fw-semibold"><i class="bi bi-tags"></i> General information</div>
            <div class="card-body row g-3">
                <div class="col-md-8">
                    <label class="form-label">Name *</label>
                    <input type="text" name="name" id="catName" maxlength="255" class="form-control {{ $err('name') }}" required
                        placeholder="e.g. Clothes" value="{{ $val('name') }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Code</label>
                    <input type="text" name="code" maxlength="20" class="form-control {{ $err('code') }}"
                        placeholder="Auto (CAT-001)" value="{{ $val('code') }}">
                </div>

                <div class="col-md-8">
                    <label class="form-label">Parent category</label>
                    <select name="parent_id" class="form-select {{ $err('parent_id') }}">
                        <option value="">— None (top level) —</option>
                        @foreach($parents as $parent)
                            <option value="{{ $parent->id }}" @selected($val('parent_id') == $parent->id)>{{ $parent->full_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Display order</label>
                    <input type="number" name="sort_order" min="0" max="9999" step="1"
                        class="form-control {{ $err('sort_order') }}" value="{{ $val('sort_order', 0) }}">
                </div>

                <div class="col-12">
                    <label class="form-label">Description</label>
                    <textarea name="description" rows="3" class="form-control {{ $err('description') }}">{{ $val('description') }}</textarea>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Default VAT rate</label>
                    <select name="default_tax_rate" class="form-select {{ $err('default_tax_rate') }}">
                        <option value="">— Not set —</option>
                        @foreach(\App\Models\MasterData\Product::TAX_RATES as $rate)
                            <option value="{{ $rate }}" @selected($val('default_tax_rate') !== null && (float) $val('default_tax_rate') == $rate)>{{ $rate }} %</option>
                        @endforeach
                    </select>
                    <div class="form-text">Pre-fills the VAT rate of products created in this category.</div>
                </div>

                <div class="col-md-6 d-flex align-items-center">
                    <div class="form-check form-switch">
                        <input type="hidden" name="is_active" value="0">
                        <input class="form-check-input" type="checkbox" role="switch" id="is_active" name="is_active" value="1"
                            @checked($val('is_active', true))>
                        <label class="form-check-label" for="is_active">Active category</label>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Appearance --}}
    <div class="col-lg-4">
        <div class="card shadow-sm h-100">
            <div class="card-header fw-semibold"><i class="bi bi-palette"></i> Appearance</div>
            <div class="card-body">
                <div class="text-center mb-3">
                    <span id="catPreview" class="badge fs-6 px-3 py-2" style="background: {{ $color }};">
                        <i class="bi {{ $icon }}"></i> <span>{{ $val('name') ?: 'Preview' }}</span>
                    </span>
                </div>

                <label class="form-label">Colour *</label>
                <div class="d-flex flex-wrap gap-2 mb-2">
                    @foreach(['#0d6efd', '#6f42c1', '#d63384', '#dc3545', '#fd7e14', '#ffc107', '#198754', '#20c997', '#0dcaf0', '#6c757d', '#343a40'] as $swatch)
                        <button type="button" class="btn p-0 border rounded-circle color-swatch" data-color="{{ $swatch }}"
                            style="width: 28px; height: 28px; background: {{ $swatch }};" title="{{ $swatch }}"></button>
                    @endforeach
                </div>
                <input type="color" name="color" id="catColor" class="form-control form-control-color w-100 mb-3 {{ $err('color') }}"
                    value="{{ $color }}">

                <label class="form-label">Icon *</label>
                <input type="hidden" name="icon" id="catIcon" value="{{ $icon }}">
                <div class="icon-grid border rounded p-2">
                    @foreach(\App\Models\MasterData\Category::ICONS as $ic)
                        <button type="button" class="btn btn-sm icon-option {{ $ic === $icon ? 'btn-primary' : 'btn-light' }}"
                            data-icon="{{ $ic }}" title="{{ $ic }}"><i class="bi {{ $ic }}"></i></button>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

<div class="d-flex flex-column flex-sm-row justify-content-end gap-2 mb-5">
    <a href="{{ $c->exists ? route('masterdata.categories.show', $c) : route('masterdata.categories.index') }}"
        class="btn btn-outline-secondary">Cancel</a>
    <button type="submit" class="btn btn-primary">
        <i class="bi bi-save"></i> {{ $c->exists ? 'Update category' : 'Save category' }}
    </button>
</div>

@push('styles')
    <style>
        .icon-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(38px, 1fr)); gap: 4px; max-height: 190px; overflow-y: auto; }
        .icon-grid .btn { font-size: 1.05rem; }
    </style>
@endpush

@push('scripts')
    <script>
        (function () {
            const preview = document.getElementById('catPreview');
            const color = document.getElementById('catColor');
            const icon = document.getElementById('catIcon');

            color.addEventListener('input', () => preview.style.background = color.value);
            document.getElementById('catName').addEventListener('input', e =>
                preview.querySelector('span').textContent = e.target.value || 'Preview');

            document.querySelectorAll('.color-swatch').forEach(btn => btn.addEventListener('click', () => {
                color.value = btn.dataset.color;
                preview.style.background = btn.dataset.color;
            }));

            document.querySelectorAll('.icon-option').forEach(btn => btn.addEventListener('click', () => {
                document.querySelectorAll('.icon-option').forEach(b => b.classList.replace('btn-primary', 'btn-light'));
                btn.classList.replace('btn-light', 'btn-primary');
                icon.value = btn.dataset.icon;
                preview.querySelector('i').className = 'bi ' + btn.dataset.icon;
            }));
        })();
    </script>
@endpush
