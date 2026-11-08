@php
    $p = $product;
    $val = fn($field, $default = null) => old($field, $p->$field ?? $default);
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
    <div class="card-header fw-semibold"><i class="bi bi-box-seam"></i> General information</div>
    <div class="card-body row g-3">
        <div class="col-md-6">
            <label class="form-label">Name *</label>
            <input type="text" name="name" maxlength="255" class="form-control {{ $err('name') }}" required
                placeholder="e.g. Olive oil 1L" value="{{ $val('name') }}">
        </div>

        <div class="col-sm-6 col-md-3">
            <label class="form-label">Type *</label>
            <select name="type" id="productType" class="form-select {{ $err('type') }}" required>
                @foreach(\App\Models\MasterData\Product::TYPES as $key => $label)
                    <option value="{{ $key }}" @selected($val('type', 'stockable') === $key)>{{ $label }}</option>
                @endforeach
            </select>
        </div>

        <div class="col-sm-6 col-md-3">
            <label class="form-label">Brand</label>
            <input type="text" name="brand" maxlength="100" class="form-control {{ $err('brand') }}"
                value="{{ $val('brand') }}">
        </div>

        <div class="col-sm-6 col-md-3">
            <label class="form-label">Code / SKU</label>
            <input type="text" name="code" maxlength="50" class="form-control {{ $err('code') }}"
                placeholder="Auto (PRD-00001)" value="{{ $val('code') }}">
        </div>

        <div class="col-sm-6 col-md-3">
            <label class="form-label">Barcode</label>
            <div class="input-group">
                <input type="text" name="bar_code" id="barcode" maxlength="100" class="form-control {{ $err('bar_code') }}"
                    placeholder="EAN / UPC" value="{{ $val('bar_code') }}">
                <button type="button" class="btn btn-outline-secondary" id="scanBtn" title="Scan with camera">
                    <i class="bi bi-upc-scan"></i>
                </button>
            </div>
        </div>

        <div class="col-sm-6 col-md-3">
            <label class="form-label">Category</label>
            <select name="category_id" class="form-select {{ $err('category_id') }}">
                <option value="">— None —</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}" data-tax="{{ $category->default_tax_rate !== null ? (float) $category->default_tax_rate : '' }}"
                        @selected($val('category_id') == $category->id)>{{ $category->full_name }}</option>
                @endforeach
            </select>
        </div>

        <div class="col-sm-6 col-md-3">
            <label class="form-label">Unit</label>
            <select name="unit_id" class="form-select {{ $err('unit_id') }}">
                <option value="">— None —</option>
                @foreach($units as $unit)
                    <option value="{{ $unit->unit_id }}" @selected($val('unit_id') == $unit->unit_id)>
                        {{ $unit->name }}{{ $unit->abbreviation ? ' (' . $unit->abbreviation . ')' : '' }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="col-12 d-none" id="scannerBox">
            <div class="border rounded p-2 text-center">
                <div id="barcode-scanner" class="mx-auto" style="max-width: 360px;"></div>
                <button type="button" class="btn btn-sm btn-outline-danger mt-2" id="stopScanBtn">Stop scanning</button>
            </div>
        </div>

        <div class="col-12">
            <label class="form-label">Description</label>
            <textarea name="description" rows="2" class="form-control {{ $err('description') }}"
                placeholder="Shown on quotations, orders and invoices">{{ $val('description') }}</textarea>
        </div>

        <div class="col-12">
            <div class="form-check form-switch">
                <input type="hidden" name="is_active" value="0">
                <input class="form-check-input" type="checkbox" role="switch" id="is_active" name="is_active" value="1"
                    @checked($val('is_active', true))>
                <label class="form-check-label" for="is_active">Active (can be sold and purchased)</label>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-3">
    {{-- Pricing --}}
    <div class="col-lg-6">
        <div class="card shadow-sm h-100">
            <div class="card-header fw-semibold"><i class="bi bi-tag"></i> Pricing</div>
            <div class="card-body row g-3">
                <div class="col-sm-6">
                    <label class="form-label">Purchase price (HT)</label>
                    <input type="number" name="purchase_price" id="purchase_price" min="0" step="0.01"
                        class="form-control {{ $err('purchase_price') }}" value="{{ $val('purchase_price', 0) }}">
                </div>
                <div class="col-sm-6">
                    <label class="form-label">Sale price (HT)</label>
                    <input type="number" name="sale_price" id="sale_price" min="0" step="0.01"
                        class="form-control {{ $err('sale_price') }}" value="{{ $val('sale_price', 0) }}">
                </div>
                <div class="col-sm-6">
                    <label class="form-label">VAT rate *</label>
                    <select name="tax_rate" id="tax_rate" class="form-select {{ $err('tax_rate') }}" required>
                        @foreach(\App\Models\MasterData\Product::TAX_RATES as $rate)
                            <option value="{{ $rate }}" @selected((float) $val('tax_rate', 20) == $rate)>{{ $rate }} %</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-sm-6">
                    <label class="form-label">Sale price (TTC)</label>
                    <input type="text" id="sale_ttc" class="form-control bg-light" readonly tabindex="-1">
                </div>
                <div class="col-12">
                    <div class="d-flex justify-content-between align-items-center bg-light rounded px-3 py-2">
                        <span class="text-muted small">Gross margin</span>
                        <strong id="marginText">—</strong>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Stock & purchasing --}}
    <div class="col-lg-6">
        <div class="card shadow-sm h-100">
            <div class="card-header fw-semibold"><i class="bi bi-boxes"></i> Stock &amp; purchasing</div>
            <div class="card-body row g-3">
                <div class="col-12 stock-field">
                    <div class="form-check form-switch">
                        <input type="hidden" name="track_stock" value="0">
                        <input class="form-check-input" type="checkbox" role="switch" id="track_stock" name="track_stock" value="1"
                            @checked($val('track_stock', true))>
                        <label class="form-check-label" for="track_stock">Track stock for this product</label>
                    </div>
                </div>
                <div class="col-sm-6 stock-field">
                    <label class="form-label">Reorder level</label>
                    <input type="number" name="reorder_level" min="0" step="1"
                        class="form-control {{ $err('reorder_level') }}" value="{{ $val('reorder_level', 0) }}">
                    <div class="form-text">Alert when stock falls to this level</div>
                </div>
                <div class="col-sm-6 stock-field">
                    <label class="form-label">Maximum stock</label>
                    <input type="number" name="max_stock" min="0" step="1"
                        class="form-control {{ $err('max_stock') }}" value="{{ $val('max_stock') }}">
                </div>
                <div class="col-sm-6">
                    <label class="form-label">Preferred supplier</label>
                    <select name="preferred_supplier_id" class="form-select {{ $err('preferred_supplier_id') }}">
                        <option value="">— None —</option>
                        @foreach($suppliers as $supplier)
                            <option value="{{ $supplier->id }}" @selected($val('preferred_supplier_id') == $supplier->id)>
                                {{ $supplier->name }} ({{ $supplier->code }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-sm-6 stock-field">
                    <label class="form-label">Weight</label>
                    <div class="input-group">
                        <input type="number" name="weight" min="0" step="0.001"
                            class="form-control {{ $err('weight') }}" value="{{ $val('weight') }}">
                        <span class="input-group-text">kg</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Notes --}}
