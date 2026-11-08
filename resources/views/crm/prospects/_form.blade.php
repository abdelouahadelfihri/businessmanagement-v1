@php
    $pr = $prospect;
    $val = function ($field, $default = null) use ($pr) {
        $v = old($field, $pr->$field ?? $default);
        return $v instanceof \Carbon\CarbonInterface ? $v->format('Y-m-d') : $v;
    };
    $err = fn($field) => $errors->has($field) ? 'is-invalid' : '';
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
    <div class="card-header fw-semibold"><i class="bi bi-bullseye"></i> General information</div>
    <div class="card-body row g-3">
        <div class="col-md-6">
            <label class="form-label">Company / person name *</label>
            <input type="text" name="name" maxlength="255" required class="form-control {{ $err('name') }}"
                placeholder="e.g. Riad Hotels Group" value="{{ $val('name') }}">
        </div>
        <div class="col-sm-6 col-md-3">
            <label class="form-label">Type *</label>
            <select name="type" class="form-select {{ $err('type') }}" required>
                @foreach(\App\Models\CRM\Prospect::TYPES as $key => $label)
                    <option value="{{ $key }}" @selected($val('type', 'company') === $key)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-sm-6 col-md-3">
            <label class="form-label">Code</label>
            <input type="text" name="code" maxlength="20" class="form-control {{ $err('code') }}"
                placeholder="Auto (PRO-00001)" value="{{ $val('code') }}">
        </div>
        <div class="col-md-4">
            <label class="form-label">Industry</label>
            <input type="text" name="industry" maxlength="100" class="form-control {{ $err('industry') }}"
                placeholder="e.g. Hospitality" value="{{ $val('industry') }}">
        </div>
        <div class="col-md-4">
            <label class="form-label">Email</label>
            <input type="email" name="email" maxlength="255" class="form-control {{ $err('email') }}" value="{{ $val('email') }}">
        </div>
        <div class="col-md-4">
            <label class="form-label">Phone</label>
            <input type="tel" name="phone" maxlength="30" class="form-control {{ $err('phone') }}" value="{{ $val('phone') }}">
        </div>
        <div class="col-md-4">
            <label class="form-label">Website</label>
            <input type="url" name="website" maxlength="255" class="form-control {{ $err('website') }}"
                placeholder="https://" value="{{ $val('website') }}">
        </div>
        <div class="col-md-4">
            <label class="form-label">City</label>
            <input type="text" name="city" maxlength="100" class="form-control {{ $err('city') }}" value="{{ $val('city') }}">
        </div>
        <div class="col-md-4">
            <label class="form-label">Country</label>
            <input type="text" name="country" maxlength="100" class="form-control {{ $err('country') }}" value="{{ $val('country', 'Morocco') }}">
        </div>
        <div class="col-12">
            <label class="form-label">Address</label>
            <textarea name="address" rows="2" class="form-control {{ $err('address') }}">{{ $val('address') }}</textarea>
        </div>
    </div>
</div>

{{-- Opportunity --}}
<div class="card shadow-sm mb-3">
    <div class="card-header fw-semibold"><i class="bi bi-graph-up-arrow"></i> Opportunity</div>
    <div class="card-body row g-3">
        <div class="col-sm-6 col-lg-3">
            <label class="form-label">Status *</label>
            <select name="status" id="prospectStatus" class="form-select {{ $err('status') }}" required>
                @foreach(\App\Models\CRM\Prospect::STATUSES as $key => $label)
                    <option value="{{ $key }}" data-probability="{{ \App\Models\CRM\Prospect::STATUS_PROBABILITY[$key] }}"
                        @selected($val('status', 'new') === $key)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-sm-6 col-lg-3">
            <label class="form-label">Source *</label>
            <select name="source" class="form-select {{ $err('source') }}" required>
                @foreach(\App\Models\CRM\Prospect::SOURCES as $key => $label)
                    <option value="{{ $key }}" @selected($val('source', 'other') === $key)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-sm-6 col-lg-3">
            <label class="form-label">Estimated value</label>
            <div class="input-group">
                <input type="number" name="estimated_value" min="0" step="0.01" class="form-control {{ $err('estimated_value') }}"
                    value="{{ $val('estimated_value', 0) }}">
                <select name="currency" class="form-select flex-grow-0 w-auto {{ $err('currency') }}">
                    @foreach(\App\Models\CRM\Prospect::CURRENCIES as $cur)
                        <option value="{{ $cur }}" @selected($val('currency', 'MAD') === $cur)>{{ $cur }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <label class="form-label">Win probability *</label>
            <div class="input-group">
                <input type="number" name="probability" id="prospectProbability" min="0" max="100" step="5" required
                    class="form-control {{ $err('probability') }}" value="{{ $val('probability', 10) }}">
                <span class="input-group-text">%</span>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <label class="form-label">Expected close date</label>
            <input type="date" name="expected_close_date" class="form-control {{ $err('expected_close_date') }}"
                value="{{ $val('expected_close_date') }}">
        </div>
        <div class="col-sm-6 col-lg-3">
            <label class="form-label">Next follow-up</label>
            <input type="date" name="next_follow_up_date" class="form-control {{ $err('next_follow_up_date') }}"
                value="{{ $val('next_follow_up_date') }}">
        </div>
        <div class="col-sm-6 col-lg-3">
            <label class="form-label">Assigned to</label>
            <input type="text" name="assigned_to" maxlength="100" list="assigneeList" class="form-control {{ $err('assigned_to') }}"
                placeholder="Sales rep" value="{{ $val('assigned_to') }}">
            <datalist id="assigneeList">
                @foreach(\App\Models\CRM\Prospect::whereNotNull('assigned_to')->distinct()->pluck('assigned_to') as $name)
                    <option value="{{ $name }}">
                @endforeach
            </datalist>
        </div>
        <div class="col-sm-6 col-lg-3" id="lostReasonBox">
            <label class="form-label">Lost reason *</label>
            <input type="text" name="lost_reason" maxlength="255" class="form-control {{ $err('lost_reason') }}"
                placeholder="e.g. Price too high" value="{{ $val('lost_reason') }}">
        </div>
        <div class="col-12">
            <label class="form-label">Notes</label>
            <textarea name="notes" rows="3" class="form-control {{ $err('notes') }}"
                placeholder="Needs, budget, decision makers, history...">{{ $val('notes') }}</textarea>
        </div>
    </div>
</div>

@unless($pr->exists)
    @include('crm.contacts._quick')
@endunless

<div class="d-flex flex-column flex-sm-row justify-content-end gap-2 mb-5">
    <a href="{{ $pr->exists ? route('crm.prospects.show', $pr) : route('crm.prospects.index') }}" class="btn btn-outline-secondary">Cancel</a>
    <button type="submit" class="btn btn-primary">
        <i class="bi bi-save"></i> {{ $pr->exists ? 'Update prospect' : 'Save prospect' }}
    </button>
</div>

@push('scripts')
    <script>
        (function () {
            const status = document.getElementById('prospectStatus');
            const probability = document.getElementById('prospectProbability');
            const lostBox = document.getElementById('lostReasonBox');

            function toggleLost() { lostBox.classList.toggle('d-none', status.value !== 'lost'); }

            status.addEventListener('change', () => {
                probability.value = status.selectedOptions[0].dataset.probability;
                toggleLost();
            });
            toggleLost();
        })();
    </script>
@endpush
