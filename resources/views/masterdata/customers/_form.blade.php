@php
    $c = $customer;
    $val = fn($field, $default = null) => old($field, $c->$field ?? $default);
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
    <div class="card-header fw-semibold"><i class="bi bi-person-vcard"></i> General information</div>
    <div class="card-body row g-3">
        <div class="col-md-3">
            <label class="form-label">Code</label>
            <input type="text" name="code" maxlength="20" class="form-control {{ $err('code') }}"
                placeholder="Auto (CUS-00001)" value="{{ $val('code') }}">
        </div>

        <div class="col-md-3">
            <label class="form-label">Type *</label>
            <select name="type" class="form-select {{ $err('type') }}" required>
                @foreach(\App\Models\MasterData\Customer::TYPES as $key => $label)
                    <option value="{{ $key }}" @selected($val('type', 'company') === $key)>{{ $label }}</option>
                @endforeach
            </select>
        </div>

        <div class="col-md-6">
            <label class="form-label">Name / company name *</label>
            <input type="text" name="name" maxlength="255" class="form-control {{ $err('name') }}" required
                placeholder="e.g. Atlas Distribution SARL" value="{{ $val('name') }}">
        </div>

        <div class="col-12">
            <div class="form-check form-switch">
                <input type="hidden" name="is_active" value="0">
                <input class="form-check-input" type="checkbox" role="switch" id="is_active" name="is_active" value="1"
                    @checked($val('is_active', true))>
                <label class="form-check-label" for="is_active">Active customer</label>
            </div>
        </div>
    </div>
</div>

{{-- Contact --}}
<div class="card shadow-sm mb-3">
    <div class="card-header fw-semibold"><i class="bi bi-telephone"></i> Contact</div>
    <div class="card-body row g-3">
        <div class="col-md-6 col-lg-3">
            <label class="form-label">Email</label>
            <input type="email" name="email" maxlength="255" class="form-control {{ $err('email') }}"
                placeholder="contact@example.ma" value="{{ $val('email') }}">
        </div>
        <div class="col-md-6 col-lg-3">
            <label class="form-label">Phone</label>
            <input type="tel" name="phone" maxlength="30" class="form-control {{ $err('phone') }}"
                placeholder="+212 5 22 00 00 00" value="{{ $val('phone') }}">
        </div>
        <div class="col-md-6 col-lg-3">
            <label class="form-label">Mobile</label>
            <input type="tel" name="mobile" maxlength="30" class="form-control {{ $err('mobile') }}"
                placeholder="+212 6 00 00 00 00" value="{{ $val('mobile') }}">
        </div>
        <div class="col-md-6 col-lg-3">
            <label class="form-label">Website</label>
            <input type="url" name="website" maxlength="255" class="form-control {{ $err('website') }}"
                placeholder="https://" value="{{ $val('website') }}">
        </div>
    </div>
</div>

{{-- Addresses --}}
<div class="card shadow-sm mb-3">
    <div class="card-header fw-semibold"><i class="bi bi-geo-alt"></i> Addresses</div>
    <div class="card-body row g-3">
        <div class="col-md-6">
            <label class="form-label">Billing address</label>
            <textarea name="address" rows="2" class="form-control {{ $err('address') }}"
                placeholder="Street, number, district">{{ $val('address') }}</textarea>
        </div>
        <div class="col-md-6">
            <label class="form-label">Shipping address <small class="text-muted">(if different)</small></label>
            <textarea name="shipping_address" rows="2" class="form-control {{ $err('shipping_address') }}">{{ $val('shipping_address') }}</textarea>
        </div>
        <div class="col-md-4">
            <label class="form-label">City</label>
            <input type="text" name="city" maxlength="100" class="form-control {{ $err('city') }}"
                placeholder="e.g. Casablanca" value="{{ $val('city') }}">
        </div>
        <div class="col-md-4">
            <label class="form-label">Postal code</label>
            <input type="text" name="postal_code" maxlength="20" class="form-control {{ $err('postal_code') }}"
                value="{{ $val('postal_code') }}">
        </div>
        <div class="col-md-4">
            <label class="form-label">Country</label>
            <input type="text" name="country" maxlength="100" class="form-control {{ $err('country') }}"
                value="{{ $val('country', 'Morocco') }}">
        </div>
    </div>
