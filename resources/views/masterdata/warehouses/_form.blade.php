@php
    $w = $warehouse;
    $val = fn($field, $default = null) => old($field, $w->$field ?? $default);
    $err = fn($field) => $errors->has($field) ? 'is-invalid' : '';
    $W = \App\Models\MasterData\Warehouse::class;
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

{{-- General information --}}
<div class="card shadow-sm mb-3">
    <div class="card-header fw-semibold"><i class="bi bi-building"></i> General information</div>
    <div class="card-body row g-3">
        <div class="col-md-6">
            <label class="form-label">Name *</label>
            <input type="text" name="name" maxlength="255" required class="form-control {{ $err('name') }}"
                placeholder="e.g. Casablanca central warehouse" value="{{ $val('name') }}">
        </div>
        <div class="col-sm-6 col-md-3">
            <label class="form-label">Code</label>
            <input type="text" name="code" maxlength="20" class="form-control {{ $err('code') }}"
                placeholder="Auto (WH-001)" value="{{ $val('code') }}">
        </div>
        <div class="col-sm-6 col-md-3">
            <label class="form-label">Type *</label>
            <select name="type" class="form-select {{ $err('type') }}" required>
                @foreach($W::TYPES as $key => $meta)
                    <option value="{{ $key }}" @selected($val('type', 'main') === $key)>{{ $meta['label'] }}</option>
                @endforeach
            </select>
        </div>

        <div class="col-md-6">
            <label class="form-label">Location</label>
            <select name="location_id" class="form-select {{ $err('location_id') }}">
                <option value="">— None —</option>
                @foreach($locations as $location)
                    <option value="{{ $location->location_id }}" @selected($val('location_id') == $location->location_id)>
                        {{ $location->name }}{{ $location->address ? ' — ' . \Illuminate\Support\Str::limit($location->address, 40) : '' }}
                    </option>
                @endforeach
            </select>
            @canroute('masterdata.locations.create')
                <div class="form-text">Missing? <a href="{{ route('masterdata.locations.create') }}" target="_blank">Add a location</a>, then reload this page.</div>
            @endcanroute
        </div>
        <div class="col-md-6 d-flex flex-column justify-content-end gap-1">
            <div class="form-check form-switch">
                <input type="hidden" name="is_default" value="0">
                <input class="form-check-input" type="checkbox" role="switch" id="is_default" name="is_default" value="1"
                    @checked($val('is_default', false))>
                <label class="form-check-label" for="is_default">Default warehouse (used first for receipts and deliveries)</label>
            </div>
            <div class="form-check form-switch">
                <input type="hidden" name="is_active" value="0">
                <input class="form-check-input" type="checkbox" role="switch" id="is_active" name="is_active" value="1"
                    @checked($val('is_active', true))>
                <label class="form-check-label" for="is_active">Active</label>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-3">
    {{-- Contact & address --}}
    <div class="col-lg-7">
        <div class="card shadow-sm h-100">
            <div class="card-header fw-semibold"><i class="bi bi-person-badge"></i> Manager &amp; address</div>
            <div class="card-body row g-3">
                <div class="col-md-6">
                    <label class="form-label">Manager</label>
                    <input type="text" name="manager_name" maxlength="100" class="form-control {{ $err('manager_name') }}"
                        placeholder="Warehouse manager" value="{{ $val('manager_name') }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Phone</label>
                    <input type="tel" name="phone" maxlength="30" class="form-control {{ $err('phone') }}" value="{{ $val('phone') }}">
                </div>
                <div class="col-12">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" maxlength="255" class="form-control {{ $err('email') }}" value="{{ $val('email') }}">
                </div>
                <div class="col-12">
                    <label class="form-label">Address <small class="text-muted">(if different from the location)</small></label>
                    <textarea name="address" rows="2" class="form-control {{ $err('address') }}">{{ $val('address') }}</textarea>
                </div>
            </div>
        </div>
    </div>

    {{-- Storage --}}
    <div class="col-lg-5">
        <div class="card shadow-sm h-100">
            <div class="card-header fw-semibold"><i class="bi bi-boxes"></i> Storage</div>
            <div class="card-body row g-3">
                <div class="col-12">
                    <label class="form-label">Capacity</label>
                    <div class="input-group">
                        <input type="number" name="capacity_m2" min="0" step="0.01" class="form-control {{ $err('capacity_m2') }}"
                            value="{{ $val('capacity_m2') }}">
                        <span class="input-group-text">m²</span>
                    </div>
                </div>
                <div class="col-12">
                    <div class="form-check form-switch">
                        <input type="hidden" name="is_refrigerated" value="0">
                        <input class="form-check-input" type="checkbox" role="switch" id="is_refrigerated" name="is_refrigerated" value="1"
                            @checked($val('is_refrigerated', false))>
                        <label class="form-check-label" for="is_refrigerated"><i class="bi bi-snow text-info"></i> Refrigerated / cold storage</label>
                    </div>
                </div>
                <div class="col-6 temp-field">
                    <label class="form-label">Min. temperature</label>
                    <div class="input-group">
                        <input type="number" name="min_temperature" min="-50" max="50" step="0.1"
                            class="form-control {{ $err('min_temperature') }}" value="{{ $val('min_temperature') }}">
                        <span class="input-group-text">°C</span>
                    </div>
                </div>
                <div class="col-6 temp-field">
                    <label class="form-label">Max. temperature</label>
                    <div class="input-group">
                        <input type="number" name="max_temperature" min="-50" max="50" step="0.1"
                            class="form-control {{ $err('max_temperature') }}" value="{{ $val('max_temperature') }}">
                        <span class="input-group-text">°C</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm mb-3">
    <div class="card-header fw-semibold"><i class="bi bi-journal-text"></i> Notes</div>
    <div class="card-body">
        <textarea name="notes" rows="3" class="form-control {{ $err('notes') }}"
            placeholder="Opening hours, access instructions, loading dock...">{{ $val('notes') }}</textarea>
    </div>
</div>

<div class="d-flex flex-column flex-sm-row justify-content-end gap-2 mb-5">
    <a href="{{ $w->exists ? route('masterdata.warehouses.show', $w) : route('masterdata.warehouses.index') }}" class="btn btn-outline-secondary">Cancel</a>
    <button type="submit" class="btn btn-primary">
        <i class="bi bi-save"></i> {{ $w->exists ? 'Update warehouse' : 'Save warehouse' }}
    </button>
</div>

@push('scripts')
    <script>
        (function () {
            const cold = document.getElementById('is_refrigerated');
            const toggle = () => document.querySelectorAll('.temp-field').forEach(el => el.classList.toggle('d-none', !cold.checked));
            cold.addEventListener('change', toggle);
            toggle();
        })();
    </script>
@endpush
