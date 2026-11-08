{{-- Optional primary contact block for owner create forms (customer / supplier / prospect). --}}
@php $qc = fn($f) => old('primary_contact.' . $f); @endphp

<div class="card shadow-sm mb-3">
    <div class="card-header fw-semibold">
        <i class="bi bi-person-plus"></i> Primary contact <small class="text-muted fw-normal">(optional — more can be added later)</small>
    </div>
    <div class="card-body row g-3">
        <div class="col-sm-6 col-lg-3">
            <label class="form-label">First name</label>
            <input type="text" name="primary_contact[first_name]" maxlength="100"
                class="form-control {{ $errors->has('primary_contact.first_name') ? 'is-invalid' : '' }}" value="{{ $qc('first_name') }}">
        </div>
        <div class="col-sm-6 col-lg-3">
            <label class="form-label">Last name</label>
            <input type="text" name="primary_contact[last_name]" maxlength="100"
                class="form-control {{ $errors->has('primary_contact.last_name') ? 'is-invalid' : '' }}" value="{{ $qc('last_name') }}">
        </div>
        <div class="col-sm-6 col-lg-2">
            <label class="form-label">Job title</label>
            <input type="text" name="primary_contact[job_title]" maxlength="100"
                class="form-control {{ $errors->has('primary_contact.job_title') ? 'is-invalid' : '' }}"
                placeholder="e.g. Buyer" value="{{ $qc('job_title') }}">
        </div>
        <div class="col-sm-6 col-lg-2">
            <label class="form-label">Email</label>
            <input type="email" name="primary_contact[email]" maxlength="255"
                class="form-control {{ $errors->has('primary_contact.email') ? 'is-invalid' : '' }}" value="{{ $qc('email') }}">
        </div>
        <div class="col-sm-6 col-lg-2">
            <label class="form-label">Mobile</label>
            <input type="tel" name="primary_contact[mobile]" maxlength="30"
                class="form-control {{ $errors->has('primary_contact.mobile') ? 'is-invalid' : '' }}" value="{{ $qc('mobile') }}">
        </div>
    </div>
</div>
