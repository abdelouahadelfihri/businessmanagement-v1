@extends('layouts.app')

@section('title', $customer->name)

@section('content')
    @php
        $c = $customer;
        $money = fn($v) => number_format((float) $v, 2);
        $invoiceLabels = \App\Models\Sales\SaleInvoice::STATUSES;
        $invoiceColors = \App\Models\Sales\SaleInvoice::STATUS_COLORS;
        $orderLabels = \App\Models\Sales\SaleOrder::STATUSES;
        $orderColors = \App\Models\Sales\SaleOrder::STATUS_COLORS;
        $overLimit = $c->credit_limit > 0 && $stats['outstanding'] > $c->credit_limit;
    @endphp

    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-start align-items-md-center mb-4 flex-column flex-md-row gap-2">
            <h1 class="mb-0">
                {{ $c->name }}
                <span class="badge bg-{{ $c->is_active ? 'success' : 'secondary' }} fs-6 align-middle">
                    {{ $c->is_active ? 'Active' : 'Inactive' }}
                </span>
                <small class="d-block fs-6 text-muted mt-1">
                    {{ $c->code }} · {{ \App\Models\MasterData\Customer::TYPES[$c->type] ?? '' }}
                </small>
            </h1>

            <div class="d-flex flex-wrap gap-2">
                <a href="{{ route('sales.salesquotations.create') }}" class="btn btn-outline-primary">
                    <i class="bi bi-file-earmark-plus"></i> New quotation
                </a>
                <a href="{{ route('masterdata.customers.edit', $c) }}" class="btn btn-warning">
                    <i class="bi bi-pencil-square"></i> Edit
                </a>
                @unless($c->quotations_count || $c->orders_count || $c->invoices_count || $c->returns_count)
                    <form action="{{ route('masterdata.customers.destroy', $c) }}" method="POST"
                        onsubmit="return confirm('Delete this customer?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger"><i class="bi bi-trash"></i> Delete</button>
                    </form>
                @endunless
                <a href="{{ route('masterdata.customers.index') }}" class="btn btn-outline-secondary">Back</a>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        @if($c->sourceProspect)
            <div class="alert alert-info">
                <i class="bi bi-bullseye"></i>
                Converted from prospect
                <a href="{{ route('crm.prospects.show', $c->sourceProspect) }}" class="alert-link">{{ $c->sourceProspect->code }}</a>
                on {{ optional($c->sourceProspect->converted_at)->format('Y-m-d') }}
                (source: {{ \App\Models\CRM\Prospect::SOURCES[$c->sourceProspect->source] ?? '—' }}).
            </div>
        @endif

        @if($overLimit)
            <div class="alert alert-warning">
                <i class="bi bi-exclamation-triangle-fill"></i>
                Outstanding balance ({{ $money($stats['outstanding']) }} {{ $c->currency }}) is above the credit limit
                of {{ $money($c->credit_limit) }} {{ $c->currency }}.
            </div>
        @endif

        {{-- Key figures --}}
        <div class="row g-3 mb-3">
            @foreach([
                ['Total invoiced', $money($stats['invoiced']) . ' ' . $c->currency, 'bi-receipt', 'primary'],
                ['Total paid', $money($stats['paid']) . ' ' . $c->currency, 'bi-cash-coin', 'success'],
                ['Outstanding', $money($stats['outstanding']) . ' ' . $c->currency, 'bi-wallet2', $stats['outstanding'] > 0 ? 'warning' : 'secondary'],
                ['Overdue invoices', $stats['overdue'], 'bi-exclamation-triangle', $stats['overdue'] ? 'danger' : 'secondary'],
            ] as [$label, $value, $icon, $tone])
                <div class="col-6 col-lg-3">
                    <div class="card shadow-sm h-100">
                        <div class="card-body">
                            <small class="text-muted d-block mb-1"><i class="bi {{ $icon }} text-{{ $tone }}"></i> {{ $label }}</small>
                            <div class="fs-5 fw-bold text-break">{{ $value }}</div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="card shadow-sm mb-3">
            <div class="card-header fw-semibold">General information</div>
            <div class="card-body row g-3">
                <div class="col-sm-6 col-md-4">
                    <small class="text-muted d-block">Primary contact</small>
                    @php $pc = $c->contacts->firstWhere('is_primary', true); @endphp
                    @if($pc)<a href="{{ route('crm.contacts.show', $pc) }}">{{ $pc->display_name }}</a>@else — @endif
                </div>
                <div class="col-sm-6 col-md-4">
                    <small class="text-muted d-block">Email</small>
                    @if($c->email)<a href="mailto:{{ $c->email }}">{{ $c->email }}</a>@else — @endif
                </div>
                <div class="col-sm-6 col-md-4">
                    <small class="text-muted d-block">Website</small>
                    @if($c->website)<a href="{{ $c->website }}" target="_blank" rel="noopener">{{ $c->website }}</a>@else — @endif
                </div>

                <div class="col-sm-6 col-md-4">
                    <small class="text-muted d-block">Phone</small>
                    @if($c->phone)<a href="tel:{{ $c->phone }}">{{ $c->phone }}</a>@else — @endif
                </div>
                <div class="col-sm-6 col-md-4">
                    <small class="text-muted d-block">Mobile</small>
                    @if($c->mobile)<a href="tel:{{ $c->mobile }}">{{ $c->mobile }}</a>@else — @endif
                </div>
                <div class="col-sm-6 col-md-4"><small class="text-muted d-block">Customer since</small>{{ optional($c->created_at)->format('Y-m-d') }}</div>

                <div class="col-md-6"><small class="text-muted d-block">Billing address</small>{!! nl2br(e($c->full_address ?: '—')) !!}</div>
                <div class="col-md-6"><small class="text-muted d-block">Shipping address</small>{!! nl2br(e($c->shipping_address ?: 'Same as billing')) !!}</div>
            </div>
        </div>

        <div class="row g-3 mb-3">
            <div class="col-lg-6">
                <div class="card shadow-sm h-100">
                    <div class="card-header fw-semibold">Legal identifiers</div>
                    <div class="card-body row g-3">
                        <div class="col-6"><small class="text-muted d-block">ICE</small>{{ $c->ice ?? '—' }}</div>
                        <div class="col-6"><small class="text-muted d-block">IF (tax ID)</small>{{ $c->tax_id ?? '—' }}</div>
                        <div class="col-6"><small class="text-muted d-block">RC (trade register)</small>{{ $c->trade_register ?? '—' }}</div>
                        <div class="col-6"><small class="text-muted d-block">Patente</small>{{ $c->patente ?? '—' }}</div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="card shadow-sm h-100">
                    <div class="card-header fw-semibold">Commercial terms</div>
                    <div class="card-body row g-3">
                        <div class="col-6"><small class="text-muted d-block">Payment terms</small>{{ $c->payment_terms_days }} days</div>
                        <div class="col-6"><small class="text-muted d-block">Currency</small>{{ $c->currency }}</div>
                        <div class="col-6">
                            <small class="text-muted d-block">Credit limit</small>
                            {{ $c->credit_limit > 0 ? $money($c->credit_limit) . ' ' . $c->currency : 'No limit' }}
                        </div>
                        <div class="col-6"><small class="text-muted d-block">Default discount</small>{{ $money($c->discount_percent) }} %</div>
                    </div>
                </div>
            </div>
        </div>

        @include('crm.contacts._list', ['owner' => $c])

        {{-- Activity --}}
        <div class="card shadow-sm mb-3">
            <div class="card-header fw-semibold d-flex flex-wrap gap-3">
                <span>Activity</span>
                <span class="badge bg-light text-dark">{{ $c->quotations_count }} quotations</span>
                <span class="badge bg-light text-dark">{{ $c->orders_count }} orders</span>
                <span class="badge bg-light text-dark">{{ $c->invoices_count }} invoices</span>
                <span class="badge bg-light text-dark">{{ $c->returns_count }} returns</span>
            </div>
            <div class="card-body row g-3">
                <div class="col-xl-6">
                    <h6 class="fw-semibold">Recent invoices</h6>
                    <div class="table-responsive">
                        <table class="table table-bordered align-middle mb-0 small text-nowrap">
                            <thead class="table-light">
                                <tr><th>Invoice</th><th>Date</th><th>Due</th><th class="text-end">Total</th><th class="text-end">Balance</th><th>Status</th></tr>
                            </thead>
                            <tbody>
                                @forelse($recentInvoices as $invoice)
                                    <tr>
                                        <td><a href="{{ route('sales.salesinvoices.show', $invoice) }}">{{ $invoice->invoice_number }}</a></td>
                                        <td>{{ optional($invoice->date)->format('Y-m-d') }}</td>
                                        <td class="{{ $invoice->isOverdue() ? 'text-danger fw-semibold' : '' }}">{{ optional($invoice->due_date)->format('Y-m-d') ?? '—' }}</td>
                                        <td class="text-end">{{ $money($invoice->total) }}</td>
                                        <td class="text-end">{{ $money($invoice->balance_due) }}</td>
                                        <td><span class="badge bg-{{ $invoiceColors[$invoice->status] ?? 'secondary' }}">{{ $invoiceLabels[$invoice->status] ?? ucfirst($invoice->status) }}</span></td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6" class="text-center text-muted">No invoices yet.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="col-xl-6">
                    <h6 class="fw-semibold">Recent sales orders</h6>
                    <div class="table-responsive">
                        <table class="table table-bordered align-middle mb-0 small text-nowrap">
                            <thead class="table-light">
                                <tr><th>Order</th><th>Date</th><th class="text-end">Total</th><th>Status</th></tr>
                            </thead>
                            <tbody>
                                @forelse($recentOrders as $order)
                                    <tr>
                                        <td><a href="{{ route('sales.salesorders.show', $order) }}">{{ $order->order_number }}</a></td>
                                        <td>{{ optional($order->date)->format('Y-m-d') }}</td>
                                        <td class="text-end">{{ $money($order->total) }} {{ $order->currency }}</td>
                                        <td><span class="badge bg-{{ $orderColors[$order->status] ?? 'secondary' }}">{{ $orderLabels[$order->status] ?? ucfirst($order->status) }}</span></td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="text-center text-muted">No sales orders yet.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm mb-5">
            <div class="card-body">
                <h6 class="fw-semibold">Notes</h6>
                <p class="text-muted mb-0">{!! nl2br(e($c->notes ?: '—')) !!}</p>
            </div>
        </div>
    </div>
@endsection
