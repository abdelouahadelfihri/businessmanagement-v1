@php
    $saleInvoice = $saleInvoice ?? null;
    $prefill = $prefill ?? [];
    $lines = $lines ?? [];

    $val = function ($field, $default = null) use ($saleInvoice, $prefill) {
        $v = old($field, optional($saleInvoice)->$field ?? ($prefill[$field] ?? $default));
        return $v instanceof \Carbon\CarbonInterface ? $v->format('Y-m-d') : $v;
    };

    $fmt = fn($v) => rtrim(rtrim(number_format((float) $v, 3, '.', ''), '0'), '.') ?: '0';

    $paymentTerms = ['Immediate', '15 days', '30 days', '45 days', '60 days', 'End of month'];
    $paymentMethods = ['Bank transfer', 'Cash', 'Cheque', 'Card', 'Other'];
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

@if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif

<input type="hidden" name="sales_order_id" value="{{ $saleOrder->id }}">

<div class="card shadow-sm mb-3">
    <div class="card-header fw-semibold">General information</div>
    <div class="card-body row g-3">
        <div class="col-md-4">
            <small class="text-muted d-block">Sales order</small>
            <a href="{{ route('sales.salesorders.show', $saleOrder) }}">{{ $saleOrder->order_number }}</a>
        </div>
        <div class="col-md-4">
            <small class="text-muted d-block">Customer</small>
            {{ $saleOrder->customer->name ?? '—' }}
        </div>
        <div class="col-md-4">
            <small class="text-muted d-block">Currency</small>
            {{ $saleOrder->currency }}
        </div>

        <div class="col-md-3">
            <label class="form-label">Invoice date *</label>
            <input type="date" name="date" class="form-control" value="{{ $val('date', now()->format('Y-m-d')) }}"
                required>
        </div>

        <div class="col-md-3">
            <label class="form-label">Due date</label>
            <input type="date" name="due_date" class="form-control" value="{{ $val('due_date') }}">
        </div>

        <div class="col-md-3">
            <label class="form-label">Payment terms</label>
            <select name="payment_terms" class="form-select">
                <option value="">—</option>
                @foreach($paymentTerms as $term)
                    <option value="{{ $term }}" @selected($val('payment_terms') === $term)>{{ $term }}</option>
                @endforeach
            </select>
        </div>

        <div class="col-md-3">
            <label class="form-label">Payment method</label>
            <select name="payment_method" class="form-select">
                <option value="">—</option>
                @foreach($paymentMethods as $method)
                    <option value="{{ $method }}" @selected($val('payment_method') === $method)>{{ $method }}</option>
                @endforeach
            </select>
        </div>

        <div class="col-md-4">
            <label class="form-label">Customer reference / PO number</label>
            <input type="text" name="reference" class="form-control" maxlength="100" value="{{ $val('reference') }}">
        </div>

        <div class="col-md-8">
            <label class="form-label">Billing address</label>
            <textarea name="billing_address" class="form-control" rows="2">{{ $val('billing_address') }}</textarea>
        </div>
    </div>
</div>

