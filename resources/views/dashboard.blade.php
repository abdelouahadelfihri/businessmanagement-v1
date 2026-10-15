@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    @php
        $money = fn($v) => number_format((float) $v, 2);
        $hour = now()->hour;
        $greeting = $hour < 12 ? 'Good morning' : ($hour < 18 ? 'Good afternoon' : 'Good evening');
        $orderLabels = \App\Models\Sales\SaleOrder::STATUSES;
        $orderColors = \App\Models\Sales\SaleOrder::STATUS_COLORS;
        $invoiceLabels = \App\Models\Sales\SaleInvoice::STATUSES;
        $invoiceColors = \App\Models\Sales\SaleInvoice::STATUS_COLORS;
        $pendingTodo = collect($todo)->sum('count');
    @endphp

    <div class="container-fluid dashboard py-3">

        {{-- Header --}}
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
            <div>
                <h1 class="h3 mb-1">{{ $greeting }} 👋</h1>
                <p class="text-muted mb-0">
                    Here is how the business is doing —
                    <strong>{{ $from->format('d M Y') }}</strong> to <strong>{{ $to->format('d M Y') }}</strong>
                </p>
            </div>

            <form method="GET" action="{{ route('dashboard') }}" class="period-switch btn-group shadow-sm" role="group">
                @foreach(\App\Http\Controllers\DashboardController::PERIODS as $key => $label)
                    <button type="submit" name="period" value="{{ $key }}"
                        class="btn btn-sm {{ $period === $key ? 'btn-primary' : 'btn-light' }}">{{ $label }}</button>
                @endforeach
            </form>
        </div>

        {{-- Quick actions --}}
        <div class="d-flex flex-wrap gap-2 mb-4">
            <a href="{{ route('sales.salesquotations.create') }}" class="btn btn-sm btn-outline-primary rounded-pill"><i class="bi bi-file-earmark-plus"></i> New quotation</a>
            <a href="{{ route('sales.salesorders.create') }}" class="btn btn-sm btn-outline-primary rounded-pill"><i class="bi bi-cart-plus"></i> New sales order</a>
            <a href="{{ route('sales.salesinvoices.create') }}" class="btn btn-sm btn-outline-primary rounded-pill"><i class="bi bi-receipt"></i> New invoice</a>
            <a href="{{ route('purchases.purchasesrequests.create') }}" class="btn btn-sm btn-outline-secondary rounded-pill"><i class="bi bi-clipboard-plus"></i> New purchase request</a>
            <a href="{{ route('purchases.purchasesorders.create') }}" class="btn btn-sm btn-outline-secondary rounded-pill"><i class="bi bi-bag-plus"></i> New purchase order</a>
            <a href="{{ route('masterdata.customers.create') }}" class="btn btn-sm btn-outline-secondary rounded-pill"><i class="bi bi-person-plus"></i> New customer</a>
            <a href="{{ route('masterdata.products.create') }}" class="btn btn-sm btn-outline-secondary rounded-pill"><i class="bi bi-box"></i> New product</a>
        </div>

        {{-- KPI cards --}}
        <div class="row g-3 mb-4">
            @php
                $cards = [
                    ['label' => 'Revenue', 'value' => $money($kpis['revenue']), 'icon' => 'bi-graph-up-arrow', 'tone' => 'primary',
                        'change' => $kpis['revenue_change'], 'hint' => 'Issued & paid invoices', 'good' => 'up'],
                    ['label' => 'Purchases', 'value' => $money($kpis['purchases']), 'icon' => 'bi-bag', 'tone' => 'purple',
                        'change' => $kpis['purchases_change'], 'hint' => 'Validated supplier invoices', 'good' => 'down'],
                    ['label' => 'Gross margin', 'value' => $money($kpis['margin']), 'icon' => 'bi-piggy-bank', 'tone' => $kpis['margin'] >= 0 ? 'success' : 'danger',
                        'change' => null, 'hint' => 'Revenue − purchases', 'good' => 'up'],
                    ['label' => 'To collect', 'value' => $money($kpis['receivables']), 'icon' => 'bi-wallet2', 'tone' => 'warning',
                        'change' => null, 'hint' => $kpis['overdue_count'] . ' overdue · ' . $money($kpis['overdue_amount']), 'good' => 'down'],
                    ['label' => 'To pay', 'value' => $money($kpis['payables']), 'icon' => 'bi-credit-card', 'tone' => 'danger',
                        'change' => null, 'hint' => 'Unpaid supplier invoices', 'good' => 'down'],
                    ['label' => 'Returns', 'value' => $money($kpis['returns']), 'icon' => 'bi-arrow-return-left', 'tone' => 'info',
                        'change' => null, 'hint' => 'Validated customer returns', 'good' => 'down'],
                ];
            @endphp

            @foreach($cards as $card)
                <div class="col-xl-2 col-lg-4 col-sm-6">
                    <div class="card kpi-card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <span class="text-muted small text-uppercase fw-semibold">{{ $card['label'] }}</span>
                                <span class="kpi-icon tone-{{ $card['tone'] }}"><i class="bi {{ $card['icon'] }}"></i></span>
                            </div>
                            <div class="fs-4 fw-bold lh-1 mb-2">{{ $card['value'] }}</div>
                            <div class="small d-flex align-items-center gap-2 flex-wrap">
                                @if(!is_null($card['change']))
                                    @php
                                        $up = $card['change'] >= 0;
                                        $isGood = ($up && $card['good'] === 'up') || (!$up && $card['good'] === 'down');
                                    @endphp
                                    <span class="badge rounded-pill {{ $card['change'] == 0 ? 'bg-light text-muted' : ($isGood ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger') }}">
                                        <i class="bi {{ $up ? 'bi-arrow-up-right' : 'bi-arrow-down-right' }}"></i> {{ abs($card['change']) }}%
                                    </span>
                                @endif
                                <span class="text-muted">{{ $card['hint'] }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Charts + todo --}}
        <div class="row g-3 mb-4">
            <div class="col-xl-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-transparent border-0 d-flex justify-content-between align-items-center pt-3">
                        <h6 class="mb-0 fw-semibold">Sales vs purchases <small class="text-muted fw-normal">· last 12 months</small></h6>
                    </div>
                    <div class="card-body">
                        <div class="chart-box"><canvas id="trendChart"></canvas></div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-transparent border-0 pt-3">
                        <h6 class="mb-0 fw-semibold">Sales orders by status</h6>
                    </div>
                    <div class="card-body d-flex flex-column">
                        @if($orderStatuses->sum() > 0)
                            <div class="chart-box chart-box-sm"><canvas id="orderChart"></canvas></div>
                        @else
                            <div class="empty-state my-auto"><i class="bi bi-pie-chart"></i><span>No sales orders yet</span></div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-transparent border-0 d-flex justify-content-between align-items-center pt-3">
                        <h6 class="mb-0 fw-semibold">Needs your attention</h6>
                        @if($pendingTodo)
                            <span class="badge bg-danger rounded-pill">{{ $pendingTodo }}</span>
                        @endif
                    </div>
                    <div class="list-group list-group-flush">
                        @foreach($todo as $item)
                            <a href="{{ $item['route'] }}" class="list-group-item list-group-item-action d-flex align-items-center gap-2 {{ $item['count'] ? '' : 'text-muted' }}">
                                <i class="bi {{ $item['icon'] }} text-{{ $item['count'] ? $item['color'] : 'muted' }}"></i>
                                <span class="flex-grow-1 small">{{ $item['label'] }}</span>
                                <span class="badge rounded-pill {{ $item['count'] ? 'bg-' . $item['color'] : 'bg-light text-muted' }}">{{ $item['count'] }}</span>
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        {{-- Rankings + stock --}}
        <div class="row g-3 mb-4">
            <div class="col-xl-4 col-md-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-transparent border-0 pt-3"><h6 class="mb-0 fw-semibold">Top customers</h6></div>
                    <div class="card-body pt-0">
                        @php $maxCustomer = (float) ($topCustomers->max('revenue') ?: 1); @endphp
                        @forelse($topCustomers as $row)
                            <div class="mb-3">
                                <div class="d-flex justify-content-between small mb-1">
                                    <span class="fw-semibold text-truncate me-2">{{ $row->customer->name ?? 'Unknown customer' }}</span>
                                    <span>{{ $money($row->revenue) }}</span>
                                </div>
                                <div class="progress" style="height: 6px;">
                                    <div class="progress-bar" style="width: {{ round($row->revenue / $maxCustomer * 100) }}%"></div>
                                </div>
                                <small class="text-muted">{{ $row->invoices }} invoice(s)</small>
                            </div>
                        @empty
                            <div class="empty-state"><i class="bi bi-people"></i><span>No invoiced sales in this period</span></div>
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="col-xl-4 col-md-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-transparent border-0 pt-3"><h6 class="mb-0 fw-semibold">Best-selling products</h6></div>
                    <div class="card-body pt-0">
                        @php $maxProduct = (float) ($topProducts->max('revenue') ?: 1); @endphp
                        @forelse($topProducts as $row)
                            <div class="mb-3">
                                <div class="d-flex justify-content-between small mb-1">
                                    <span class="fw-semibold text-truncate me-2">{{ $row->product->name ?? 'Unknown product' }}</span>
                                    <span>{{ $money($row->revenue) }}</span>
                                </div>
                                <div class="progress" style="height: 6px;">
                                    <div class="progress-bar bg-success" style="width: {{ round($row->revenue / $maxProduct * 100) }}%"></div>
                                </div>
                                <small class="text-muted">{{ rtrim(rtrim(number_format((float) $row->qty, 3, '.', ''), '0'), '.') }} sold</small>
                            </div>
                        @empty
                            <div class="empty-state"><i class="bi bi-box-seam"></i><span>No products sold in this period</span></div>
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="col-xl-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-transparent border-0 d-flex justify-content-between align-items-center pt-3">
                        <h6 class="mb-0 fw-semibold">Low stock</h6>
                        <a href="{{ route('masterdata.products.index') }}" class="small">All products</a>
                    </div>
                    <div class="card-body pt-0">
                        @forelse($lowStock as $product)
                            @php
                                $stock = (float) $product->stock;
                                $pct = $product->reorder_level > 0 ? min(100, round($stock / $product->reorder_level * 100)) : 0;
                            @endphp
                            <div class="d-flex align-items-center gap-3 mb-3">
                                <span class="kpi-icon {{ $stock <= 0 ? 'tone-danger' : 'tone-warning' }}"><i class="bi bi-box"></i></span>
                                <div class="flex-grow-1">
                                    <div class="d-flex justify-content-between small">
                                        <span class="fw-semibold text-truncate me-2">{{ $product->name }}</span>
                                        <span class="{{ $stock <= 0 ? 'text-danger fw-semibold' : '' }}">
                                            {{ rtrim(rtrim(number_format($stock, 3, '.', ''), '0'), '.') ?: '0' }} / {{ $product->reorder_level }}
                                        </span>
                                    </div>
                                    <div class="progress mt-1" style="height: 5px;">
                                        <div class="progress-bar {{ $stock <= 0 ? 'bg-danger' : 'bg-warning' }}" style="width: {{ $pct }}%"></div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="empty-state"><i class="bi bi-check2-circle text-success"></i><span>All products are above their reorder level</span></div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        {{-- Recent activity --}}
        <div class="row g-3 mb-4">
            <div class="col-xl-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-transparent border-0 d-flex justify-content-between align-items-center pt-3">
                        <h6 class="mb-0 fw-semibold">Recent invoices</h6>
                        <a href="{{ route('sales.salesinvoices.index') }}" class="small">View all</a>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 small">
                            <thead class="table-light">
                                <tr><th>Invoice</th><th>Customer</th><th>Date</th><th class="text-end">Total</th><th>Status</th></tr>
                            </thead>
                            <tbody>
                                @forelse($recentInvoices as $invoice)
                                    <tr>
                                        <td><a href="{{ route('sales.salesinvoices.show', $invoice) }}">{{ $invoice->invoice_number }}</a></td>
                                        <td>{{ $invoice->customer->name ?? '—' }}</td>
                                        <td>{{ optional($invoice->date)->format('Y-m-d') }}</td>
                                        <td class="text-end">{{ $money($invoice->total) }}</td>
                                        <td>
                                            <span class="badge bg-{{ $invoiceColors[$invoice->status] ?? 'secondary' }}">{{ $invoiceLabels[$invoice->status] ?? ucfirst($invoice->status) }}</span>
                                            @if($invoice->isOverdue())<span class="badge bg-danger">Overdue</span>@endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="text-center text-muted py-4">No invoices yet.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-xl-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-transparent border-0 d-flex justify-content-between align-items-center pt-3">
                        <h6 class="mb-0 fw-semibold">Recent sales orders</h6>
                        <a href="{{ route('sales.salesorders.index') }}" class="small">View all</a>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 small">
                            <thead class="table-light">
                                <tr><th>Order</th><th>Customer</th><th>Date</th><th class="text-end">Total</th><th>Status</th></tr>
                            </thead>
                            <tbody>
                                @forelse($recentOrders as $order)
                                    <tr>
                                        <td><a href="{{ route('sales.salesorders.show', $order) }}">{{ $order->order_number }}</a></td>
                                        <td>{{ $order->customer->name ?? '—' }}</td>
                                        <td>{{ optional($order->date)->format('Y-m-d') }}</td>
                                        <td class="text-end">{{ $money($order->total) }}</td>
                                        <td><span class="badge bg-{{ $orderColors[$order->status] ?? 'secondary' }}">{{ $orderLabels[$order->status] ?? ucfirst($order->status) }}</span></td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="text-center text-muted py-4">No sales orders yet.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- Overdue + counters --}}
        <div class="row g-3 mb-5">
            <div class="col-xl-8">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-transparent border-0 d-flex justify-content-between align-items-center pt-3">
                        <h6 class="mb-0 fw-semibold text-danger"><i class="bi bi-exclamation-triangle"></i> Overdue invoices</h6>
                        <a href="{{ route('sales.salesinvoices.index', ['status' => 'overdue']) }}" class="small">View all</a>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 small">
                            <thead class="table-light">
                                <tr><th>Invoice</th><th>Customer</th><th>Due date</th><th>Late by</th><th class="text-end">Balance due</th></tr>
                            </thead>
                            <tbody>
                                @forelse($overdueInvoices as $invoice)
                                    <tr>
                                        <td><a href="{{ route('sales.salesinvoices.show', $invoice) }}">{{ $invoice->invoice_number }}</a></td>
                                        <td>{{ $invoice->customer->name ?? '—' }}</td>
                                        <td>{{ optional($invoice->due_date)->format('Y-m-d') }}</td>
                                        <td><span class="badge bg-danger-subtle text-danger">{{ (int) $invoice->due_date->diffInDays(today()) }} days</span></td>
                                        <td class="text-end fw-semibold">{{ $money($invoice->balance_due) }} {{ $invoice->currency }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="text-center text-muted py-4"><i class="bi bi-check2-circle text-success"></i> Nothing overdue — nice.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-xl-4">
                <div class="row g-3 h-100">
                    @foreach([
                        ['Customers', $kpis['customers'], 'bi-people', 'primary', route('masterdata.customers.index')],
                        ['Suppliers', $kpis['suppliers'], 'bi-truck', 'purple', route('masterdata.suppliers.index')],
                        ['Products', $kpis['products'], 'bi-box-seam', 'success', route('masterdata.products.index')],
                        ['Open orders', $kpis['open_orders'], 'bi-cart-check', 'info', route('sales.salesorders.index')],
                    ] as [$label, $value, $icon, $tone, $url])
                        <div class="col-6">
                            <a href="{{ $url }}" class="card border-0 shadow-sm h-100 text-decoration-none counter-card">
                                <div class="card-body d-flex align-items-center gap-3">
                                    <span class="kpi-icon tone-{{ $tone }}"><i class="bi {{ $icon }}"></i></span>
                                    <div>
                                        <div class="fs-4 fw-bold text-body lh-1">{{ $value }}</div>
                                        <small class="text-muted">{{ $label }}</small>
                                    </div>
                                </div>
                            </a>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .dashboard { background: #f5f7fb; border-radius: 12px; }
        .dashboard .card { border-radius: 14px; }
        .kpi-card { transition: transform .15s ease, box-shadow .15s ease; }
        .kpi-card:hover, .counter-card:hover { transform: translateY(-2px); box-shadow: 0 .5rem 1.25rem rgba(31, 45, 61, .10) !important; }
        .counter-card { transition: transform .15s ease, box-shadow .15s ease; }
        .kpi-icon {
            width: 40px; height: 40px; border-radius: 12px; flex-shrink: 0;
            display: inline-flex; align-items: center; justify-content: center; font-size: 1.15rem;
        }
        .tone-primary { background: #e7efff; color: #2f6bff; }
        .tone-purple  { background: #f0e9ff; color: #7c4dff; }
        .tone-success { background: #e3f7ec; color: #1f9d55; }
        .tone-warning { background: #fff4de; color: #d98e04; }
        .tone-danger  { background: #ffe8e8; color: #dc3545; }
        .tone-info    { background: #e2f6fb; color: #0aa2c0; }
        .period-switch .btn { border-color: #e3e7ef; }
        .chart-box { position: relative; height: 290px; }
        .chart-box-sm { height: 250px; }
        .empty-state {
            display: flex; flex-direction: column; align-items: center; gap: .4rem;
            color: #98a2b3; padding: 1.5rem 0; text-align: center; font-size: .875rem;
        }
        .empty-state i { font-size: 1.8rem; }
        .dashboard .list-group-item { border-color: #eef1f6; padding: .55rem 1rem; }
        .dashboard .table thead th { font-weight: 600; color: #667085; border-bottom: 0; }
    </style>
@endpush

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            Chart.defaults.font.family = getComputedStyle(document.body).fontFamily;
            Chart.defaults.color = '#667085';

            const trend = @json($trend);
            const fmt = v => Number(v).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });

            new Chart(document.getElementById('trendChart'), {
                type: 'bar',
                data: {
                    labels: trend.labels,
                    datasets: [
                        { label: 'Sales', data: trend.sales, backgroundColor: '#2f6bff', borderRadius: 6, maxBarThickness: 22 },
                        { label: 'Purchases', data: trend.purchases, backgroundColor: '#c4b5fd', borderRadius: 6, maxBarThickness: 22 },
                    ],
                },
                options: {
                    maintainAspectRatio: false,
                    interaction: { mode: 'index', intersect: false },
                    plugins: {
                        legend: { position: 'bottom', labels: { usePointStyle: true, boxWidth: 8 } },
                        tooltip: { callbacks: { label: c => ` ${c.dataset.label}: ${fmt(c.parsed.y)}` } },
                    },
                    scales: {
                        x: { grid: { display: false } },
                        y: { beginAtZero: true, grid: { color: '#eef1f6' }, ticks: { callback: v => Number(v).toLocaleString() } },
                    },
                },
            });

            const orderCanvas = document.getElementById('orderChart');
            if (orderCanvas) {
                const statuses = @json($orderStatuses);
                const labels = @json($orderLabels);
                const palette = { draft: '#98a2b3', confirmed: '#2f6bff', processing: '#0aa2c0', delivered: '#1f9d55', invoiced: '#344054', cancelled: '#dc3545' };
                const keys = Object.keys(statuses);

                new Chart(orderCanvas, {
                    type: 'doughnut',
                    data: {
                        labels: keys.map(k => labels[k] ?? k),
                        datasets: [{ data: keys.map(k => statuses[k]), backgroundColor: keys.map(k => palette[k] ?? '#d0d5dd'), borderWidth: 2, borderColor: '#fff' }],
                    },
                    options: {
                        maintainAspectRatio: false,
                        cutout: '68%',
                        plugins: { legend: { position: 'bottom', labels: { usePointStyle: true, boxWidth: 8 } } },
                    },
                });
            }
        });
    </script>
@endpush
