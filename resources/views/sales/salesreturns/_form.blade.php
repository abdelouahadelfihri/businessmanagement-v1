@php
    $saleReturn = $saleReturn ?? null;
    $prefill = $prefill ?? [];
    $lines = $lines ?? [];
    $invoices = $invoices ?? collect();

    $val = function ($field, $default = null) use ($saleReturn, $prefill) {
        $v = old($field, optional($saleReturn)->$field ?? ($prefill[$field] ?? $default));
        return $v instanceof \Carbon\CarbonInterface ? $v->format('Y-m-d') : $v;
    };

    $fmt = fn($v) => rtrim(rtrim(number_format((float) $v, 3, '.', ''), '0'), '.') ?: '0';
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

        <div class="col-md-4">
            <label class="form-label">Return to warehouse (stock goes back here) *</label>
            <select name="warehouse_id" class="form-select" required>
                <option value="">Select a warehouse</option>
                @foreach($warehouses as $warehouse)
                    <option value="{{ $warehouse->id }}" @selected($val('warehouse_id') == $warehouse->id)>{{ $warehouse->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="col-md-4">
            <label class="form-label">Return date *</label>
            <input type="date" name="date" class="form-control" value="{{ $val('date', now()->format('Y-m-d')) }}"
                required>
        </div>

        <div class="col-md-4">
            <label class="form-label">Related invoice</label>
            <select name="invoice_id" class="form-select">
                <option value="">— none —</option>
                @foreach($invoices as $invoice)
                    <option value="{{ $invoice->id }}" @selected($val('invoice_id') == $invoice->id)>{{ $invoice->invoice_number }}</option>
                @endforeach
            </select>
        </div>

        <div class="col-md-4">
            <label class="form-label">Reason *</label>
            <select name="reason_type" class="form-select" required>
                <option value="">Select a reason</option>
                @foreach(\App\Models\Sales\SaleReturn::REASONS as $key => $label)
                    <option value="{{ $key }}" @selected($val('reason_type') === $key)>{{ $label }}</option>
                @endforeach
            </select>
        </div>

        <div class="col-md-4">
            <label class="form-label">Refund method</label>
            <select name="refund_method" class="form-select">
                <option value="">—</option>
                @foreach(\App\Models\Sales\SaleReturn::REFUND_METHODS as $method)
                    <option value="{{ $method }}" @selected($val('refund_method') === $method)>{{ $method }}</option>
                @endforeach
            </select>
        </div>

        <div class="col-md-4">
            <label class="form-label">Reference (RMA / customer ref.)</label>
            <input type="text" name="reference" class="form-control" maxlength="100" value="{{ $val('reference') }}">
        </div>

        <div class="col-12">
            <label class="form-label">Reason details</label>
            <textarea name="reason" class="form-control" rows="2"
                placeholder="What exactly is wrong with the goods?">{{ $val('reason') }}</textarea>
        </div>
    </div>
</div>

<div class="card shadow-sm mb-3">
    <div class="card-header fw-semibold">Lines to return</div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-bordered align-middle mb-0" id="linesTable">
                <thead class="table-light">
                    <tr>
                        <th>Product</th>
                        <th>Description</th>
                        <th class="text-end">Delivered</th>
                        <th class="text-end">Already returned</th>
                        <th class="text-end">Returnable</th>
                        <th style="width:130px;">Qty to return *</th>
                        <th style="width:130px;">Condition</th>
                        <th class="text-center" style="width:90px;" title="Put the goods back into the sellable stock">Restock</th>
                        <th class="text-end">Unit price</th>
                        <th class="text-end" style="width:120px;">Line total</th>
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
                            <td class="text-end">{{ $fmt($line['delivered']) }}</td>
                            <td class="text-end">{{ $fmt($line['returned']) }}</td>
                            <td class="text-end fw-semibold">{{ $fmt($line['returnable']) }}</td>
                            <td>
                                <input type="number" step="0.001" min="0" max="{{ $line['returnable'] }}"
                                    name="items[{{ $i }}][quantity]" class="form-control form-control-sm qty-input"
                                    value="{{ old("items.$i.quantity", $line['quantity']) }}" required>
                            </td>
                            <td>
                                <select name="items[{{ $i }}][condition]" class="form-select form-select-sm condition-select">
                                    @foreach(\App\Models\Sales\SaleReturn::CONDITIONS as $key => $label)
                                        <option value="{{ $key }}" @selected(old("items.$i.condition", $line['condition']) === $key)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td class="text-center">
                                <input type="hidden" name="items[{{ $i }}][restock]" value="0">
                                <input type="checkbox" name="items[{{ $i }}][restock]" value="1"
                                    class="form-check-input restock-check"
                                    @checked((int) old("items.$i.restock", $line['restock'] ? 1 : 0) === 1)>
                            </td>
                            <td class="text-end">{{ number_format($line['price'], 2) }}</td>
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
                    <label class="form-label">Notes (printed on the return document)</label>
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
                <div class="d-flex justify-content-between mb-2">
                    <span>VAT</span><strong id="taxText">0.00</strong>
                </div>
                <hr>
                <div class="d-flex justify-content-between fs-5">
                    <span>Total to refund</span><strong id="totalText">0.00</strong>
                </div>
                <small class="text-muted d-block mt-2">Prices, discounts and VAT come from the sales order.
                    Totals are recalculated on the server when you save.</small>
            </div>
        </div>
    </div>
</div>

<div class="alert alert-warning">
    <i class="bi bi-box-arrow-in-down"></i>
    <strong>Save &amp; validate</strong> puts the lines marked <em>Restock</em> back into the stock of the selected
    warehouse. Lines that are not restocked (damaged goods, for example) are recorded but do not touch the stock.
    A draft does not touch the stock.
</div>

<div class="d-flex gap-2 mb-5">
    <button type="submit" class="btn btn-secondary">
        <i class="bi bi-save"></i> Save as draft
    </button>
    <button type="submit" name="validate_now" value="1" class="btn btn-success"
        onclick="return confirm('Validate now? The stock will be updated.');">
        <i class="bi bi-check2-circle"></i> Save &amp; validate
    </button>
    <a href="{{ route('sales.salesreturns.index') }}" class="btn btn-outline-secondary">Cancel</a>
</div>

@push('scripts')
    <script>
        (function () {
            const rows = document.querySelectorAll('#linesTable .line-row');

            function recalc() {
                let subtotal = 0, tax = 0;

                rows.forEach(row => {
                    const qty = parseFloat(row.querySelector('.qty-input').value) || 0;
                    const price = parseFloat(row.dataset.price) || 0;
                    const disc = parseFloat(row.dataset.discount) || 0;
                    const rate = parseFloat(row.dataset.tax) || 0;

                    const total = qty * price * (1 - disc / 100);
                    row.querySelector('.line-total').textContent = total.toFixed(2);
                    subtotal += total;
                    tax += total * rate / 100;
                });

                document.getElementById('subtotalText').textContent = subtotal.toFixed(2);
                document.getElementById('taxText').textContent = tax.toFixed(2);
                document.getElementById('totalText').textContent = (subtotal + tax).toFixed(2);
            }

            // Goods that are not in "good" condition are not restocked by default
            rows.forEach(row => {
                row.querySelector('.condition-select').addEventListener('change', function () {
                    row.querySelector('.restock-check').checked = (this.value === 'good');
                });
            });

            document.getElementById('linesTable').addEventListener('input', recalc);
            recalc();
        })();
    </script>
@endpush
