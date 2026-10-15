<?php

namespace App\Http\Controllers;

use App\Models\MasterData\Customer;
use App\Models\MasterData\Product;
use App\Models\MasterData\Supplier;
use App\Models\Purchases\PurchaseInvoice;
use App\Models\Purchases\PurchaseOrder;
use App\Models\Purchases\PurchaseRequest;
use App\Models\Sales\SaleDelivery;
use App\Models\Sales\SaleInvoice;
use App\Models\Sales\SaleOrder;
use App\Models\Sales\SaleQuotation;
use App\Models\Sales\SaleReturn;
use App\Models\Sales\SalesInvoiceLine;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public const PERIODS = [
        'today' => 'Today',
        '7d' => 'Last 7 days',
        '30d' => 'Last 30 days',
        'month' => 'This month',
        'year' => 'This year',
    ];

    /** Invoice statuses that count as real revenue. */
    private const BILLED = ['issued', 'partial', 'paid'];

    public function index(Request $request)
    {
        $period = array_key_exists($request->input('period'), self::PERIODS) ? $request->input('period') : 'month';
        [$from, $to] = $this->range($period);
        [$prevFrom, $prevTo] = [$from->copy()->sub($from->diff($to)), $from->copy()->subSecond()];

        // ---- KPIs ------------------------------------------------------------
        $revenue = $this->revenue($from, $to);
        $prevRevenue = $this->revenue($prevFrom, $prevTo);

        $purchases = $this->purchases($from, $to);
        $prevPurchases = $this->purchases($prevFrom, $prevTo);

        $openInvoices = SaleInvoice::whereIn('status', ['issued', 'partial']);
        $kpis = [
            'revenue' => $revenue,
            'revenue_change' => $this->change($revenue, $prevRevenue),
            'purchases' => $purchases,
            'purchases_change' => $this->change($purchases, $prevPurchases),
            'receivables' => (float) (clone $openInvoices)->sum('balance_due'),
            'overdue_count' => (clone $openInvoices)->whereDate('due_date', '<', today())->count(),
            'overdue_amount' => (float) (clone $openInvoices)->whereDate('due_date', '<', today())->sum('balance_due'),
            'payables' => (float) PurchaseInvoice::whereNotNull('validated_at')
                ->selectRaw('COALESCE(SUM(total - COALESCE(amount_paid, 0)), 0) as due')->value('due'),
            'open_orders' => SaleOrder::whereIn('status', ['confirmed', 'processing'])->count(),
            'returns' => (float) SaleReturn::where('status', 'validated')->whereBetween('date', [$from, $to])->sum('total'),
            'customers' => Customer::count(),
            'suppliers' => Supplier::count(),
            'products' => Product::count(),
        ];
        $kpis['margin'] = $kpis['revenue'] - $kpis['purchases'];

        // ---- Charts ----------------------------------------------------------
        $trend = $this->monthlyTrend();

        $orderStatuses = SaleOrder::select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')->pluck('total', 'status');

        // ---- Lists -----------------------------------------------------------
        $topCustomers = SaleInvoice::with('customer')
            ->whereIn('status', self::BILLED)
            ->whereBetween('date', [$from, $to])
            ->select('customer_id', DB::raw('SUM(total) as revenue'), DB::raw('COUNT(*) as invoices'))
            ->groupBy('customer_id')
            ->orderByDesc('revenue')
            ->limit(5)
            ->get();

        $topProducts = $this->topProducts($from, $to);

        $lowStock = Product::where('is_active', true)
            ->where('reorder_level', '>', 0)
            ->withSum('warehouseStocks as stock', 'quantity')
            ->get()
            ->filter(fn($p) => (float) $p->stock <= (float) $p->reorder_level)
            ->sortBy(fn($p) => (float) $p->stock - (float) $p->reorder_level)
            ->take(6)
            ->values();

        $overdueInvoices = SaleInvoice::with('customer')
            ->whereIn('status', ['issued', 'partial'])
            ->whereDate('due_date', '<', today())
            ->orderBy('due_date')
            ->limit(5)
            ->get();

        $recentInvoices = SaleInvoice::with('customer')->latest('id')->limit(6)->get();
        $recentOrders = SaleOrder::with('customer')->latest('id')->limit(6)->get();

        // ---- Things waiting on someone --------------------------------------
        $todo = [
            ['label' => 'Purchase requests to approve', 'count' => PurchaseRequest::where('status', 'pending')->count(),
                'icon' => 'bi-clipboard-check', 'color' => 'warning', 'route' => route('purchases.purchasesrequests.index')],
            ['label' => 'Purchase orders to approve', 'count' => PurchaseOrder::where('status', PurchaseOrder::STATUS_PENDING_APPROVAL)->count(),
                'icon' => 'bi-bag-check', 'color' => 'warning', 'route' => route('purchases.purchasesorders.index')],
            ['label' => 'Quotations awaiting answer', 'count' => SaleQuotation::where('status', 'sent')->count(),
                'icon' => 'bi-file-earmark-text', 'color' => 'info', 'route' => route('sales.salesquotations.index', ['status' => 'sent'])],
            ['label' => 'Orders to deliver', 'count' => $kpis['open_orders'],
                'icon' => 'bi-truck', 'color' => 'primary', 'route' => route('sales.salesorders.index')],
            ['label' => 'Draft deliveries', 'count' => SaleDelivery::where('status', 'draft')->count(),
                'icon' => 'bi-box-seam', 'color' => 'secondary', 'route' => route('sales.salesdeliveries.index', ['status' => 'draft'])],
            ['label' => 'Draft invoices', 'count' => SaleInvoice::where('status', 'draft')->count(),
                'icon' => 'bi-receipt', 'color' => 'secondary', 'route' => route('sales.salesinvoices.index', ['status' => 'draft'])],
            ['label' => 'Draft returns', 'count' => SaleReturn::where('status', 'draft')->count(),
                'icon' => 'bi-arrow-return-left', 'color' => 'secondary', 'route' => route('sales.salesreturns.index', ['status' => 'draft'])],
            ['label' => 'Overdue invoices', 'count' => $kpis['overdue_count'],
                'icon' => 'bi-exclamation-triangle', 'color' => 'danger', 'route' => route('sales.salesinvoices.index', ['status' => 'overdue'])],
        ];

        return view('dashboard', compact(
            'period', 'from', 'to', 'kpis', 'trend', 'orderStatuses',
            'topCustomers', 'topProducts', 'lowStock', 'overdueInvoices',
            'recentInvoices', 'recentOrders', 'todo'
        ));
    }

    /* ------------------------------------------------------------------ */

    private function range(string $period): array
    {
        $now = now();

        return match ($period) {
            'today' => [$now->copy()->startOfDay(), $now->copy()->endOfDay()],
            '7d' => [$now->copy()->subDays(6)->startOfDay(), $now->copy()->endOfDay()],
            '30d' => [$now->copy()->subDays(29)->startOfDay(), $now->copy()->endOfDay()],
            'year' => [$now->copy()->startOfYear(), $now->copy()->endOfDay()],
            default => [$now->copy()->startOfMonth(), $now->copy()->endOfDay()],
        };
    }

    private function revenue(Carbon $from, Carbon $to): float
    {
        return (float) SaleInvoice::whereIn('status', self::BILLED)->whereBetween('date', [$from, $to])->sum('total');
    }

    private function purchases(Carbon $from, Carbon $to): float
    {
        return (float) PurchaseInvoice::whereNotNull('validated_at')->whereBetween('date', [$from, $to])->sum('total');
    }

    /** % change vs the previous period, null when there is nothing to compare with. */
    private function change(float $current, float $previous): ?float
    {
        if ($previous == 0.0) {
            return $current == 0.0 ? 0.0 : null;
        }

        return round(($current - $previous) / $previous * 100, 1);
    }

    /** Best-selling products (by invoiced amount, excluding VAT). */
    private function topProducts(Carbon $from, Carbon $to)
    {
        $rows = DB::table((new SalesInvoiceLine())->getTable() . ' as l')
            ->join('sales_invoices as i', 'i.id', '=', 'l.sales_invoice_id')
            ->whereIn('i.status', self::BILLED)
            ->whereBetween('i.date', [$from, $to])
            ->whereNotNull('l.product_id')
            ->select('l.product_id', DB::raw('SUM(l.quantity) as qty'), DB::raw('SUM(l.total_price) as revenue'))
            ->groupBy('l.product_id')
            ->orderByDesc('revenue')
            ->limit(5)
            ->get();

        $products = Product::whereIn('id', $rows->pluck('product_id'))->get()->keyBy('id');

        return $rows->map(function ($row) use ($products) {
            $row->product = $products->get($row->product_id);
            return $row;
        });
    }

    /** Sales vs purchases for the last 12 months (including the current one). */
    private function monthlyTrend(): array
    {
        $start = now()->startOfMonth()->subMonths(11);

        $sales = SaleInvoice::whereIn('status', self::BILLED)
            ->where('date', '>=', $start)
            ->selectRaw("DATE_FORMAT(date, '%Y-%m') as ym, SUM(total) as total")
            ->groupBy('ym')->pluck('total', 'ym');

        $purchases = PurchaseInvoice::whereNotNull('validated_at')
            ->where('date', '>=', $start)
            ->selectRaw("DATE_FORMAT(date, '%Y-%m') as ym, SUM(total) as total")
            ->groupBy('ym')->pluck('total', 'ym');

        $labels = $salesData = $purchaseData = [];
        for ($i = 0; $i < 12; $i++) {
            $month = $start->copy()->addMonths($i);
            $key = $month->format('Y-m');
            $labels[] = $month->format('M y');
            $salesData[] = round((float) ($sales[$key] ?? 0), 2);
            $purchaseData[] = round((float) ($purchases[$key] ?? 0), 2);
        }

        return ['labels' => $labels, 'sales' => $salesData, 'purchases' => $purchaseData];
    }
}
