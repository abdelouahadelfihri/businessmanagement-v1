@php
    $l = $location;
    $val = fn($field, $default = null) => old($field, $l->$field ?? $default);
    $err = fn($field) => $errors->has($field) ? 'is-invalid' : '';
    $L = \App\Models\MasterData\Location::class;
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
    <div class="card-header fw-semibold"><i class="bi bi-geo-alt"></i> General information</div>
    <div class="card-body row g-3">
        <div class="col-md-6">
            <label class="form-label">Name *</label>
            <input type="text" name="name" maxlength="255" required class="form-control {{ $err('name') }}"
                placeholder="e.g. Sidi Bernoussi industrial site" value="{{ $val('name') }}">
        </div>
        <div class="col-sm-6 col-md-3">
            <label class="form-label">Code</label>
            <input type="text" name="code" maxlength="20" class="form-control {{ $err('code') }}"
                placeholder="Auto (LOC-001)" value="{{ $val('code') }}">
        </div>
        <div class="col-sm-6 col-md-3">
            <label class="form-label">Type *</label>
            <select name="type" class="form-select {{ $err('type') }}" required>
                @foreach($L::TYPES as $key => $meta)
                    <option value="{{ $key }}" @selected($val('type', 'warehouse_site') === $key)>{{ $meta['label'] }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-12">
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
    {{-- Address --}}
    <div class="col-lg-7">
        <div class="card shadow-sm h-100">
            <div class="card-header fw-semibold"><i class="bi bi-signpost-2"></i> Address</div>
            <div class="card-body row g-3">
                <div class="col-12">
                    <label class="form-label">Street address</label>
                    <input type="text" name="address" maxlength="255" class="form-control {{ $err('address') }}"
                        placeholder="Street, number, district" value="{{ $val('address') }}">
                </div>
                <div class="col-sm-6 col-md-4">
                    <label class="form-label">City</label>
                    <input type="text" name="city" maxlength="100" list="cityList" class="form-control {{ $err('city') }}"
                        placeholder="e.g. Casablanca" value="{{ $val('city') }}">
                    <datalist id="cityList">
                        @foreach(['Casablanca', 'Rabat', 'Marrakech', 'Fès', 'Tanger', 'Agadir', 'Meknès', 'Oujda', 'Kénitra', 'Tétouan', 'El Jadida', 'Mohammedia'] as $city)
                            <option value="{{ $city }}">
                        @endforeach
                    </datalist>
                </div>
                <div class="col-sm-6 col-md-3">
                    <label class="form-label">Postal code</label>
                    <input type="text" name="postal_code" maxlength="20" class="form-control {{ $err('postal_code') }}" value="{{ $val('postal_code') }}">
                </div>
                <div class="col-md-5">
                    <label class="form-label">Country</label>
                    <input type="text" name="country" maxlength="100" class="form-control {{ $err('country') }}" value="{{ $val('country', 'Morocco') }}">
                </div>

                <div class="col-sm-6">
                    <label class="form-label">Latitude</label>
                    <input type="number" name="latitude" id="lat" step="0.0000001" min="-90" max="90" class="form-control {{ $err('latitude') }}"
                        placeholder="e.g. 33.5731" value="{{ $val('latitude') }}">
                </div>
                <div class="col-sm-6">
                    <label class="form-label">Longitude</label>
                    <input type="number" name="longitude" id="lng" step="0.0000001" min="-180" max="180" class="form-control {{ $err('longitude') }}"
                        placeholder="e.g. -7.5898" value="{{ $val('longitude') }}">
                </div>
                <div class="col-12 d-flex flex-wrap gap-2 align-items-center">
                    <button type="button" class="btn btn-sm btn-outline-secondary" id="useMyPosition">
                        <i class="bi bi-crosshair"></i> Use my current position
                    </button>
                    <small class="text-muted" id="geoStatus">Optional — gives an exact map link. In Google Maps, right-click a point to copy its coordinates.</small>
                </div>
            </div>
        </div>
    </div>

    {{-- Contact --}}
    <div class="col-lg-5">
        <div class="card shadow-sm h-100">
            <div class="card-header fw-semibold"><i class="bi bi-person-badge"></i> Site contact</div>
            <div class="card-body row g-3">
                <div class="col-12">
                    <label class="form-label">Contact name</label>
                    <input type="text" name="contact_name" maxlength="100" class="form-control {{ $err('contact_name') }}"
                        placeholder="Site manager / guard" value="{{ $val('contact_name') }}">
                </div>
                <div class="col-sm-6 col-lg-12">
                    <label class="form-label">Phone</label>
                    <input type="tel" name="phone" maxlength="30" class="form-control {{ $err('phone') }}" value="{{ $val('phone') }}">
                </div>
                <div class="col-sm-6 col-lg-12">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" maxlength="255" class="form-control {{ $err('email') }}" value="{{ $val('email') }}">
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm mb-3">
    <div class="card-header fw-semibold"><i class="bi bi-journal-text"></i> Notes</div>
    <div class="card-body">
        <textarea name="notes" rows="3" class="form-control {{ $err('notes') }}"
            placeholder="Access, opening hours, security, parking...">{{ $val('notes') }}</textarea>
    </div>
</div>

<div class="d-flex flex-column flex-sm-row justify-content-end gap-2 mb-5">
    <a href="{{ $l->exists ? route('masterdata.locations.show', $l) : route('masterdata.locations.index') }}" class="btn btn-outline-secondary">Cancel</a>
    <button type="submit" class="btn btn-primary">
        <i class="bi bi-save"></i> {{ $l->exists ? 'Update location' : 'Save location' }}
    </button>
</div>

@push('scripts')
    <script>
        document.getElementById('useMyPosition').addEventListener('click', function () {
            const status = document.getElementById('geoStatus');
            if (!navigator.geolocation) { status.textContent = 'Your browser cannot share its position.'; return; }
            status.textContent = 'Getting your position...';
            navigator.geolocation.getCurrentPosition(pos => {
                document.getElementById('lat').value = pos.coords.latitude.toFixed(7);
                document.getElementById('lng').value = pos.coords.longitude.toFixed(7);
                status.textContent = 'Position filled in (accuracy about ' + Math.round(pos.coords.accuracy) + ' m).';
            }, () => { status.textContent = 'Position not available (permission denied or no signal).'; });
        });
    </script>
@endpush
