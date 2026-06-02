<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;

class ProductReportController extends Controller
{
    /**
     * GET /api/product-report
     *
     * Joins: order_items → orders, order_items → products (for category/stock)
     * Falls back gracefully when products table has no category column.
     */
    public function index(Request $request)
    {
        [$from, $to] = $this->resolveDateRange($request);
        $status   = $request->input('status', 'all');
        $sortBy   = $request->input('sort_by', 'revenue');
        $category = $request->input('category');

        $orderCol = match ($sortBy) {
            'qty'    => 'total_qty',
            'orders' => 'total_orders',
            default  => 'total_revenue',
        };

        // ── All products aggregated from order_items ──────────────────
        // Left join products so we get category/stock even if column optional
        $allProducts = OrderItem::query()
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->leftJoin('products', 'order_items.product_id', '=', 'products.id')
            ->whereBetween('orders.created_at', [$from->startOfDay(), $to->copy()->endOfDay()])
            ->when($status === 'all',
                fn($q) => $q->where('orders.status', 'success'), // default: hanya success
                fn($q) => $q->where('orders.status', $status)    // kalau pilih specific
            )
            ->when($category, fn($q) => $q->where('products.category', $category))
            ->selectRaw('
                order_items.product_name,
                MAX(products.category) as category,
                SUM(order_items.qty)   as total_qty,
                SUM(order_items.subtotal) as total_revenue,
                COUNT(DISTINCT order_items.order_id) as total_orders
            ')
            ->groupBy('order_items.product_name')
            ->orderByDesc($orderCol)
            ->get();

        // ── Top 10 ────────────────────────────────────────────────────
        $topProducts = $allProducts->take(10)->values();

        // ── Slow moving — bottom 10 (has at least 1 sale) ────────────
        $slowMoving = $allProducts
            ->where('total_revenue', '>', 0)
            ->sortBy('total_revenue')
            ->take(10)
            ->values();

        // ── By Category ───────────────────────────────────────────────
        $byCategoryRaw = OrderItem::query()
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->leftJoin('products', 'order_items.product_id', '=', 'products.id')
            ->whereBetween('orders.created_at', [$from->startOfDay(), $to->copy()->endOfDay()])
            ->where('orders.status', 'success')
            ->when($status !== 'all', fn($q) => $q->where('orders.status', $status))
            ->selectRaw("
                COALESCE(NULLIF(products.category, ''), 'Lainnya') as category,
                SUM(order_items.subtotal) as total_revenue,
                SUM(order_items.qty)      as total_qty,
                COUNT(DISTINCT order_items.order_id) as total_orders
            ")
            ->groupBy('category')
            ->orderByDesc('total_revenue')
            ->get();

        $totalCatRevenue = $byCategoryRaw->sum('total_revenue') ?: 1;
        $byCategory = $byCategoryRaw->map(fn($c) => [
            'category'      => $c->category,
            'total_revenue' => (float) $c->total_revenue,
            'total_qty'     => (int)   $c->total_qty,
            'total_orders'  => (int)   $c->total_orders,
            'pct'           => round(($c->total_revenue / $totalCatRevenue) * 100, 1),
        ])->values();

        // ── Low stock from products table ─────────────────────────────
        // Note: querying without stock filter since 'stock' column not confirmed
        $lowStock = collect();
        try {
            $lowStock = \App\Models\ProductVariant::query()
                ->join('products', 'product_variants.product_id', '=', 'products.id')
                ->whereColumn('product_variants.stock_qty', '<=', 'product_variants.low_stock_alert')
                ->where('products.track_inventory', 1)
                ->where('product_variants.is_active', 1)
                ->orderBy('product_variants.stock_qty')
                ->get([
                    'product_variants.id',
                    'products.name as product_name',
                    'products.category',
                    'product_variants.label as variant_label',
                    'product_variants.sku',
                    'product_variants.stock_qty',
                    'product_variants.low_stock_alert',
                ])
                ->map(function ($pv) use ($allProducts) {
                    // Match by product name (karena order_items simpan product_name)
                    $sold = $allProducts->firstWhere('product_name', $pv->product_name);
                    
                    // Label tampilan: "Produk - Varian" atau hanya "Produk"
                    $displayName = $pv->product_name;
                    if ($pv->variant_label) {
                        $displayName .= ' - ' . $pv->variant_label;
                    }

                    return [
                        'id'              => $pv->id,
                        'product_name'    => $displayName,
                        'category'        => $pv->category,
                        'sku'             => $pv->sku,
                        'stock'           => (int) $pv->stock_qty,
                        'low_stock_alert' => (int) $pv->low_stock_alert,
                        'total_qty_sold'  => $sold ? (int) $sold->total_qty : 0,
                    ];
                })->values();
        } catch (\Exception $e) {
            \Log::error('Low stock query failed: ' . $e->getMessage());
            $lowStock = collect();
        }

        // ── Summary KPIs ──────────────────────────────────────────────
        $totalRevenue = (float) $allProducts->sum('total_revenue');
        $totalQty     = (int)   $allProducts->sum('total_qty');
        $totalOrders  = (int)   $allProducts->sum('total_orders');
        $topProduct   = $allProducts->first();

        // Compare with previous period
        $diffDays = max($from->diffInDays($to), 1);
        $prevFrom = $from->copy()->subDays($diffDays);
        $prevTo   = $to->copy()->subDays($diffDays);

        $prevRevenue = OrderItem::query()
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('orders.status', 'success')
            ->whereBetween('orders.created_at', [$prevFrom->startOfDay(), $prevTo->copy()->endOfDay()])
            ->sum('order_items.subtotal');

        $prevQty = OrderItem::query()
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('orders.status', 'success')
            ->whereBetween('orders.created_at', [$prevFrom->startOfDay(), $prevTo->copy()->endOfDay()])
            ->sum('order_items.qty');

        $summary = [
            'total_revenue'           => $totalRevenue,
            'total_qty'               => $totalQty,
            'total_orders'            => $totalOrders,
            'total_products'          => $allProducts->count(),
            'total_categories'        => $byCategory->count(),
            'avg_revenue_per_product' => $allProducts->count() > 0
                ? round($totalRevenue / $allProducts->count(), 0) : 0,
            'top_product_name'        => $topProduct?->product_name ?? null,
            'top_product_revenue'     => (float) ($topProduct?->total_revenue ?? 0),
            'low_stock_count'         => $lowStock->count(), // stock_qty <= low_stock_alert
            'slow_moving_count'       => $slowMoving->count(),
            'revenue_growth'          => $prevRevenue > 0
                ? round((($totalRevenue - $prevRevenue) / $prevRevenue) * 100, 1) : null,
            'qty_growth'              => $prevQty > 0
                ? round((($totalQty - $prevQty) / $prevQty) * 100, 1) : null,
        ];

        // ── Trend: top 5 products over time ──────────────────────────
        [$trendLabels, $trendByProduct] = $this->buildProductTrend(
            $from, $to, $status, $category, $topProducts->take(5)
        );

        // ── Available categories for filter ──────────────────────────
        $categoryList = Product::whereNotNull('category')
            ->where('category', '!=', '')
            ->distinct()
            ->orderBy('category')
            ->pluck('category');

        // ── CSV export ────────────────────────────────────────────────
        if ($request->input('export') === 'csv') {
            return $this->exportCsv($allProducts);
        }

        return response()->json([
            'period' => [
                'from'  => $from->toDateString(),
                'to'    => $to->toDateString(),
                'label' => $this->periodLabel($request->input('period', 'this_month')),
            ],
            'summary'          => $summary,
            'top_products'     => $topProducts,
            'all_products'     => $allProducts->values(),
            'by_category'      => $byCategory,
            'slow_moving'      => $slowMoving,
            'low_stock'        => $lowStock,
            'trend_labels'     => $trendLabels,
            'trend_by_product' => $trendByProduct,
            'category_list'    => $categoryList,
        ]);
    }

    // ──────────────────────────────────────────────────────────────────
    // Helpers
    // ──────────────────────────────────────────────────────────────────

    private function resolveDateRange(Request $request): array
    {
        $period = $request->input('period', 'this_month');
        $now    = Carbon::now();

        return match ($period) {
            'today'      => [$now->copy()->startOfDay(),   $now->copy()->endOfDay()],
            'yesterday'  => [$now->copy()->subDay()->startOfDay(), $now->copy()->subDay()->endOfDay()],
            'last_month' => [$now->copy()->subMonth()->startOfMonth(), $now->copy()->subMonth()->endOfMonth()],
            'this_year'  => [$now->copy()->startOfYear(),  $now->copy()->endOfYear()],
            'last_7'     => [$now->copy()->subDays(6)->startOfDay(),  $now->copy()->endOfDay()],
            'last_30'    => [$now->copy()->subDays(29)->startOfDay(), $now->copy()->endOfDay()],
            'last_90'    => [$now->copy()->subDays(89)->startOfDay(), $now->copy()->endOfDay()],
            'custom'     => [
                Carbon::parse($request->input('date_from'))->startOfDay(),
                Carbon::parse($request->input('date_to'))->endOfDay(),
            ],
            default      => [$now->copy()->startOfMonth(), $now->copy()->endOfMonth()],
        };
    }

    private function periodLabel(string $period): string
    {
        return match ($period) {
            'today'      => 'Hari Ini',
            'yesterday'  => 'Kemarin',
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

    private function buildProductTrend(Carbon $from, Carbon $to, string $status, ?string $category, $topProducts): array
    {
        if ($topProducts->isEmpty()) return [[], []];

        $diffDays = $from->diffInDays($to);
        $format   = $diffDays <= 1 ? '%Y-%m-%d %H:00'
                  : ($diffDays <= 60 ? '%Y-%m-%d' : '%Y-%m');

        $productNames = $topProducts->pluck('product_name')->toArray();

        $rows = OrderItem::query()
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->whereBetween('orders.created_at', [$from->startOfDay(), $to->copy()->endOfDay()])
            ->where('orders.status', 'success')
            ->when($status !== 'all', fn($q) => $q->where('orders.status', $status))
            ->whereIn('order_items.product_name', $productNames)
            ->selectRaw("
                DATE_FORMAT(orders.created_at, '{$format}') as period,
                order_items.product_name,
                SUM(order_items.subtotal) as revenue
            ")
            ->groupBy('period', 'order_items.product_name')
            ->orderBy('period')
            ->get();

        $labels    = $rows->pluck('period')->unique()->sort()->values()->toArray();
        $byProduct = [];

        foreach ($productNames as $name) {
            $data = array_map(function ($label) use ($rows, $name) {
                $row = $rows->first(fn($r) => $r->period === $label && $r->product_name === $name);
                return $row ? (float) $row->revenue : 0;
            }, $labels);
            $byProduct[] = ['product_name' => $name, 'data' => $data];
        }

        return [$labels, $byProduct];
    }

    private function exportCsv($products)
    {
        $rows   = [];
        $rows[] = implode(',', ['Produk', 'Kategori', 'Qty Terjual', 'Jumlah Order', 'Total Revenue']);

        foreach ($products as $p) {
            $rows[] = implode(',', [
                '"' . str_replace('"', '""', $p->product_name) . '"',
                '"' . str_replace('"', '""', $p->category ?? '') . '"',
                $p->total_qty,
                $p->total_orders,
                $p->total_revenue,
            ]);
        }

        $filename = 'product-report-' . now()->format('Ymd-His') . '.csv';
        return response(implode("\n", $rows), 200, [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename={$filename}",
        ]);
    }

    public function exportExcel(Request $request)
    {
        [$from, $to] = $this->resolveDateRange($request);
        $status  = $request->input('status', 'all');
        $sortBy  = $request->input('sort_by', 'revenue');
        $category = $request->input('category');

        $orderCol = match ($sortBy) {
            'qty'    => 'total_qty',
            'orders' => 'total_orders',
            default  => 'total_revenue',
        };

        $products = OrderItem::query()
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->leftJoin('products', 'order_items.product_id', '=', 'products.id')
            ->whereBetween('orders.created_at', [$from->startOfDay(), $to->copy()->endOfDay()])
            ->where('orders.status', 'success')
            ->when($status !== 'all', fn($q) => $q->where('orders.status', $status))
            ->when($category, fn($q) => $q->where('products.category', $category))
            ->selectRaw('
                order_items.product_name,
                MAX(products.category) as category,
                SUM(order_items.qty)   as total_qty,
                SUM(order_items.subtotal) as total_revenue,
                COUNT(DISTINCT order_items.order_id) as total_orders
            ')
            ->groupBy('order_items.product_name')
            ->orderByDesc($orderCol)
            ->get();

        $period = [
            'from'  => $from->toDateString(),
            'to'    => $to->toDateString(),
            'label' => $this->periodLabel($request->input('period', 'this_month')),
        ];

        $filename = 'product-report-' . $from->format('Ymd') . '-' . $to->format('Ymd') . '.xlsx';

        return Excel::download(
            new \App\Exports\ProductReportExport($products, $period),
            $filename
        );
    }
}