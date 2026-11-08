{{-- One editable contact row. Expects $key, $row (array), $isPrimary. --}}
@php
    $v = fn($f) => $row[$f] ?? '';
    $e = fn($f) => $errors->has("contacts.$key.$f") ? 'is-invalid' : '';
    $name = fn($f) => "contacts[$key][$f]";
    $deleted = ! empty($row['_delete']);
@endphp

<div class="contact-row border rounded p-3 bg-light-subtle {{ $deleted ? 'd-none' : '' }}">
    <input type="hidden" class="contact-id" name="{{ $name('id') }}" value="{{ $v('id') }}">
    <input type="hidden" class="contact-delete" name="{{ $name('_delete') }}" value="{{ $deleted ? 1 : 0 }}">

    <div class="d-flex justify-content-between align-items-center mb-2">
        <span class="fw-semibold small text-muted">Contact #<span class="row-number"></span></span>
        <div class="d-flex align-items-center gap-3">
            <div class="form-check mb-0">
                <input class="form-check-input primary-radio" type="radio" name="primary_contact_row" value="{{ $key }}"
                    id="primary_{{ $key }}" @checked($isPrimary)>
                <label class="form-check-label small" for="primary_{{ $key }}">Primary</label>
            </div>
            <button type="button" class="btn btn-sm btn-outline-danger remove-contact" title="Remove contact">
                <i class="bi bi-trash"></i>
            </button>
        </div>
    </div>

    <div class="row g-2">
        <div class="col-sm-6 col-lg-4">
            <input type="text" name="{{ $name('first_name') }}" maxlength="100" class="form-control form-control-sm {{ $e('first_name') }}"
                placeholder="First name *" aria-label="First name" value="{{ $v('first_name') }}">
        </div>
        <div class="col-sm-6 col-lg-4">
            <input type="text" name="{{ $name('last_name') }}" maxlength="100" class="form-control form-control-sm {{ $e('last_name') }}"
                placeholder="Last name" aria-label="Last name" value="{{ $v('last_name') }}">
        </div>
        <div class="col-sm-6 col-lg-4">
            <input type="text" name="{{ $name('job_title') }}" maxlength="100" class="form-control form-control-sm {{ $e('job_title') }}"
                placeholder="Job title" aria-label="Job title" value="{{ $v('job_title') }}">
        </div>
        <div class="col-sm-6 col-lg-4">
            <input type="email" name="{{ $name('email') }}" maxlength="255" class="form-control form-control-sm {{ $e('email') }}"
                placeholder="Email" aria-label="Email" value="{{ $v('email') }}">
        </div>
        <div class="col-sm-6 col-lg-4">
            <input type="tel" name="{{ $name('phone') }}" maxlength="30" class="form-control form-control-sm {{ $e('phone') }}"
                placeholder="Phone" aria-label="Phone" value="{{ $v('phone') }}">
        </div>
        <div class="col-sm-6 col-lg-4">
            <input type="tel" name="{{ $name('mobile') }}" maxlength="30" class="form-control form-control-sm {{ $e('mobile') }}"
                placeholder="Mobile" aria-label="Mobile" value="{{ $v('mobile') }}">
        </div>
    </div>
</div>
