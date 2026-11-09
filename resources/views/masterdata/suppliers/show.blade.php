@extends('layouts.app')

@section('title', $supplier->name)

@section('content')
    @php
        $s = $supplier;
        $money = fn($v) => number_format((float) $v, 2);
        $orderColors = [
            'draft' => 'secondary', 'pending_approval' => 'warning', 'approved' => 'primary', 'sent' => 'info',
            'partially_received' => 'warning', 'received' => 'success', 'closed' => 'dark', 'cancelled' => 'danger',
        ];
        $paymentColors = ['unpaid' => 'danger', 'partial' => 'warning', 'paid' => 'success'];
        $label = fn($v) => ucfirst(str_replace('_', ' ', (string) $v));
        $hasDocs = $s->requests_count || $s->orders_count || $s->receipts_count || $s->invoices_count || $s->returns_count;
    @endphp

    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-start align-items-md-center mb-4 flex-column flex-md-row gap-2">
            <h1 class="mb-0">
                {{ $s->name }}
                <span class="badge bg-{{ $s->is_active ? 'success' : 'secondary' }} fs-6 align-middle">
                    {{ $s->is_active ? 'Active' : 'Inactive' }}
                </span>
                <small class="d-block fs-6 text-muted mt-1">
                    {{ $s->code }} · {{ \App\Models\MasterData\Supplier::TYPES[$s->type] ?? '' }}
                    @if($s->rating)
                        · <span class="text-warning">{{ str_repeat('★', $s->rating) }}</span>{{ str_repeat('☆', 5 - $s->rating) }}
                    @endif
                </small>
            </h1>

            <div class="d-flex flex-wrap gap-2">
                @canroute('purchases.purchasesorders.create')
                <a href="{{ route('purchases.purchasesorders.create') }}" class="btn btn-outline-primary">
                    <i class="bi bi-bag-plus"></i> New purchase order
                </a>
                @endcanroute
                @canroute('masterdata.suppliers.edit')
                <a href="{{ route('masterdata.suppliers.edit', $s) }}" class="btn btn-warning">
                    <i class="bi bi-pencil-square"></i> Edit
                </a>
                @endcanroute
                @unless($hasDocs)
                    @canroute('masterdata.suppliers.destroy')
                    <form action="{{ route('masterdata.suppliers.destroy', $s) }}" method="POST"
                        onsubmit="return confirm('Delete this supplier?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger"><i class="bi bi-trash"></i> Delete</button>
                    </form>
                    @endcanroute
                @endunless
                @canroute('masterdata.suppliers.index')
                <a href="{{ route('masterdata.suppliers.index') }}" class="btn btn-outline-secondary">Back</a>
                @endcanroute
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        {{-- Key figures --}}
        <div class="row g-3 mb-3">
            @foreach([
                ['Total purchased', $money($stats['purchased']) . ' ' . $s->currency, 'bi-bag', 'primary'],
                ['Total paid', $money($stats['paid']) . ' ' . $s->currency, 'bi-cash-coin', 'success'],
                ['Amount due', $money($stats['due']) . ' ' . $s->currency, 'bi-credit-card', $stats['due'] > 0 ? 'danger' : 'secondary'],
                ['Overdue invoices', $stats['overdue'], 'bi-exclamation-triangle', $stats['overdue'] ? 'danger' : 'secondary'],
            ] as [$title, $value, $icon, $tone])
                <div class="col-6 col-lg-3">
                    <div class="card shadow-sm h-100">
                        <div class="card-body">
                            <small class="text-muted d-block mb-1"><i class="bi {{ $icon }} text-{{ $tone }}"></i> {{ $title }}</small>
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
                    @php $pc = $s->contacts->firstWhere('is_primary', true); @endphp
                    @if($pc)@canroute('crm.contacts.show')<a href="{{ route('crm.contacts.show', $pc) }}">{{ $pc->display_name }}</a>@else {{ $pc->display_name }}@endcanroute @else — @endif
                </div>
                <div class="col-sm-6 col-md-4">
                    <small class="text-muted d-block">Email</small>
                    @if($s->email)<a href="mailto:{{ $s->email }}">{{ $s->email }}</a>@else — @endif
                </div>
                <div class="col-sm-6 col-md-4">
                    <small class="text-muted d-block">Website</small>
                    @if($s->website)<a href="{{ $s->website }}" target="_blank" rel="noopener">{{ $s->website }}</a>@else — @endif
                </div>

                <div class="col-sm-6 col-md-4">
                    <small class="text-muted d-block">Phone</small>
                    @if($s->phone)<a href="tel:{{ $s->phone }}">{{ $s->phone }}</a>@else — @endif
                </div>
                <div class="col-sm-6 col-md-4">
                    <small class="text-muted d-block">Mobile</small>
                    @if($s->mobile)<a href="tel:{{ $s->mobile }}">{{ $s->mobile }}</a>@else — @endif
                </div>
                <div class="col-sm-6 col-md-4"><small class="text-muted d-block">Supplier since</small>{{ optional($s->created_at)->format('Y-m-d') }}</div>

                <div class="col-12"><small class="text-muted d-block">Address</small>{!! nl2br(e($s->full_address ?: '—')) !!}</div>
            </div>
        </div>

        <div class="row g-3 mb-3">
            <div class="col-lg-4">
                <div class="card shadow-sm h-100">
                    <div class="card-header fw-semibold">Legal identifiers</div>
                    <div class="card-body row g-3">
                        <div class="col-6"><small class="text-muted d-block">ICE</small>{{ $s->ice ?? '—' }}</div>
                        <div class="col-6"><small class="text-muted d-block">IF (tax ID)</small>{{ $s->tax_id ?? '—' }}</div>
                        <div class="col-6"><small class="text-muted d-block">RC</small>{{ $s->trade_register ?? '—' }}</div>
                        <div class="col-6"><small class="text-muted d-block">Patente</small>{{ $s->patente ?? '—' }}</div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card shadow-sm h-100">
                    <div class="card-header fw-semibold">Bank details</div>
                    <div class="card-body row g-3">
                        <div class="col-6"><small class="text-muted d-block">Bank</small>{{ $s->bank_name ?? '—' }}</div>
                        <div class="col-6"><small class="text-muted d-block">SWIFT / BIC</small>{{ $s->swift ?? '—' }}</div>
                        <div class="col-12"><small class="text-muted d-block">RIB</small><span class="font-monospace text-break">{{ $s->rib ?? '—' }}</span></div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card shadow-sm h-100">
                    <div class="card-header fw-semibold">Purchasing terms</div>
                    <div class="card-body row g-3">
                        <div class="col-6"><small class="text-muted d-block">Payment terms</small>{{ $s->payment_terms_days }} days</div>
                        <div class="col-6"><small class="text-muted d-block">Payment method</small>{{ \App\Models\MasterData\Supplier::PAYMENT_METHODS[$s->payment_method] ?? '—' }}</div>
                        <div class="col-6"><small class="text-muted d-block">Lead time</small>{{ $s->lead_time_days !== null ? $s->lead_time_days . ' days' : '—' }}</div>
                        <div class="col-6"><small class="text-muted d-block">Currency</small>{{ $s->currency }}</div>
                        <div class="col-6"><small class="text-muted d-block">Minimum order</small>{{ $s->minimum_order > 0 ? $money($s->minimum_order) . ' ' . $s->currency : '—' }}</div>
                        <div class="col-6"><small class="text-muted d-block">Discount granted</small>{{ $money($s->discount_percent) }} %</div>
                    </div>
                </div>
            </div>
        </div>

        @include('crm.contacts._list', ['owner' => $s])

        {{-- Activity --}}
        <div class="card shadow-sm mb-3">
            <div class="card-header fw-semibold d-flex flex-wrap gap-2 gap-md-3">
                <span>Activity</span>
                <span class="badge bg-light text-dark">{{ $s->requests_count }} requests</span>
                <span class="badge bg-light text-dark">{{ $s->orders_count }} orders</span>
                <span class="badge bg-light text-dark">{{ $s->receipts_count }} receipts</span>
                <span class="badge bg-light text-dark">{{ $s->invoices_count }} invoices</span>
                <span class="badge bg-light text-dark">{{ $s->returns_count }} returns</span>
            </div>
            <div class="card-body row g-3">
                <div class="col-xl-6">
                    <h6 class="fw-semibold">Recent purchase orders</h6>
                    <div class="table-responsive">
                        <table class="table table-bordered align-middle mb-0 small text-nowrap">
                            <thead class="table-light">
                                <tr><th>Order</th><th>Date</th><th class="text-end">Total</th><th>Status</th></tr>
                            </thead>
                            <tbody>
                                @forelse($recentOrders as $order)
                                    <tr>
                                        <td>@canroute('purchases.purchasesorders.show')<a href="{{ route('purchases.purchasesorders.show', $order) }}">{{ $order->po_number }}</a>@else {{ $order->po_number }}@endcanroute</td>
                                        <td>{{ optional($order->order_date)->format('Y-m-d') }}</td>
                                        <td class="text-end">{{ $money($order->total_amount) }} {{ $order->currency }}</td>
                                        <td><span class="badge bg-{{ $orderColors[$order->status] ?? 'secondary' }}">{{ $label($order->status) }}</span></td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="text-center text-muted">No purchase orders yet.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="col-xl-6">
                    <h6 class="fw-semibold">Recent invoices</h6>
                    <div class="table-responsive">
                        <table class="table table-bordered align-middle mb-0 small text-nowrap">
                            <thead class="table-light">
                                <tr><th>Invoice</th><th>Date</th><th>Due</th><th class="text-end">Total</th><th>Payment</th></tr>
                            </thead>
                            <tbody>
                                @forelse($recentInvoices as $invoice)
                                    <tr>
                                        <td>
                                            @canroute('purchases.purchasesinvoices.show')
                                            <a href="{{ route('purchases.purchasesinvoices.show', $invoice) }}">{{ $invoice->invoice_number }}</a>
                                            @else
                                            {{ $invoice->invoice_number }}
                                            @endcanroute
                                            @if($invoice->supplier_invoice_number)
                                                <small class="d-block text-muted">{{ $invoice->supplier_invoice_number }}</small>
                                            @endif
                                        </td>
                                        <td>{{ optional($invoice->date)->format('Y-m-d') }}</td>
                                        <td>{{ optional($invoice->due_date)->format('Y-m-d') ?? '—' }}</td>
                                        <td class="text-end">{{ $money($invoice->total) }}</td>
                                        <td>
                                            @if($invoice->status === 'validated')
                                                <span class="badge bg-{{ $paymentColors[$invoice->payment_status] ?? 'secondary' }}">{{ $label($invoice->payment_status ?? 'unpaid') }}</span>
                                            @else
                                                <span class="badge bg-secondary">{{ $label($invoice->status) }}</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="text-center text-muted">No invoices yet.</td></tr>
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
                <p class="text-muted mb-0">{!! nl2br(e($s->notes ?: '—')) !!}</p>
            </div>
        </div>
    </div>
@endsection