</div>

<div class="row g-3 mb-3">
    {{-- Legal identifiers --}}
    <div class="col-lg-6">
        <div class="card shadow-sm h-100">
            <div class="card-header fw-semibold"><i class="bi bi-bank"></i> Legal identifiers</div>
            <div class="card-body row g-3">
                <div class="col-sm-6">
                    <label class="form-label">ICE</label>
                    <input type="text" name="ice" maxlength="15" inputmode="numeric" pattern="\d{15}"
                        class="form-control {{ $err('ice') }}" placeholder="15 digits" value="{{ $val('ice') }}">
                </div>
                <div class="col-sm-6">
                    <label class="form-label">IF (tax ID)</label>
                    <input type="text" name="tax_id" maxlength="30" class="form-control {{ $err('tax_id') }}"
                        value="{{ $val('tax_id') }}">
                </div>
                <div class="col-sm-6">
                    <label class="form-label">RC (trade register)</label>
                    <input type="text" name="trade_register" maxlength="30" class="form-control {{ $err('trade_register') }}"
                        value="{{ $val('trade_register') }}">
                </div>
                <div class="col-sm-6">
                    <label class="form-label">Patente</label>
                    <input type="text" name="patente" maxlength="30" class="form-control {{ $err('patente') }}"
                        value="{{ $val('patente') }}">
                </div>
            </div>
        </div>
    </div>

    {{-- Commercial terms --}}
    <div class="col-lg-6">
        <div class="card shadow-sm h-100">
            <div class="card-header fw-semibold"><i class="bi bi-briefcase"></i> Commercial terms</div>
            <div class="card-body row g-3">
                <div class="col-sm-6">
                    <label class="form-label">Payment terms *</label>
                    <div class="input-group">
                        <input type="number" name="payment_terms_days" min="0" max="365" step="1" required
                            class="form-control {{ $err('payment_terms_days') }}" value="{{ $val('payment_terms_days', 30) }}">
                        <span class="input-group-text">days</span>
                    </div>
                </div>
                <div class="col-sm-6">
                    <label class="form-label">Currency *</label>
                    <select name="currency" class="form-select {{ $err('currency') }}" required>
                        @foreach(\App\Models\MasterData\Customer::CURRENCIES as $cur)
                            <option value="{{ $cur }}" @selected($val('currency', 'MAD') === $cur)>{{ $cur }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-sm-6">
                    <label class="form-label">Credit limit</label>
                    <input type="number" name="credit_limit" min="0" step="0.01"
                        class="form-control {{ $err('credit_limit') }}" value="{{ $val('credit_limit', 0) }}">
                    <div class="form-text">0 = no limit</div>
                </div>
                <div class="col-sm-6">
                    <label class="form-label">Default discount</label>
                    <div class="input-group">
                        <input type="number" name="discount_percent" min="0" max="100" step="0.01"
                            class="form-control {{ $err('discount_percent') }}" value="{{ $val('discount_percent', 0) }}">
                        <span class="input-group-text">%</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Contacts (add / edit / remove, saved with the form) --}}
@include('crm.contacts._editor', ['owner' => $c])

{{-- Notes --}}
<div class="card shadow-sm mb-3">
    <div class="card-header fw-semibold"><i class="bi bi-journal-text"></i> Notes</div>
    <div class="card-body">
        <textarea name="notes" rows="3" class="form-control {{ $err('notes') }}"
            placeholder="Internal notes about this customer">{{ $val('notes') }}</textarea>
    </div>
</div>

<div class="d-flex flex-column flex-sm-row justify-content-end gap-2 mb-5">
    <a href="{{ $c->exists ? route('masterdata.customers.show', $c) : route('masterdata.customers.index') }}"
        class="btn btn-outline-secondary">Cancel</a>
    <button type="submit" class="btn btn-primary">
        <i class="bi bi-save"></i> {{ $c->exists ? 'Update customer' : 'Save customer' }}
    </button>
</div>