<div class="card shadow-sm mb-3">
    <div class="card-header fw-semibold"><i class="bi bi-journal-text"></i> Notes</div>
    <div class="card-body">
        <textarea name="notes" rows="3" class="form-control {{ $err('notes') }}"
            placeholder="Internal notes about this product">{{ $val('notes') }}</textarea>
    </div>
</div>

<div class="d-flex flex-column flex-sm-row justify-content-end gap-2 mb-5">
    <a href="{{ $p->exists ? route('masterdata.products.show', $p) : route('masterdata.products.index') }}"
        class="btn btn-outline-secondary">Cancel</a>
    <button type="submit" class="btn btn-primary">
        <i class="bi bi-save"></i> {{ $p->exists ? 'Update product' : 'Save product' }}
    </button>
</div>

@push('scripts')
    <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
    <script>
        (function () {
            const num = id => parseFloat(document.getElementById(id).value) || 0;

            // Live TTC price and margin
            function updatePricing() {
                const buy = num('purchase_price'), sell = num('sale_price'), vat = num('tax_rate');
                document.getElementById('sale_ttc').value = (sell * (1 + vat / 100)).toFixed(2);

                const margin = document.getElementById('marginText');
                if (sell > 0) {
                    const amount = sell - buy, pct = amount / sell * 100;
                    margin.textContent = `${amount.toFixed(2)} (${pct.toFixed(1)} %)`;
                    margin.className = amount < 0 ? 'text-danger' : 'text-success';
                } else {
                    margin.textContent = '—';
                    margin.className = '';
                }
            }
            ['purchase_price', 'sale_price', 'tax_rate'].forEach(id =>
                document.getElementById(id).addEventListener('input', updatePricing));
            updatePricing();

            // Picking a category applies its default VAT rate
            document.querySelector('[name=category_id]').addEventListener('change', e => {
                const tax = e.target.selectedOptions[0]?.dataset.tax;
                if (tax !== undefined && tax !== '') {
                    document.getElementById('tax_rate').value = String(parseFloat(tax));
                    updatePricing();
                }
            });

            // Services have no stock
            const type = document.getElementById('productType');
            function toggleStock() {
                const isService = type.value === 'service';
                document.querySelectorAll('.stock-field').forEach(el => el.classList.toggle('d-none', isService));
            }
            type.addEventListener('change', toggleStock);
            toggleStock();

            // Barcode scanner (camera)
            let scanner = null;
            const box = document.getElementById('scannerBox');
            function stopScan() {
                if (scanner) scanner.stop().catch(() => {}).finally(() => { scanner = null; });
                box.classList.add('d-none');
            }
            document.getElementById('scanBtn').addEventListener('click', () => {
                if (typeof Html5Qrcode === 'undefined') return alert('Scanner could not be loaded.');
                box.classList.remove('d-none');
                scanner = new Html5Qrcode('barcode-scanner');
                scanner.start({ facingMode: 'environment' }, { fps: 10, qrbox: { width: 250, height: 120 } }, text => {
                    document.getElementById('barcode').value = text;
                    stopScan();
                }).catch(err => { alert('Camera not available: ' + err); stopScan(); });
            });
            document.getElementById('stopScanBtn').addEventListener('click', stopScan);
        })();
    </script>
@endpush
