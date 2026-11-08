@php
    $s = $supplier;
    $val = fn($field, $default = null) => old($field, $s->$field ?? $default);
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
    <div class="card-header fw-semibold"><i class="bi bi-truck"></i> General information</div>
    <div class="card-body row g-3">
        <div class="col-md-3">
            <label class="form-label">Code</label>
            <input type="text" name="code" maxlength="20" class="form-control {{ $err('code') }}"
                placeholder="Auto (SUP-00001)" value="{{ $val('code') }}">
        </div>

        <div class="col-md-3">
            <label class="form-label">Type *</label>
            <select name="type" class="form-select {{ $err('type') }}" required>
                @foreach(\App\Models\MasterData\Supplier::TYPES as $key => $label)
                    <option value="{{ $key }}" @selected($val('type', 'company') === $key)>{{ $label }}</option>
                @endforeach
            </select>
        </div>

        <div class="col-md-6">
            <label class="form-label">Name / company name *</label>
            <input type="text" name="name" maxlength="255" class="form-control {{ $err('name') }}" required
                placeholder="e.g. Maghreb Steel SA" value="{{ $val('name') }}">
        </div>

        <div class="col-md-6">
            <label class="form-label">Contact person</label>
            <input type="text" name="contact_person" maxlength="255" class="form-control {{ $err('contact_person') }}"
                placeholder="e.g. Karim Benali" value="{{ $val('contact_person') }}">
        </div>

        <div class="col-sm-6 col-md-3">
            <label class="form-label">Rating</label>
            <select name="rating" class="form-select {{ $err('rating') }}">
                <option value="">Not rated</option>
                @for($i = 5; $i >= 1; $i--)
                    <option value="{{ $i }}" @selected((int) $val('rating') === $i)>{{ str_repeat('★', $i) . str_repeat('☆', 5 - $i) }}</option>
                @endfor
            </select>
        </div>

        <div class="col-sm-6 col-md-3 d-flex align-items-end">
            <div class="form-check form-switch mb-2">
                <input type="hidden" name="is_active" value="0">
                <input class="form-check-input" type="checkbox" role="switch" id="is_active" name="is_active" value="1"
                    @checked($val('is_active', true))>
                <label class="form-check-label" for="is_active">Active supplier</label>
            </div>
        </div>
    </div>
</div>

{{-- Contact & address --}}
<div class="card shadow-sm mb-3">
    <div class="card-header fw-semibold"><i class="bi bi-telephone"></i> Contact &amp; address</div>
    <div class="card-body row g-3">
        <div class="col-md-6 col-lg-3">
            <label class="form-label">Email</label>
            <input type="email" name="email" maxlength="255" class="form-control {{ $err('email') }}"
                placeholder="sales@example.ma" value="{{ $val('email') }}">
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

        <div class="col-12">
            <label class="form-label">Address</label>
            <textarea name="address" rows="2" class="form-control {{ $err('address') }}"
                placeholder="Street, number, industrial zone">{{ $val('address') }}</textarea>
        </div>
        <div class="col-md-4">
            <label class="form-label">City</label>
            <input type="text" name="city" maxlength="100" class="form-control {{ $err('city') }}"
                placeholder="e.g. Tanger" value="{{ $val('city') }}">
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
            <div class="card-header fw-semibold"><i class="bi bi-building-check"></i> Legal identifiers</div>
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

    {{-- Bank details --}}
    <div class="col-lg-6">
        <div class="card shadow-sm h-100">
            <div class="card-header fw-semibold"><i class="bi bi-bank"></i> Bank details</div>
            <div class="card-body row g-3">
                <div class="col-sm-6">
                    <label class="form-label">Bank</label>
                    <input type="text" name="bank_name" maxlength="100" class="form-control {{ $err('bank_name') }}"
                        placeholder="e.g. Attijariwafa bank" value="{{ $val('bank_name') }}">
                </div>
                <div class="col-sm-6">
                    <label class="form-label">SWIFT / BIC</label>
                    <input type="text" name="swift" maxlength="11" class="form-control text-uppercase {{ $err('swift') }}"
                        placeholder="8 or 11 characters" value="{{ $val('swift') }}">
                </div>
                <div class="col-12">
                    <label class="form-label">RIB</label>
                    <input type="text" name="rib" maxlength="24" inputmode="numeric" pattern="\d{24}"
                        class="form-control font-monospace {{ $err('rib') }}" placeholder="24 digits" value="{{ $val('rib') }}">
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Purchasing terms --}}
<div class="card shadow-sm mb-3">
    <div class="card-header fw-semibold"><i class="bi bi-briefcase"></i> Purchasing terms</div>
    <div class="card-body row g-3">
        <div class="col-sm-6 col-lg-4">
            <label class="form-label">Payment terms *</label>
            <div class="input-group">
                <input type="number" name="payment_terms_days" min="0" max="365" step="1" required
                    class="form-control {{ $err('payment_terms_days') }}" value="{{ $val('payment_terms_days', 30) }}">
                <span class="input-group-text">days</span>
            </div>
        </div>
        <div class="col-sm-6 col-lg-4">
            <label class="form-label">Payment method *</label>
            <select name="payment_method" class="form-select {{ $err('payment_method') }}" required>
                @foreach(\App\Models\MasterData\Supplier::PAYMENT_METHODS as $key => $label)
                    <option value="{{ $key }}" @selected($val('payment_method', 'bank_transfer') === $key)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-sm-6 col-lg-4">
            <label class="form-label">Currency *</label>
            <select name="currency" class="form-select {{ $err('currency') }}" required>
                @foreach(\App\Models\MasterData\Supplier::CURRENCIES as $cur)
                    <option value="{{ $cur }}" @selected($val('currency', 'MAD') === $cur)>{{ $cur }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-sm-6 col-lg-4">
            <label class="form-label">Delivery lead time</label>
            <div class="input-group">
                <input type="number" name="lead_time_days" min="0" max="365" step="1"
                    class="form-control {{ $err('lead_time_days') }}" value="{{ $val('lead_time_days') }}">
                <span class="input-group-text">days</span>
            </div>
        </div>
        <div class="col-sm-6 col-lg-4">
            <label class="form-label">Minimum order amount</label>
            <input type="number" name="minimum_order" min="0" step="0.01"
                class="form-control {{ $err('minimum_order') }}" value="{{ $val('minimum_order', 0) }}">
        </div>
        <div class="col-sm-6 col-lg-4">
            <label class="form-label">Discount granted</label>
            <div class="input-group">
                <input type="number" name="discount_percent" min="0" max="100" step="0.01"
                    class="form-control {{ $err('discount_percent') }}" value="{{ $val('discount_percent', 0) }}">
                <span class="input-group-text">%</span>
            </div>
        </div>
    </div>
</div>

{{-- Notes --}}
<div class="card shadow-sm mb-3">
    <div class="card-header fw-semibold"><i class="bi bi-journal-text"></i> Notes</div>
    <div class="card-body">
        <textarea name="notes" rows="3" class="form-control {{ $err('notes') }}"
            placeholder="Internal notes about this supplier">{{ $val('notes') }}</textarea>
    </div>
</div>

<div class="d-flex flex-column flex-sm-row justify-content-end gap-2 mb-5">
    <a href="{{ $s->exists ? route('masterdata.suppliers.show', $s) : route('masterdata.suppliers.index') }}"
        class="btn btn-outline-secondary">Cancel</a>
    <button type="submit" class="btn btn-primary">
        <i class="bi bi-save"></i> {{ $s->exists ? 'Update supplier' : 'Save supplier' }}
    </button>
</div>
