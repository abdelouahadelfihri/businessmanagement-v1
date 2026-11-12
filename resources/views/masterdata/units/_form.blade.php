@php
    $u = $unit;
    $val = fn($field, $default = null) => old($field, $u->$field ?? $default);
    $err = fn($field) => $errors->has($field) ? 'is-invalid' : '';
    $U = \App\Models\MasterData\Unit::class;
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
    <div class="col-lg-7">
        <div class="card shadow-sm h-100">
            <div class="card-header fw-semibold"><i class="bi bi-rulers"></i> General information</div>
            <div class="card-body row g-3">
                <div class="col-md-7">
                    <label class="form-label">Name *</label>
                    <input type="text" name="name" maxlength="100" required class="form-control {{ $err('name') }}"
                        placeholder="e.g. Kilogram" value="{{ $val('name') }}">
                </div>
                <div class="col-md-5">
                    <label class="form-label">Abbreviation</label>
                    <input type="text" name="abbreviation" id="unitAbbr" maxlength="20" class="form-control {{ $err('abbreviation') }}"
                        placeholder="e.g. kg" value="{{ $val('abbreviation') }}">
                    <div class="form-text">Shown on documents next to quantities.</div>
                </div>

                <div class="col-md-7">
                    <label class="form-label">Type *</label>
                    <select name="type" id="unitType" class="form-select {{ $err('type') }}" required>
                        @foreach($U::TYPES as $key => $meta)
                            <option value="{{ $key }}" @selected($val('type', 'count') === $key)>{{ $meta['label'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-5">
                    <label class="form-label">Decimal places *</label>
                    <select name="decimal_places" class="form-select {{ $err('decimal_places') }}" required>
                        @foreach(range(0, 6) as $d)
                            <option value="{{ $d }}" @selected((int) $val('decimal_places', 0) === $d)>
                                {{ $d }} — {{ number_format(1, $d, '.', '') }}
                            </option>
                        @endforeach
                    </select>
                    <div class="form-text">0 for pieces, 3 for kilograms or liters.</div>
                </div>

                <div class="col-12">
                    <label class="form-label">Description</label>
                    <input type="text" name="description" maxlength="255" class="form-control {{ $err('description') }}"
                        value="{{ $val('description') }}">
                </div>

                <div class="col-12">
                    <div class="form-check form-switch">
                        <input type="hidden" name="is_active" value="0">
                        <input class="form-check-input" type="checkbox" role="switch" id="is_active" name="is_active" value="1"
                            @checked($val('is_active', true))>
                        <label class="form-check-label" for="is_active">Active (can be chosen on products)</label>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Conversion --}}
    <div class="col-lg-5">
        <div class="card shadow-sm h-100">
            <div class="card-header fw-semibold"><i class="bi bi-arrow-left-right"></i> Conversion</div>
            <div class="card-body">
                <label class="form-label">Based on</label>
                <select name="base_unit_id" id="unitBase" class="form-select mb-1 {{ $err('base_unit_id') }}">
                    <option value="">— None (this is a base unit) —</option>
                    @foreach($baseUnits as $base)
                        <option value="{{ $base->unit_id }}" data-type="{{ $base->type }}" data-short="{{ $base->short }}"
                            @selected($val('base_unit_id') == $base->unit_id)>
                            {{ $base->label }} · {{ $base->type_label }}
                        </option>
                    @endforeach
                </select>
                <div class="form-text mb-3">Choose a base unit to define this one as a multiple or fraction of it.</div>

                <div id="factorBox">
                    <label class="form-label">Conversion *</label>
                    <div class="input-group">
                        <span class="input-group-text">1&nbsp;<span id="convFrom">unit</span>&nbsp;=</span>
                        <input type="number" name="conversion_factor" id="unitFactor" min="0.000001" step="any"
                            class="form-control {{ $err('conversion_factor') }}" value="{{ $val('conversion_factor', 1) + 0 }}">
                        <span class="input-group-text" id="convTo">base</span>
                    </div>
                    <div class="form-text">e.g. 1 box = 12 pc, 1 g = 0.001 kg.</div>
                </div>

                <div id="noBaseNote" class="alert alert-light border small mb-0">
                    <i class="bi bi-info-circle"></i> A base unit is the reference of its type (e.g. kg for weight, pc for count).
                </div>
            </div>
        </div>
    </div>
</div>

<div class="d-flex flex-column flex-sm-row justify-content-end gap-2 mb-5">
    <a href="{{ $u->exists ? route('masterdata.units.show', $u) : route('masterdata.units.index') }}" class="btn btn-outline-secondary">Cancel</a>
    <button type="submit" class="btn btn-primary">
        <i class="bi bi-save"></i> {{ $u->exists ? 'Update unit' : 'Save unit' }}
    </button>
</div>

@push('scripts')
    <script>
        (function () {
            const base = document.getElementById('unitBase');
            const type = document.getElementById('unitType');
            const abbr = document.getElementById('unitAbbr');
            const factorBox = document.getElementById('factorBox');
            const note = document.getElementById('noBaseNote');

            function refresh() {
                const opt = base.selectedOptions[0];
                const hasBase = !!base.value;

                factorBox.classList.toggle('d-none', !hasBase);
                note.classList.toggle('d-none', hasBase);

                if (hasBase) {
                    type.value = opt.dataset.type;          // a unit and its base share the same type
                    document.getElementById('convTo').textContent = opt.dataset.short;
                }
                document.getElementById('convFrom').textContent = abbr.value || 'unit';

                // Only offer base units of the selected type
                [...base.options].forEach(o => { if (o.value) o.hidden = o.dataset.type !== type.value; });
            }

            base.addEventListener('change', refresh);
            abbr.addEventListener('input', refresh);
            type.addEventListener('change', () => {
                if (base.value && base.selectedOptions[0].dataset.type !== type.value) base.value = '';
                refresh();
            });
            refresh();
        })();
    </script>
@endpush
