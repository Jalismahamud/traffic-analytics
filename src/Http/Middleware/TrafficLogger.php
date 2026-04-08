<?php

namespace Jalismahamud\TrafficAnalytics\Http\Middleware;

use App\Models\TrafficLog;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TrafficLogger
{
    private const SKIP_EXTENSIONS = ['css', 'js', 'png', 'jpg', 'jpeg', 'gif', 'svg', 'ico', 'woff', 'woff2', 'ttf', 'map'];
    private const SKIP_PREFIXES   = ['_debugbar', 'telescope', 'horizon', 'livewire'];

    public function handle(Request $request, Closure $next): Response
    {
        $start = microtime(true);

        $response = $next($request);

        $this->log($request, $response, $start);

        return $response;
    }

    private function log(Request $request, Response $response, float $start): void
    {
        try {
            $path = $request->path();

            if ($this->shouldSkip($path)) {
                return;
            }

            TrafficLog::insert([
                'url'           => substr($request->fullUrl(), 0, 2000),
                'method'        => $request->method(),
                'ip_address'    => $request->ip(),
                'status_code'   => $response->getStatusCode(),
                'response_time' => round((microtime(true) - $start) * 1000, 3),
                'user_id'       => $request->user()?->id,
                'user_agent'    => substr($request->userAgent() ?? '', 0, 1000),
                'referrer'      => substr($request->header('referer') ?? '', 0, 1000) ?: null,
                'created_at'    => now(),
            ]);
        } catch (\Throwable) {
        }
    }

    private function shouldSkip(string $path): bool
    {
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        if (in_array($ext, self::SKIP_EXTENSIONS, true)) {
            return true;
        }

        foreach (self::SKIP_PREFIXES as $prefix) {
            if (str_starts_with($path, $prefix)) {
                return true;
            }
        }

        return false;
    }
}
