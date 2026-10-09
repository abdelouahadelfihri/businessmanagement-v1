@php
    $purchaseReceipt = $purchaseReceipt ?? null;
    $prefill = $prefill ?? [];
    $prefillLines = $prefillLines ?? null;

    $val = function ($field, $default = null) use ($purchaseReceipt, $prefill) {
        $v = old($field, optional($purchaseReceipt)->$field ?? ($prefill[$field] ?? $default));
        return $v instanceof \Carbon\CarbonInterface ? $v->format('Y-m-d') : $v;
    };

    $blankLine = [
        'purchase_order_line_id' => null,
        'product_id' => null,
        'description' => '',
        'ordered_quantity' => 0,
        'received_quantity' => 1,
        'rejected_quantity' => 0,
        'unit' => '',
        'unit_price' => 0,
        'discount_percent' => 0,
        'tax_rate' => 20,
        'batch_number' => '',
        'expiry_date' => '',
    ];

    $lines = old(
        'items',
        $purchaseReceipt
        ? $purchaseReceipt->items->map(fn($l) => [
            'purchase_order_line_id' => $l->purchase_order_line_id,
            'product_id' => $l->product_id,
            'description' => $l->description,
            'ordered_quantity' => (float) $l->ordered_quantity,
            'received_quantity' => (float) $l->received_quantity,
            'rejected_quantity' => (float) $l->rejected_quantity,
            'unit' => $l->unit,
            'unit_price' => (float) $l->unit_price,
            'discount_percent' => (float) $l->discount_percent,
            'tax_rate' => (float) $l->tax_rate,
            'batch_number' => $l->batch_number,
            'expiry_date' => optional($l->expiry_date)->format('Y-m-d'),
        ])->values()->all()
        : ($prefillLines ?: [$blankLine])
    );
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

