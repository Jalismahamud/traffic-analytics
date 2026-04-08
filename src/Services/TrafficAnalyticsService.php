<?php

namespace App\Services;

use App\Models\TrafficLog;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class TrafficAnalyticsService
{
    private const CACHE_TTL = 60;

    public function getSummary(string $from, string $to): array
    {
        $cacheKey = "traffic_summary_{$from}_{$to}";

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($from, $to) {
            $base = TrafficLog::dateRange($from, $to);

            $total      = (clone $base)->count();
            $unique     = (clone $base)->distinct('ip_address')->count('ip_address');
            $avgTime    = (clone $base)->avg('response_time') ?? 0;
            $errors     = (clone $base)->errors()->count();
            $errorRate  = $total > 0 ? round(($errors / $total) * 100, 2) : 0;

            $todayTotal  = TrafficLog::today()->count();
            $todayUnique = TrafficLog::today()->distinct('ip_address')->count('ip_address');

            return [
                'total_requests'   => $total,
                'unique_visitors'  => $unique,
                'avg_response_time'=> round($avgTime, 2),
                'error_rate'       => $errorRate,
                'today_requests'   => $todayTotal,
                'today_unique'     => $todayUnique,
            ];
        });
    }

    public function getTopUrls(string $from, string $to, int $limit = 10): array
    {
        $cacheKey = "traffic_top_urls_{$from}_{$to}_{$limit}";

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($from, $to, $limit) {
            return TrafficLog::dateRange($from, $to)
                ->select(DB::raw('url, COUNT(*) as hits, AVG(response_time) as avg_time'))
                ->groupBy('url')
                ->orderByDesc('hits')
                ->limit($limit)
                ->get()
                ->map(fn($r) => [
                    'url'      => $this->shortenUrl($r->url),
                    'hits'     => $r->hits,
                    'avg_time' => round($r->avg_time, 2),
                ])
                ->toArray();
        });
    }

    public function getStatusDistribution(string $from, string $to): array
    {
        $cacheKey = "traffic_status_{$from}_{$to}";

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($from, $to) {
            $rows = TrafficLog::dateRange($from, $to)
                ->select(DB::raw('status_code, COUNT(*) as count'))
                ->groupBy('status_code')
                ->orderBy('status_code')
                ->get();

            $groups = ['2xx' => 0, '3xx' => 0, '4xx' => 0, '5xx' => 0];
            foreach ($rows as $row) {
                $key = floor($row->status_code / 100) . 'xx';
                if (isset($groups[$key])) {
                    $groups[$key] += $row->count;
                }
            }

            return $groups;
        });
    }

    public function getTrafficOverTime(string $from, string $to, string $groupBy = 'hour'): array
    {
        $cacheKey = "traffic_over_time_{$from}_{$to}_{$groupBy}";

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($from, $to, $groupBy) {
            $format = $groupBy === 'day' ? '%Y-%m-%d' : '%Y-%m-%d %H:00';

            return TrafficLog::dateRange($from, $to)
                ->select(DB::raw("DATE_FORMAT(created_at, '{$format}') as period, COUNT(*) as count, AVG(response_time) as avg_time"))
                ->groupBy('period')
                ->orderBy('period')
                ->get()
                ->map(fn($r) => [
                    'period'   => $r->period,
                    'count'    => $r->count,
                    'avg_time' => round($r->avg_time, 2),
                ])
                ->toArray();
        });
    }

    public function getTopIPs(string $from, string $to, int $limit = 10): array
    {
        $cacheKey = "traffic_top_ips_{$from}_{$to}_{$limit}";

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($from, $to, $limit) {
            return TrafficLog::dateRange($from, $to)
                ->select(DB::raw('ip_address, COUNT(*) as hits'))
                ->groupBy('ip_address')
                ->orderByDesc('hits')
                ->limit($limit)
                ->get()
                ->toArray();
        });
    }

    public function getMethodDistribution(string $from, string $to): array
    {
        $cacheKey = "traffic_methods_{$from}_{$to}";

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($from, $to) {
            return TrafficLog::dateRange($from, $to)
                ->select(DB::raw('method, COUNT(*) as count'))
                ->groupBy('method')
                ->orderByDesc('count')
                ->pluck('count', 'method')
                ->toArray();
        });
    }

    public function getRecentLogs(int $limit = 50): \Illuminate\Database\Eloquent\Collection
    {
        return TrafficLog::with('user:id,name')
            ->latest('created_at')
            ->limit($limit)
            ->get();
    }

    public function exportCsv(string $from, string $to): string
    {
        $rows = TrafficLog::dateRange($from, $to)
            ->select('url', 'method', 'ip_address', 'status_code', 'response_time', 'user_agent', 'referrer', 'created_at')
            ->orderByDesc('created_at')
            ->cursor();

        $lines   = [];
        $lines[] = implode(',', ['URL', 'Method', 'IP', 'Status', 'Response Time (ms)', 'User Agent', 'Referrer', 'Created At']);

        foreach ($rows as $row) {
            $lines[] = implode(',', [
                '"' . str_replace('"', '""', $row->url) . '"',
                $row->method,
                $row->ip_address,
                $row->status_code,
                $row->response_time,
                '"' . str_replace('"', '""', $row->user_agent ?? '') . '"',
                '"' . str_replace('"', '""', $row->referrer ?? '') . '"',
                $row->created_at,
            ]);
        }

        return implode("\n", $lines);
    }

    private function shortenUrl(string $url): string
    {
        $path = parse_url($url, PHP_URL_PATH) ?? $url;
        return strlen($path) > 60 ? substr($path, 0, 57) . '...' : $path;
    }
}
