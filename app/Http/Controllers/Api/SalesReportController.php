<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Exports\SalesReportExport;
use Maatwebsite\Excel\Facades\Excel;

class SalesReportController extends Controller
{
    /**
     * Kolom yang boleh dipakai untuk grouping "Revenue per Lokasi".
     * Whitelist ketat supaya tidak ada risiko SQL injection lewat nama kolom.
     */
    private const LOCATION_COLUMNS = [
        'province' => 'province',
        'city'     => 'city',
        'district' => 'district',
    ];

    /**
     * GET /admin/sales-report
     */
    public function index(Request $request)
    {
        [$from, $to] = $this->resolveDateRange($request);
        $status        = $request->input('status', 'all');
        $groupBy       = $request->input('group_by') ?? $this->autoGroupBy($from, $to);
        $courier       = $request->input('courier');
        $locationLevel = $this->resolveLocationLevel($request->input('location_level'));

        // ── Base query ──────────────────────────────────────────────
        $base = Order::query()
            ->whereBetween('created_at', [$from->startOfDay(), $to->copy()->endOfDay()]);

        if ($status !== 'all') {
            $base->where('status', $status);
        }
        if ($courier) {
            $base->where('shipping_courier', $courier);
        }

        // ── Summary KPIs ─────────────────────────────────────────────
        $summary = (clone $base)->selectRaw("
            COUNT(*) as total_orders,
            SUM(CASE WHEN status='success'   THEN 1 ELSE 0 END) as total_success,
            SUM(CASE WHEN status='pending'   THEN 1 ELSE 0 END) as total_pending,
            SUM(CASE WHEN status='cancelled' THEN 1 ELSE 0 END) as total_cancelled,
            SUM(CASE WHEN status='success'   THEN total_price ELSE 0 END) as total_revenue,
            SUM(CASE WHEN status='success'   THEN shipping_cost ELSE 0 END) as total_shipping,
            AVG(CASE WHEN status='success'   THEN total_price ELSE NULL END) as avg_order_value,
            SUM(CASE WHEN status='success'   THEN subtotal ELSE 0 END) as total_subtotal
        ")->first();

        // ── Compare with previous period ─────────────────────────────
        $diffDays  = $from->diffInDays($to) + 1;
        $prevFrom  = $from->copy()->subDays($diffDays);
        $prevTo    = $to->copy()->subDays($diffDays);

        $prevRevenue = Order::where('status', 'success')
            ->whereBetween('created_at', [$prevFrom->startOfDay(), $prevTo->copy()->endOfDay()])
            ->sum('total_price');

        $prevOrders = Order::whereBetween('created_at', [$prevFrom->startOfDay(), $prevTo->copy()->endOfDay()])
            ->count();

        $revenueGrowth = $prevRevenue > 0
            ? round((($summary->total_revenue - $prevRevenue) / $prevRevenue) * 100, 1)
            : null;

        $ordersGrowth = $prevOrders > 0
            ? round((($summary->total_orders - $prevOrders) / $prevOrders) * 100, 1)
            : null;

        // ── Revenue & Order Trend ─────────────────────────────────────
        $trend = $this->buildTrend(clone $base, $groupBy, $status, $courier, $from, $to);

        // ── Top Products ──────────────────────────────────────────────
        $topProducts = OrderItem::query()
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->whereBetween('orders.created_at', [$from->startOfDay(), $to->copy()->endOfDay()])
            ->when($status !== 'all', fn($q) => $q->where('orders.status', $status))
            ->when($courier, fn($q) => $q->where('orders.shipping_courier', $courier))
            ->where('orders.status', 'success')
            ->selectRaw("
                COALESCE(NULLIF(order_items.product_name, ''), 'Produk Tanpa Nama') as product,
                SUM(order_items.qty) as total_qty,
                SUM(order_items.subtotal) as total_revenue,
                COUNT(DISTINCT order_items.order_id) as total_orders
            ")
            ->groupBy('order_items.product_name')
            ->orderByDesc('total_revenue')
            ->limit(10)
            ->get();

        // ── Top Couriers ──────────────────────────────────────────────
        $topCouriers = (clone $base)
            ->where('status', 'success')
            ->selectRaw("
                COALESCE(NULLIF(shipping_courier, ''), 'Tidak Diketahui') as courier,
                COUNT(*) as total_orders,
                SUM(total_price) as total_revenue,
                SUM(shipping_cost) as total_shipping
            ")
            ->groupBy('shipping_courier')
            ->orderByDesc('total_revenue')
            ->limit(8)
            ->get();

        // ── Status Distribution ───────────────────────────────────────
        $statusDist = (clone $base)
            ->selectRaw('status, COUNT(*) as count, SUM(total_price) as revenue')
            ->groupBy('status')
            ->get()
            ->keyBy('status');

        // ── Revenue by Location (Provinsi / Kota / Kecamatan) ─────────
        $locationColumn = self::LOCATION_COLUMNS[$locationLevel];
        $byLocation = (clone $base)
            ->where('status', 'success')
            ->selectRaw("
                COALESCE(NULLIF({$locationColumn}, ''), 'Tidak Diketahui') as location,
                COUNT(*) as total_orders,
                SUM(total_price) as total_revenue
            ")
            ->groupBy($locationColumn)
            ->orderByDesc('total_revenue')
            ->limit(10)
            ->get();

        // ── Hourly Heatmap (order counts per hour, per day-of-week) ──
        $heatmap = $this->buildHeatmap($from, $to, $status, $courier);

        // ── Recent Orders ─────────────────────────────────────────────
        $recentOrders = (clone $base)
            ->with('items')
            ->orderByDesc('created_at')
            ->limit(20)
            ->get(['id', 'invoice_number', 'customer_name', 'shipping_courier',
                   'total_price', 'status', 'created_at']);

        // ── Available couriers for filter ─────────────────────────────
        $courierList = Order::whereBetween('created_at', [$from->startOfDay(), $to->copy()->endOfDay()])
            ->whereNotNull('shipping_courier')
            ->distinct()
            ->orderBy('shipping_courier')
            ->pluck('shipping_courier');

        // ── CSV Export ────────────────────────────────────────────────
        if ($request->input('export') === 'csv') {
            return $this->exportCsv(clone $base);
        }

        return response()->json([
            'period' => [
                'from'     => $from->toDateString(),
                'to'       => $to->toDateString(),
                'label'    => $this->periodLabel($request->input('period', 'this_month')),
                'group_by' => $groupBy,
                'prev_from'=> $prevFrom->toDateString(),
                'prev_to'  => $prevTo->toDateString(),
            ],
            'summary' => [
                'total_orders'    => (int) $summary->total_orders,
                'total_success'   => (int) $summary->total_success,
                'total_pending'   => (int) $summary->total_pending,
                'total_cancelled' => (int) $summary->total_cancelled,
                'total_revenue'   => (float) $summary->total_revenue,
                'total_subtotal'  => (float) $summary->total_subtotal,
                'total_shipping'  => (float) $summary->total_shipping,
                'avg_order_value' => round((float) $summary->avg_order_value, 0),
                'success_rate'    => $summary->total_orders > 0
                    ? round(($summary->total_success / $summary->total_orders) * 100, 1)
                    : 0,
                'revenue_growth'  => $revenueGrowth,
                'orders_growth'   => $ordersGrowth,
                'prev_revenue'    => (float) $prevRevenue,
                'prev_orders'     => (int) $prevOrders,
            ],
            'trend'          => $trend,
            'top_products'   => $topProducts,
            'top_couriers'   => $topCouriers,
            'status_dist'    => $statusDist,
            'by_location'    => $byLocation,
            'location_level' => $locationLevel,
            'heatmap'        => $heatmap,
            'recent_orders'  => $recentOrders,
            'courier_list'   => $courierList,
        ]);
    }

    /**
     * Validasi & fallback level lokasi supaya tidak ada nama kolom liar
     * yang nyelip ke query mentah.
     */
    private function resolveLocationLevel(?string $level): string
    {
        return array_key_exists($level, self::LOCATION_COLUMNS) ? $level : 'province';
    }

    private function resolveDateRange(Request $request): array
    {
        $period = $request->input('period', 'this_month');
        $now    = Carbon::now();

        return match ($period) {
            'today'      => [$now->copy()->startOfDay(),   $now->copy()->endOfDay()],
            'yesterday'  => [$now->copy()->subDay()->startOfDay(), $now->copy()->subDay()->endOfDay()],
            'this_week'  => [$now->copy()->startOfWeek(),  $now->copy()->endOfWeek()],
            'last_week'  => [$now->copy()->subWeek()->startOfWeek(), $now->copy()->subWeek()->endOfWeek()],
            'last_month' => [$now->copy()->subMonth()->startOfMonth(), $now->copy()->subMonth()->endOfMonth()],
            'this_year'  => [$now->copy()->startOfYear(),  $now->copy()->endOfYear()],
            'last_7'     => [$now->copy()->subDays(6)->startOfDay(), $now->copy()->endOfDay()],
            'last_30'    => [$now->copy()->subDays(29)->startOfDay(), $now->copy()->endOfDay()],
            'last_90'    => [$now->copy()->subDays(89)->startOfDay(), $now->copy()->endOfDay()],
            'custom'     => [
                Carbon::parse($request->input('date_from'))->startOfDay(),
                Carbon::parse($request->input('date_to'))->endOfDay(),
            ],
            default      => [$now->copy()->startOfMonth(), $now->copy()->endOfMonth()], // this_month
        };
    }

    private function autoGroupBy(Carbon $from, Carbon $to): string
    {
        $days = $from->diffInDays($to);
        if ($days <= 1)   return 'hour';
        if ($days <= 60)  return 'day';
        if ($days <= 180) return 'week';
        return 'month';
    }

    private function periodLabel(string $period): string
    {
        return match ($period) {
            'today'      => 'Hari Ini',
            'yesterday'  => 'Kemarin',
            'this_week'  => 'Minggu Ini',
            'last_week'  => 'Minggu Lalu',
            'this_month' => 'Bulan Ini',
            'last_month' => 'Bulan Lalu',
            'this_year'  => 'Tahun Ini',
            'last_7'     => '7 Hari Terakhir',
            'last_30'    => '30 Hari Terakhir',
            'last_90'    => '90 Hari Terakhir',
            'custom'     => 'Custom Range',
            default      => 'Bulan Ini',
        };
    }

    private function buildTrend($base, string $groupBy, string $status, ?string $courier, Carbon $from, Carbon $to): array
    {
        $format = match ($groupBy) {
            'hour'  => '%Y-%m-%d %H:00',
            'day'   => '%Y-%m-%d',
            'week'  => '%x-%v',   // ISO year-week
            default => '%Y-%m',
        };

        $rows = Order::query()
            ->whereBetween('created_at', [$from->startOfDay(), $to->copy()->endOfDay()])
            ->when($status !== 'all', fn($q) => $q->where('status', $status))
            ->when($courier, fn($q) => $q->where('shipping_courier', $courier))
            ->selectRaw("
                DATE_FORMAT(created_at, '{$format}') as period,
                COUNT(*) as total_orders,
                SUM(CASE WHEN status='success' THEN total_price ELSE 0 END) as revenue,
                SUM(CASE WHEN status='success' THEN 1 ELSE 0 END) as success_count,
                SUM(CASE WHEN status='pending' THEN 1 ELSE 0 END) as pending_count,
                SUM(CASE WHEN status='cancelled' THEN 1 ELSE 0 END) as cancelled_count
            ")
            ->groupBy('period')
            ->orderBy('period')
            ->get();

        return $rows->map(fn($r) => [
            'period'          => $r->period,
            'total_orders'    => (int) $r->total_orders,
            'revenue'         => (float) $r->revenue,
            'success_count'   => (int) $r->success_count,
            'pending_count'   => (int) $r->pending_count,
            'cancelled_count' => (int) $r->cancelled_count,
        ])->values()->toArray();
    }

    private function buildHeatmap(Carbon $from, Carbon $to, string $status, ?string $courier): array
    {
        $rows = Order::query()
            ->whereBetween('created_at', [$from->startOfDay(), $to->copy()->endOfDay()])
            ->when($status !== 'all', fn($q) => $q->where('status', $status))
            ->when($courier, fn($q) => $q->where('shipping_courier', $courier))
            ->selectRaw('DAYOFWEEK(created_at) as dow, HOUR(created_at) as hour, COUNT(*) as count')
            ->groupBy('dow', 'hour')
            ->get();

        // dow: 1=Sun … 7=Sat; we remap to 0=Mon … 6=Sun
        $days = ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'];
        $matrix = [];
        foreach ($days as $d) {
            $matrix[$d] = array_fill(0, 24, 0);
        }
        foreach ($rows as $r) {
            $idx = ($r->dow + 5) % 7; // shift so Mon=0
            $matrix[$days[$idx]][$r->hour] = (int) $r->count;
        }

        return collect($matrix)->map(fn($hours, $day) => [
            'day'   => $day,
            'hours' => $hours,
        ])->values()->toArray();
    }

    private function exportCsv($query)
    {
        $orders = $query->with('items')
            ->orderByDesc('created_at')
            ->get();

        $rows   = [];
        $rows[] = implode(',', [
            'Invoice', 'Customer', 'Phone', 'Email', 'Courier',
            'Status', 'Subtotal', 'Shipping Cost', 'Total', 'Date',
        ]);

        foreach ($orders as $o) {
            $rows[] = implode(',', [
                $o->invoice_number,
                '"' . str_replace('"', '""', $o->customer_name) . '"',
                $o->customer_phone,
                $o->customer_email,
                $o->shipping_courier,
                $o->status,
                $o->subtotal,
                $o->shipping_cost,
                $o->total_price,
                $o->created_at->format('Y-m-d H:i:s'),
            ]);
        }

        $filename = 'sales-report-' . now()->format('Ymd-His') . '.csv';
        return response(implode("\n", $rows), 200, [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename={$filename}",
        ]);
    }

    public function exportExcel(Request $request)
    {
        [$from, $to] = $this->resolveDateRange($request);
        $status  = $request->input('status', 'all');
        $groupBy = $request->input('group_by') ?? $this->autoGroupBy($from, $to);
        $courier = $request->input('courier');

        $base = Order::query()
            ->whereBetween('created_at', [$from->startOfDay(), $to->copy()->endOfDay()])
            ->when($status !== 'all', fn($q) => $q->where('status', $status))
            ->when($courier, fn($q) => $q->where('shipping_courier', $courier));

        // Summary KPI
        $summaryRaw = (clone $base)->selectRaw("
            COUNT(*) as total_orders,
            SUM(CASE WHEN status='success'   THEN 1 ELSE 0 END) as total_success,
            SUM(CASE WHEN status='pending'   THEN 1 ELSE 0 END) as total_pending,
            SUM(CASE WHEN status='cancelled' THEN 1 ELSE 0 END) as total_cancelled,
            SUM(CASE WHEN status='success'   THEN total_price ELSE 0 END) as total_revenue,
            SUM(CASE WHEN status='success'   THEN shipping_cost ELSE 0 END) as total_shipping,
            AVG(CASE WHEN status='success'   THEN total_price ELSE NULL END) as avg_order_value,
            SUM(CASE WHEN status='success'   THEN subtotal ELSE 0 END) as total_subtotal
        ")->first();

        $diffDays = $from->diffInDays($to) + 1;
        $prevFrom = $from->copy()->subDays($diffDays);
        $prevTo   = $to->copy()->subDays($diffDays);
        
        $prevRevenue = Order::where('status', 'success')
            ->whereBetween('created_at', [$prevFrom->startOfDay(), $prevTo->copy()->endOfDay()])
            ->sum('total_price');
        $prevOrders = Order::whereBetween('created_at', [$prevFrom->startOfDay(), $prevTo->copy()->endOfDay()])
            ->count();

        $summary = [
            'total_orders'    => (int) $summaryRaw->total_orders,
            'total_success'   => (int) $summaryRaw->total_success,
            'total_pending'   => (int) $summaryRaw->total_pending,
            'total_cancelled' => (int) $summaryRaw->total_cancelled,
            'total_revenue'   => (float) $summaryRaw->total_revenue,
            'total_subtotal'  => (float) $summaryRaw->total_subtotal,
            'total_shipping'  => (float) $summaryRaw->total_shipping,
            'avg_order_value' => round((float) $summaryRaw->avg_order_value, 0),
            'revenue_growth'  => $prevRevenue > 0 ? round((($summaryRaw->total_revenue - $prevRevenue) / $prevRevenue) * 100, 1) : null,
            'orders_growth'   => $prevOrders > 0 ? round((($summaryRaw->total_orders - $prevOrders) / $prevOrders) * 100, 1) : null,
            'prev_revenue'    => (float) $prevRevenue,
            'prev_orders'     => (float) $prevOrders,
        ];

        $period = [
            'from'      => $from->toDateString(),
            'to'        => $to->toDateString(),
            'label'     => $this->periodLabel($request->input('period', 'this_month')),
            'prev_from' => $prevFrom->toDateString(),
            'prev_to'   => $prevTo->toDateString(),
        ];

        $trend = collect($this->buildTrend(clone $base, $groupBy, $status, $courier, $from, $to));

        $topProducts = OrderItem::query()
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->whereBetween('orders.created_at', [$from->startOfDay(), $to->copy()->endOfDay()])
            ->when($status !== 'all', fn($q) => $q->where('orders.status', $status))
            ->when($courier, fn($q) => $q->where('orders.shipping_courier', $courier))
            ->where('orders.status', 'success')
            ->selectRaw("
                COALESCE(NULLIF(order_items.product_name, ''), 'Produk Tanpa Nama') as product,
                SUM(order_items.qty) as total_qty,
                SUM(order_items.subtotal) as total_revenue,
                COUNT(DISTINCT order_items.order_id) as total_orders
            ")
            ->groupBy('order_items.product_name')
            ->orderByDesc('total_revenue')
            ->limit(10)
            ->get();
        
        $orders = (clone $base)
            ->orderByDesc('created_at')
            ->get(['id', 'invoice_number', 'customer_name', 'shipping_courier',
                    'subtotal', 'shipping_cost', 'total_price', 'status', 'created_at']);
        
        $filename = 'sales-report-' . $from->format('Ymd') . '-' . $to->format('Ymd') . '.xlsx';

        return Excel::download(
            new SalesReportExport($summary, $period, $trend, $topProducts, $orders),
            $filename
        );
    }
}