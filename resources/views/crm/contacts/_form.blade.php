@php
    $ct = $contact;
    $val = fn($field, $default = null) => old($field, $ct->$field ?? $default);
    $err = fn($field) => $errors->has($field) ? 'is-invalid' : '';
    $ownerKey = old('owner', $ct->contactable_type ? $ct->contactable_type . ':' . $ct->contactable_id : request('owner'));
    $returnTo = old('return', request('return'));
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

<input type="hidden" name="return" value="{{ $returnTo }}">

{{-- Company --}}
<div class="card shadow-sm mb-3">
    <div class="card-header fw-semibold"><i class="bi bi-building"></i> Company</div>
    <div class="card-body row g-3">
        <div class="col-md-8">
            <label class="form-label">Belongs to *</label>
            <select name="owner" class="form-select {{ $err('owner') }}" required>
                <option value="">Choose a customer, supplier or prospect</option>
                @foreach($owners as $type => $list)
                    @if($list->isNotEmpty())
                        <optgroup label="{{ \App\Models\CRM\Contact::OWNER_TYPES[$type]['label'] }}s">
                            @foreach($list as $owner)
                                <option value="{{ $type }}:{{ $owner->id }}" @selected($ownerKey === $type . ':' . $owner->id)>
                                    {{ $owner->name }} ({{ $owner->code }})
                                </option>
                            @endforeach
                        </optgroup>
                    @endif
                @endforeach
            </select>
        </div>
        <div class="col-md-4 d-flex flex-column justify-content-end gap-1">
            <div class="form-check form-switch">
                <input type="hidden" name="is_primary" value="0">
                <input class="form-check-input" type="checkbox" role="switch" id="is_primary" name="is_primary" value="1"
                    @checked($val('is_primary', false))>
                <label class="form-check-label" for="is_primary">Primary contact</label>
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

{{-- Identity --}}
<div class="card shadow-sm mb-3">
    <div class="card-header fw-semibold"><i class="bi bi-person"></i> Identity</div>
    <div class="card-body row g-3">
        <div class="col-sm-4 col-lg-2">
            <label class="form-label">Title</label>
            <select name="salutation" class="form-select {{ $err('salutation') }}">
                <option value="">—</option>
                @foreach(\App\Models\CRM\Contact::SALUTATIONS as $key => $label)
                    <option value="{{ $key }}" @selected($val('salutation') === $key)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-sm-8 col-lg-5">
            <label class="form-label">First name *</label>
            <input type="text" name="first_name" maxlength="100" required class="form-control {{ $err('first_name') }}"
                value="{{ $val('first_name') }}">
        </div>
        <div class="col-lg-5">
            <label class="form-label">Last name</label>
            <input type="text" name="last_name" maxlength="100" class="form-control {{ $err('last_name') }}"
                value="{{ $val('last_name') }}">
        </div>
        <div class="col-md-6">
            <label class="form-label">Job title</label>
            <input type="text" name="job_title" maxlength="100" class="form-control {{ $err('job_title') }}"
                placeholder="e.g. Purchasing manager" value="{{ $val('job_title') }}">
        </div>
        <div class="col-md-6">
            <label class="form-label">Department</label>
            <input type="text" name="department" maxlength="100" class="form-control {{ $err('department') }}"
                placeholder="e.g. Accounting" value="{{ $val('department') }}">
        </div>
    </div>
</div>

{{-- Contact details --}}
<div class="card shadow-sm mb-3">
    <div class="card-header fw-semibold"><i class="bi bi-telephone"></i> Contact details</div>
    <div class="card-body row g-3">
        <div class="col-md-6 col-lg-3">
            <label class="form-label">Email</label>
            <input type="email" name="email" maxlength="255" class="form-control {{ $err('email') }}" value="{{ $val('email') }}">
        </div>
        <div class="col-md-6 col-lg-3">
            <label class="form-label">Phone</label>
            <input type="tel" name="phone" maxlength="30" class="form-control {{ $err('phone') }}" value="{{ $val('phone') }}">
        </div>
        <div class="col-md-6 col-lg-3">
            <label class="form-label">Mobile</label>
            <input type="tel" name="mobile" maxlength="30" class="form-control {{ $err('mobile') }}" value="{{ $val('mobile') }}">
        </div>
        <div class="col-md-6 col-lg-3">
            <label class="form-label">Preferred channel *</label>
            <select name="preferred_channel" class="form-select {{ $err('preferred_channel') }}" required>
                @foreach(\App\Models\CRM\Contact::CHANNELS as $key => $label)
                    <option value="{{ $key }}" @selected($val('preferred_channel', 'email') === $key)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-12">
            <label class="form-label">Notes</label>
            <textarea name="notes" rows="3" class="form-control {{ $err('notes') }}">{{ $val('notes') }}</textarea>
        </div>
    </div>
</div>

<div class="d-flex flex-column flex-sm-row justify-content-end gap-2 mb-5">
    <a href="{{ url()->previous() !== url()->current() ? url()->previous() : route('crm.contacts.index') }}"
        class="btn btn-outline-secondary">Cancel</a>
    <button type="submit" class="btn btn-primary">
        <i class="bi bi-save"></i> {{ $ct->exists ? 'Update contact' : 'Save contact' }}
    </button>
</div>
