<?php

namespace App\Http\Middleware;

use App\Models\VisitorLog;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Jenssegers\Agent\Agent;
use Symfony\Component\HttpFoundation\Response;

class TrackVisitor
{
    private array $skip = [
        '/api/',
        '/admin',
        '/_',
        '/storage/',
        '/favicon',
        '/robots',
        '/sitemap',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (
            $request->isMethod('GET') &&
            ! $request->expectsJson() &&
            ! $this->shouldSkip($request)
        ) {
            $this->log($request);
        }

        return $response;
    }

    private function shouldSkip(Request $request): bool
    {
        $path = '/' . ltrim($request->path(), '/');
        foreach ($this->skip as $prefix) {
            if (str_starts_with($path, $prefix)) {
                return true;
            }
        }
        return false;
    }

    private function log(Request $request): void
    {
        try {
            $sessionId = $this->getSessionId($request);
            $ip        = $request->ip();
            $ua        = $request->userAgent() ?? '';

            $agent = new Agent();
            $agent->setUserAgent($ua);

            [$browser, $browserVersion] = $this->parseBrowser($agent);
            [$os]                       = $this->parseOs($agent);
            [$deviceType, $deviceName]  = $this->parseDevice($agent);

            $referrer       = $request->headers->get('referrer', '');
            $referrerSource = $this->parseReferrerSource($referrer, $request->getHost());

            $geo = $this->getGeo($ip);

            $newVisitorKey = 'visitor_new_' . $sessionId;
            $isNew         = ! Cache::has($newVisitorKey);
            if ($isNew) {
                Cache::put($newVisitorKey, true, now()->addDays(30));
            }

            VisitorLog::create([
                'session_id'      => $sessionId,
                'ip_address'      => $ip,
                'user_id'         => Auth::id(),
                'page'            => '/' . ltrim($request->path(), '/'),
                'page_title'      => $request->query('_title'), // optional hint
                'referrer'        => $referrer ?: null,
                'referrer_source' => $referrerSource,
                'user_agent'      => substr($ua, 0, 500),
                'browser'         => $browser,
                'browser_version' => $browserVersion,
                'os'              => $os,
                'device_type'     => $deviceType,
                'device_name'     => $deviceName,
                'country'         => $geo['country'] ?? null,
                'country_code'    => $geo['country_code'] ?? null,
                'city'            => $geo['city'] ?? null,
                'region'          => $geo['region'] ?? null,
                'latitude'        => $geo['lat'] ?? null,
                'longitude'       => $geo['lon'] ?? null,
                'is_new_visitor'  => $isNew,
                'visited_at'      => now(),
            ]);
        } catch (\Throwable $e) {
            logger()->warning('VisitorLog failed: ' . $e->getMessage());
        }
    }

    private function getSessionId(Request $request): string
    {
        if ($request->hasCookie('_vid')) {
            return $request->cookie('_vid');
        }
        return hash('sha256', $request->ip() . $request->userAgent());
    }

    private function parseBrowser(Agent $agent): array
    {
        $browser = $agent->browser() ?: 'Unknown';
        $version = $agent->version($browser) ?: null;
        return [$browser, $version ? substr($version, 0, 10) : null];
    }

    private function parseOs(Agent $agent): array
    {
        return [$agent->platform() ?: 'Unknown'];
    }

    private function parseDevice(Agent $agent): array
    {
        $type = 'desktop';
        if ($agent->isMobile())     $type = 'mobile';
        elseif ($agent->isTablet()) $type = 'tablet';

        $device = $agent->device();
        return [$type, ($device && $device !== 'WebKit') ? $device : null];
    }

    private function parseReferrerSource(string $referrer, string $host): ?string
    {
        if (empty($referrer)) return 'direct';

        $parts = parse_url($referrer);
        $refHost = $parts['host'] ?? '';

        if (str_contains($refHost, $host)) return 'internal';

        $map = [
            'google'    => 'google',
            'bing'      => 'bing',
            'yahoo'     => 'yahoo',
            'duckduck'  => 'duckduckgo',
            'facebook'  => 'facebook',
            'instagram' => 'instagram',
            'twitter'   => 'twitter',
            'tiktok'    => 'tiktok',
            'youtube'   => 'youtube',
            'whatsapp'  => 'whatsapp',
            'shopee'    => 'shopee',
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

        return Cache::remember("geo_{$ip}", now()->addHours(24), function () use ($ip) {
            try {
                $res  = \Illuminate\Support\Facades\Http::timeout(3)->get("http://ip-api.com/json/{$ip}?fields=status,country,countryCode,regionName,city,lat,lon");
                $data = $res->json();
                if (($data['status'] ?? '') === 'success') {
                    return [
                        'country'      => $data['country']     ?? null,
                        'country_code' => $data['countryCode'] ?? null,
                        'region'       => $data['regionName']  ?? null,
                        'city'         => $data['city']        ?? null,
                        'lat'          => $data['lat']         ?? null,
                        'lon'          => $data['lon']         ?? null,
                    ];
                }
            } catch (\Throwable) {}
            return [];
        });
    }
}