<div class="card shadow-sm mb-3">
    <div class="card-header fw-semibold">Lines to invoice</div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-bordered align-middle mb-0" id="linesTable">
                <thead class="table-light">
                    <tr>
                        <th>Product</th>
                        <th>Description</th>
                        <th class="text-end">Ordered</th>
                        <th class="text-end">Already invoiced</th>
                        <th class="text-end">Remaining</th>
                        <th style="width:140px;">Qty to invoice *</th>
                        <th class="text-end">Unit price</th>
                        <th class="text-end">Disc. %</th>
                        <th class="text-end">VAT %</th>
                        <th class="text-end" style="width:130px;">Line total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($lines as $i => $line)
                        <tr class="line-row" data-price="{{ $line['price'] }}" data-discount="{{ $line['discount_percent'] }}"
                            data-tax="{{ $line['tax_rate'] }}">
                            <td>
                                {{ $line['product'] ?? '—' }}
                                <input type="hidden" name="items[{{ $i }}][order_line_id]" value="{{ $line['order_line_id'] }}">
                            </td>
                            <td>{{ $line['description'] }} @if($line['unit'])<small class="text-muted">({{ $line['unit'] }})</small>@endif</td>
                            <td class="text-end">{{ $fmt($line['ordered']) }}</td>
                            <td class="text-end">{{ $fmt($line['invoiced']) }}</td>
                            <td class="text-end fw-semibold">{{ $fmt($line['remaining']) }}</td>
                            <td>
                                <input type="number" step="0.001" min="0" max="{{ $line['remaining'] }}"
                                    name="items[{{ $i }}][quantity]" class="form-control form-control-sm qty-input"
                                    value="{{ old("items.$i.quantity", $line['quantity']) }}" required>
                            </td>
                            <td class="text-end">{{ number_format($line['price'], 2) }}</td>
                            <td class="text-end">{{ number_format($line['discount_percent'], 2) }}</td>
                            <td class="text-end">{{ number_format($line['tax_rate'], 2) }}</td>
                            <td class="text-end line-total">0.00</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="row g-3 mb-3">
    <div class="col-md-8">
        <div class="card shadow-sm h-100">
            <div class="card-body row g-3">
                <div class="col-12">
                    <label class="form-label">Notes (printed on the invoice)</label>
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
                <div class="d-flex justify-content-between mb-2 text-muted">
                    <span>Discounts included</span><span id="discountText">0.00</span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span>VAT</span><strong id="taxText">0.00</strong>
                </div>
                <hr>
                <div class="d-flex justify-content-between fs-5">
                    <span>Total</span><strong id="totalText">0.00</strong>
                </div>
                <small class="text-muted d-block mt-2">Prices, discounts and VAT come from the sales order. Totals are
                    recalculated on the server when you save.</small>
            </div>
        </div>
    </div>
</div>

<div class="alert alert-warning">
    <i class="bi bi-receipt"></i>
    <strong>Save &amp; issue</strong> locks the invoice: it can no longer be edited, only paid or cancelled.
    A draft can be edited and deleted freely. Invoices do not change the stock (deliveries do).
</div>

<div class="d-flex gap-2 mb-5">
    <button type="submit" class="btn btn-secondary">
        <i class="bi bi-save"></i> Save as draft
    </button>
    <button type="submit" name="validate_now" value="1" class="btn btn-success"
        onclick="return confirm('Issue this invoice now? It will no longer be editable.');">
        <i class="bi bi-check2-circle"></i> Save &amp; issue
    </button>
    <a href="{{ route('sales.salesinvoices.index') }}" class="btn btn-outline-secondary">Cancel</a>
</div>

@push('scripts')
    <script>
        (function () {
            const rows = document.querySelectorAll('#linesTable .line-row');

            function recalc() {
                let subtotal = 0, tax = 0, discount = 0;

                rows.forEach(row => {
                    const qty = parseFloat(row.querySelector('.qty-input').value) || 0;
                    const price = parseFloat(row.dataset.price) || 0;
                    const disc = parseFloat(row.dataset.discount) || 0;
                    const rate = parseFloat(row.dataset.tax) || 0;

                    const gross = qty * price;
                    const total = gross * (1 - disc / 100);
                    row.querySelector('.line-total').textContent = total.toFixed(2);
                    subtotal += total;
                    discount += gross - total;
                    tax += total * rate / 100;
                });

                document.getElementById('subtotalText').textContent = subtotal.toFixed(2);
                document.getElementById('discountText').textContent = discount.toFixed(2);
                document.getElementById('taxText').textContent = tax.toFixed(2);
                document.getElementById('totalText').textContent = (subtotal + tax).toFixed(2);
            }

            document.getElementById('linesTable').addEventListener('input', recalc);
            recalc();
        })();
    </script>
@endpush
