<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\VisitorLog;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class VisitorStatsController extends Controller
{
    public function index(Request $request)
    {
        [$from, $to] = $this->parsePeriod($request);

        $base = fn() => VisitorLog::inRange($from, $to);

        $totalViews     = $base()->count();
        $uniqueSessions = $base()->distinct('session_id')->count('session_id');
        $newVisitors    = $base()->where('is_new_visitor', true)->distinct('session_id')->count('session_id');
        $returningV     = max(0, $uniqueSessions - $newVisitors);
        $avgTime        = (int) $base()->whereNotNull('time_on_page')->avg('time_on_page');
        $bounceCount    = $base()->where('is_bounce', true)->distinct('session_id')->count('session_id');
        $bounceRate     = $uniqueSessions > 0 ? round(($bounceCount / $uniqueSessions) * 100, 1) : 0;

        $diff = $to->diffInSeconds($from);
        $prevForm = (clone $from)->subSeconds($diff);
        $prevTo   = clone $from;
        $prevViews  = VisitorLog::inRange($prevForm, $prevTo)->count();
        $prevUnique = VisitorLog::inRange($prevForm, $prevTo)->distinct('session_id')->count('session_id');

        $viewDelta   = $prevViews > 0   ? round((($totalViews - $prevViews) / $prevViews) * 100, 1)     : null;
        $uniqueDelta = $prevUnique > 0  ? round((($uniqueSessions - $prevUnique) / $prevUnique) * 100, 1) : null;

        $dailyTrend = $base()
            ->select(
                DB::raw('DATE(visited_at) as date'),
                DB::raw('COUNT(*) as views'),
                DB::raw('COUNT(DISTINCT session_id) as unique_visitors')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $topPages = $base()
            ->select('page', DB::raw('COUNT(*) as views'), DB::raw('COUNT(DISTINCT session_id) as unique_visitors'))
            ->groupBy('page')
            ->orderByDesc('views')
            ->limit(10)
            ->get();

        $devices = $base()
            ->select('device_type', DB::raw('COUNT(*) as count'))
            ->whereNotNull('device_type')
            ->groupBy('device_type')
            ->orderByDesc('count')
            ->get();

        $browsers = $base()
            ->select('browser', DB::raw('COUNT(*) as count'))
            ->whereNotNull('browser')
            ->groupBy('browser')
            ->orderByDesc('count')
            ->limit(8)
            ->get();

        $os = $base()
            ->select('os', DB::raw('COUNT(*) as count'))
            ->whereNotNull('os')
            ->groupBy('os')
            ->orderByDesc('count')
            ->limit(8)
            ->get();

        $referrers = $base()
            ->select('referrer_source', DB::raw('COUNT(*) as count'), DB::raw('COUNT(DISTINCT session_id) as visitors'))
            ->whereNotNull('referrer_source')
            ->groupBy('referrer_source')
            ->orderByDesc('count')
            ->limit(10)
            ->get();

        $countries = $base()
            ->select('country', 'country_code', DB::raw('COUNT(*) as count'), DB::raw('COUNT(DISTINCT session_id) as visitors'))
            ->whereNotNull('country')
            ->groupBy('country', 'country_code')
            ->orderByDesc('count')
            ->limit(10)
            ->get();

        $cities = $base()
            ->select('city', 'region', 'country', DB::raw('COUNT(*) as count'))
            ->whereNotNull('city')
            ->groupBy('city', 'region', 'country')
            ->orderByDesc('count')
            ->limit(10)
            ->get();

        $hourlyRaw = $base()
            ->select(DB::raw('HOUR(visited_at) as hour'), DB::raw('COUNT(*) as count'))
            ->groupBy('hour')
            ->orderBy('hour')
            ->pluck('count', 'hour');

        $hourly = collect(range(0, 23))->map(fn($h) => [
            'hour'  => $h,
            'count' => $hourlyRaw[$h] ?? 0,
        ])->values();

        $newVsReturning = [
            ['label' => 'New',       'value' => $newVisitors],
            ['label' => 'Returning', 'value' => $returningV],
        ];

        $monthlyTrend = VisitorLog::select(
                DB::raw("DATE_FORMAT(visited_at, '%Y-%m') as month"),
                DB::raw('COUNT(*) as views'),
                DB::raw('COUNT(DISTINCT session_id) as unique_visitors')
            )
            ->where('visited_at', '>=', now()->subMonths(6)->startOfMonth())
            ->groupBy('month')
            ->orderBy('month')
            ->get();
 
        return response()->json([
            'period' => [
                'from'  => $from->toDateTimeString(),
                'to'    => $to->toDateTimeString(),
                'label' => $this->periodLabel($request),
            ],
            'overview' => [
                'total_views'      => $totalViews,
                'unique_visitors'  => $uniqueSessions,
                'new_visitors'     => $newVisitors,
                'returning'        => $returningV,
                'avg_time_on_page' => $avgTime,   // seconds
                'bounce_rate'      => $bounceRate,
                'view_delta'       => $viewDelta,
                'unique_delta'     => $uniqueDelta,
            ],
            'daily_trend'     => $dailyTrend,
            'monthly_trend'   => $monthlyTrend,
            'top_pages'       => $topPages,
            'devices'         => $devices,
            'browsers'        => $browsers,
            'os'              => $os,
            'referrers'       => $referrers,
            'countries'       => $countries,
            'cities'          => $cities,
            'hourly'          => $hourly,
            'new_vs_returning'=> $newVsReturning,
        ]);
    }

    public function ping(Request $request)
    {
        $validated = $request->validate([
            'page'       => 'nullable|string|max:500',
            'page_title' => 'nullable|string|max:255',
            'referrer'   => 'nullable|string|max:1000',
        ]);

        $ua     = $request->userAgent() ?? '';
        $agent  = new \Jenssegers\Agent\Agent();
        $agent->setUserAgent($ua);

        $browser = $agent->browser() ?: 'Unknown';
        $version = $agent->version($browser);

        $deviceType = 'desktop';
        if ($agent->isMobile())     $deviceType = 'mobile';
        elseif ($agent->isTablet()) $deviceType = 'tablet';

        $referrer       = $validated['referrer'] ?? '';
        $referrerSource = $this->parseReferrerSource($referrer, $request->getHost());
        $geo            = $this->getGeo($request->ip());

        $sessionId      = $request->cookie('_vid') ?? hash('sha256', $request->ip() . $ua);
        $newKey         = 'visitor_new_' . $sessionId;
        $isNew          = ! \Illuminate\Support\Facades\Cache::has($newKey);
        if ($isNew) \Illuminate\Support\Facades\Cache::put($newKey, true, now()->addDays(30));

        VisitorLog::create([
            'session_id'      => $sessionId,
            'ip_address'      => $request->ip(),
            'user_id'         => \Auth::id(),
            'page'            => $validated['page'] ?? '/',
            'page_title'      => $validated['page_title'] ?? null,
            'referrer'        => $referrer ?: null,
            'referrer_source' => $referrerSource,
            'user_agent'      => substr($ua, 0, 500),
            'browser'         => $browser,
            'browser_version' => $version ? substr($version, 0, 10) : null,
            'os'              => $agent->platform() ?: null,
            'device_type'     => $deviceType,
            'is_new_visitor'  => $isNew,
            'country'         => $geo['country'] ?? null,
            'country_code'    => $geo['country_code'] ?? null,
            'city'            => $geo['city'] ?? null,
            'region'          => $geo['region'] ?? null,
            'latitude'        => $geo['lat'] ?? null,
            'longitude'       => $geo['lon'] ?? null,
            'visited_at'      => now(),
        ]);

        return response()->json(['ok' => true]);
    }

    public function updateTime(Request $request)
    {
        $validated = $request->validate([
            'session_id' => 'required|string|max:64',
            'page'       => 'required|string|max:500',
            'seconds'    => 'required|integer|min:1|max:86400',
        ]);

        VisitorLog::where('session_id', $validated['session_id'])
            ->where('page', $validated['page'])
            ->latest('visited_at')
            ->limit(1)
            ->update([
                'time_on_page' => $validated['seconds'],
                'is_bounce'    => $validated['seconds'] < 10,
            ]);

        return response()->json(['ok' => true]);
    }

    private function parsePeriod(Request $request): array
    {
        $period = $request->query('period', '7d');

        return match ($period) {
            'today' => [Carbon::today(),                            Carbon::now()],
            '7d'    => [Carbon::now()->subDays(7)->startOfDay(),    Carbon::now()],
            '30d'   => [Carbon::now()->subDays(30)->startOfDay(),   Carbon::now()],
            'custom' => [
                Carbon::parse($request->query('from', now()->subDays(7)))->startOfDay(),
                Carbon::parse($request->query('to',   now()))->endOfDay(),
            ],
            default => [Carbon::now()->subDays(7)->startOfDay(), Carbon::now()],
        };
    }

    private function periodLabel(Request $request): string
    {
        return match ($request->query('period', '7d')) {
            'today'  => 'Hari Ini',
            '7d'     => '7 Hari Terakhir',
            '30d'    => '30 Hari Terakhir',
            'custom' => 'Custom Range',
            default  => '7 Hari Terakhir',
        };
    }

    private function parseReferrerSource(string $referrer, string $host): ?string
    {
        if (empty($referrer)) return 'direct';
        $refHost = parse_url($referrer, PHP_URL_HOST) ?? '';
        if (str_contains($refHost, $host)) return 'internal';
        $map = [
            'google' => 'google', 'bing' => 'bing', 'yahoo' => 'yahoo',
            'duckduck' => 'duckduckgo', 'facebook' => 'facebook',
            'instagram' => 'instagram', 'twitter' => 'twitter',
            'tiktok' => 'tiktok', 'youtube' => 'youtube',
            'whatsapp' => 'whatsapp', 'shopee' => 'shopee',
            'tokopedia' => 'tokopedia', 
        ];
        foreach ($map as $needle => $label) {
            if (str_contains($refHost, $needle)) return $label;
        }
        return $refHost ?: 'other';
    }

    private function getGeo(string $ip): array
    {
        if (! filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
            return [];
    }
    return \Illuminate\Support\Facades\Cache::remember("geo_{$ip}", now()->addHours(24), function () use ($ip) {
            try {
                $res  = \Illuminate\Support\Facades\Http::timeout(3)->get("http://ip-api.com/json/{$ip}?fields=status,country,countryCode,regionName,city,lat,lon");
                $data = $res->json();
                if (($data['status'] ?? '') === 'success') {
                    return [
                        'country' => $data['country'] ?? null, 'country_code' => $data['countryCode'] ?? null,
                        'region'  => $data['regionName'] ?? null, 'city' => $data['city'] ?? null,
                        'lat' => $data['lat'] ?? null, 'lon' => $data['lon'] ?? null,
                    ];
                }
            } catch (\Throwable) {}
            return [];
        });
    }
}