<div class="card shadow-sm mb-3">
    <div class="card-header fw-semibold">General information</div>
    <div class="card-body row g-3">
        <div class="col-md-4">
            <label class="form-label">Purchase order *</label>
            <select name="purchase_order_id" id="purchase_order_id" class="form-select" required
                @if(!$purchaseReceipt) data-create-url="{{ route('purchases.purchasesreceipts.create') }}" @endif>
                <option value="">Select a purchase order</option>
                @foreach($purchaseOrders as $po)
                    <option value="{{ $po->id }}" @selected($val('purchase_order_id') == $po->id)>{{ $po->po_number }}</option>
                @endforeach
            </select>
            @if(!$purchaseReceipt)
                <small class="text-muted">Choosing an order loads its remaining lines.</small>
            @endif
        </div>

        <div class="col-md-4">
            <label class="form-label">Supplier *</label>
            <select name="supplier_id" class="form-select" required>
                <option value="">Select a supplier</option>
                @foreach($suppliers as $supplier)
                    <option value="{{ $supplier->id }}" @selected($val('supplier_id') == $supplier->id)>{{ $supplier->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="col-md-4">
            <label class="form-label">Warehouse *</label>
            <select name="warehouse_id" class="form-select" required>
                <option value="">Select a warehouse</option>
                @foreach($warehouses as $warehouse)
                    <option value="{{ $warehouse->id }}" @selected($val('warehouse_id') == $warehouse->id)>{{ $warehouse->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="col-md-3">
            <label class="form-label">Receipt date *</label>
            <input type="date" name="date" class="form-control" value="{{ $val('date', now()->format('Y-m-d')) }}"
                required>
        </div>

        <div class="col-md-3">
            <label class="form-label">Delivery note n°</label>
            <input type="text" name="delivery_note_number" class="form-control"
                value="{{ $val('delivery_note_number') }}">
        </div>

        <div class="col-md-3">
            <label class="form-label">Carrier</label>
            <input type="text" name="carrier" class="form-control" value="{{ $val('carrier') }}">
        </div>

        <div class="col-md-3">
            <label class="form-label">Tracking number</label>
            <input type="text" name="tracking_number" class="form-control" value="{{ $val('tracking_number') }}">
        </div>

        <div class="col-md-3">
            <label class="form-label">Currency *</label>
            <select name="currency" class="form-select" required>
                @foreach(['MAD', 'EUR', 'USD'] as $cur)
                    <option value="{{ $cur }}" @selected($val('currency', 'MAD') === $cur)>{{ $cur }}</option>
                @endforeach
            </select>
        </div>

        <div class="col-md-3">
            <label class="form-label">Quality check *</label>
            <select name="quality_status" class="form-select" required>
                @foreach(\App\Models\Purchases\PurchaseReceipt::QUALITY_STATUSES as $key => $label)
                    <option value="{{ $key }}" @selected($val('quality_status', 'pending') === $key)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
    </div>
</div>

<div class="card shadow-sm mb-3">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span class="fw-semibold">Received lines</span>
        <button type="button" id="addLine" class="btn btn-sm btn-outline-primary">
            <i class="bi bi-plus-lg"></i> Add line
        </button>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-bordered align-middle mb-0" style="min-width: 1350px;">
                <thead class="table-light">
                    <tr>
                        <th style="min-width:170px;">Product</th>
                        <th style="min-width:190px;">Description *</th>
                        <th style="width:100px;">Ordered</th>
                        <th style="width:110px;">Received *</th>
                        <th style="width:100px;">Rejected</th>
                        <th style="width:80px;">Unit</th>
                        <th style="width:120px;">Unit price *</th>
                        <th style="width:90px;">Disc. %</th>
                        <th style="width:90px;">VAT %</th>
                        <th style="width:130px;">Batch n°</th>
                        <th style="width:150px;">Expiry</th>
                        <th style="width:120px;" class="text-end">Line total</th>
                        <th style="width:50px;"></th>
                    </tr>
                </thead>
                <tbody id="linesBody"></tbody>
            </table>
        </div>
    </div>
</div>

<div class="row g-3 mb-3">
    <div class="col-md-8">
        <div class="card shadow-sm h-100">
            <div class="card-body row g-3">
                <div class="col-12">
                    <label class="form-label">Notes</label>
                    <textarea name="notes" class="form-control" rows="2">{{ $val('notes') }}</textarea>
                </div>
                <div class="col-12">
                    <label class="form-label">Internal notes</label>
                    <textarea name="internal_notes" class="form-control"
                        rows="2">{{ $val('internal_notes') }}</textarea>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between mb-2">
                    <span>Subtotal</span><strong id="subtotalText">0.00</strong>
                </div>
                <div class="mb-2">
                    <label class="form-label mb-1">Discount</label>
                    <input type="number" step="0.01" min="0" name="discount_amount" id="discount_amount"
                        class="form-control form-control-sm" value="{{ $val('discount_amount', 0) }}">
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span>VAT</span><strong id="taxText">0.00</strong>
                </div>
                <div class="mb-2">
                    <label class="form-label mb-1">Shipping cost</label>
                    <input type="number" step="0.01" min="0" name="shipping_cost" id="shipping_cost"
                        class="form-control form-control-sm" value="{{ $val('shipping_cost', 0) }}">
                </div>
                <hr>
                <div class="d-flex justify-content-between fs-5">
                    <span>Total</span><strong id="totalText">0.00</strong>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="d-flex gap-2 mb-5">
    <button type="submit" class="btn btn-secondary">
        <i class="bi bi-save"></i> Save
    </button>
    @if(!$purchaseReceipt || $purchaseReceipt->status === 'draft')
        <button type="submit" name="validate_now" value="1" class="btn btn-success">
            <i class="bi bi-check-lg"></i> Save &amp; validate
        </button>
    @endif
    <a href="{{ route('purchases.purchasesreceipts.index') }}" class="btn btn-outline-secondary">Cancel</a>
</div>

{{-- Row template --}}
<template id="lineTemplate">
    <tr class="line-row">
        <td>
            <input type="hidden" name="items[__INDEX__][purchase_order_line_id]">
            <select name="items[__INDEX__][product_id]" class="form-select form-select-sm product-select">
                <option value="">— Free text —</option>
                @foreach($products as $product)
                    <option value="{{ $product->id }}" data-name="{{ $product->name }}">{{ $product->name }}</option>
                @endforeach
            </select>
        </td>
        <td><input type="text" name="items[__INDEX__][description]" class="form-control form-control-sm" required></td>
        <td><input type="number" step="0.001" min="0" name="items[__INDEX__][ordered_quantity]"
                class="form-control form-control-sm" readonly tabindex="-1"></td>
        <td><input type="number" step="0.001" min="0" name="items[__INDEX__][received_quantity]"
                class="form-control form-control-sm" required></td>
        <td><input type="number" step="0.001" min="0" name="items[__INDEX__][rejected_quantity]"
                class="form-control form-control-sm"></td>
        <td><input type="text" name="items[__INDEX__][unit]" class="form-control form-control-sm"></td>
        <td><input type="number" step="0.01" min="0" name="items[__INDEX__][unit_price]"
                class="form-control form-control-sm" required></td>
        <td><input type="number" step="0.01" min="0" max="100" name="items[__INDEX__][discount_percent]"
                class="form-control form-control-sm"></td>
        <td><input type="number" step="0.01" min="0" max="100" name="items[__INDEX__][tax_rate]"
                class="form-control form-control-sm"></td>
        <td><input type="text" name="items[__INDEX__][batch_number]" class="form-control form-control-sm"></td>
        <td><input type="date" name="items[__INDEX__][expiry_date]" class="form-control form-control-sm"></td>
        <td class="text-end line-total">0.00</td>
        <td class="text-center">
            <button type="button" class="btn btn-sm btn-outline-danger remove-line" title="Remove line">
                <i class="bi bi-trash"></i>
            </button>
        </td>
    </tr>
</template>

@push('scripts')
    <script>
        (function () {
            const body = document.getElementById('linesBody');
            const template = document.getElementById('lineTemplate').innerHTML;
            const initialLines = @json($lines);
            const fieldNames = ['purchase_order_line_id', 'product_id', 'description', 'ordered_quantity', 'received_quantity',
                'rejected_quantity', 'unit', 'unit_price', 'discount_percent', 'tax_rate', 'batch_number', 'expiry_date'];
            let index = 0;

            const num = (el) => parseFloat(el ? el.value : 0) || 0;
            const field = (row, name) => row.querySelector('[name$="[' + name + ']"]');

            function addLine(data = {}) {
                body.insertAdjacentHTML('beforeend', template.replaceAll('__INDEX__', index));
                const row = body.lastElementChild;

                fieldNames.forEach(name => {
                    const el = field(row, name);
                    if (el && data[name] !== undefined && data[name] !== null) el.value = data[name];
                });

                index++;
                recalc();
            }

            function recalc() {
                let subtotal = 0, tax = 0;

                body.querySelectorAll('.line-row').forEach(row => {
                    const total = num(field(row, 'received_quantity')) * num(field(row, 'unit_price'))
                        * (1 - num(field(row, 'discount_percent')) / 100);

                    row.querySelector('.line-total').textContent = total.toFixed(2);
                    subtotal += total;
                    tax += total * num(field(row, 'tax_rate')) / 100;
                });

                const discount = num(document.getElementById('discount_amount'));
                const shipping = num(document.getElementById('shipping_cost'));

                document.getElementById('subtotalText').textContent = subtotal.toFixed(2);
                document.getElementById('taxText').textContent = tax.toFixed(2);
                document.getElementById('totalText').textContent = (subtotal - discount + tax + shipping).toFixed(2);
            }

            body.addEventListener('input', recalc);

            body.addEventListener('change', e => {
                if (!e.target.classList.contains('product-select')) return;
                const row = e.target.closest('.line-row');
                const name = e.target.selectedOptions[0].dataset.name;
                const desc = field(row, 'description');
                if (name && !desc.value) desc.value = name;
            });

            body.addEventListener('click', e => {
                const btn = e.target.closest('.remove-line');
                if (!btn) return;
                if (body.querySelectorAll('.line-row').length > 1) {
                    btn.closest('.line-row').remove();
                    recalc();
                }
            });

            document.getElementById('addLine').addEventListener('click', () =>
                addLine({ received_quantity: 1, rejected_quantity: 0, unit_price: 0, discount_percent: 0, tax_rate: 20 }));
            document.getElementById('discount_amount').addEventListener('input', recalc);
            document.getElementById('shipping_cost').addEventListener('input', recalc);

            // On the create page, picking a purchase order reloads the page with its remaining lines.
            const poSelect = document.getElementById('purchase_order_id');
            if (poSelect && poSelect.dataset.createUrl) {
                poSelect.addEventListener('change', () => {
                    if (poSelect.value) {
                        window.location.href = poSelect.dataset.createUrl + '?purchase_order_id=' + encodeURIComponent(poSelect.value);
                    }
                });
            }

            initialLines.forEach(line => addLine(line));
        })();
    </script>
@endpush
