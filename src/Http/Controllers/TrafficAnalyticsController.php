<?php
namespace Jalismahamud\TrafficAnalytics\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\View\View;
use Jalismahamud\TrafficAnalytics\Services\TrafficAnalyticsService;

class TrafficAnalyticsController extends Controller
{
    public function __construct(
        protected TrafficAnalyticsService $service
    ) {}

    public function dashboard(): View
    {
        return view('traffic-analytics::dashboard');
    }

    public function getChartData(Request $request): JsonResponse
    {
        $request->validate([
            'range'     => 'nullable|in:today,7,30,custom',
            'date_from' => 'nullable|date',
            'date_to'   => 'nullable|date|after_or_equal:date_from',
        ]);

        [$from, $to, $groupBy] = $this->resolveDateRange($request);

        return response()->json([
            'summary'     => $this->service->getSummary($from, $to),
            'over_time'   => $this->service->getTrafficOverTime($from, $to, $groupBy),
            'top_urls'    => $this->service->getTopUrls($from, $to),
            'status_dist' => $this->service->getStatusDistribution($from, $to),
            'top_ips'     => $this->service->getTopIPs($from, $to),
            'methods'     => $this->service->getMethodDistribution($from, $to),
            'recent_logs' => $this->service->getRecentLogs(30),
        ]);
    }

    public function exportCsv(Request $request): Response
    {
        $request->validate([
            'range'     => 'nullable|in:today,7,30,custom',
            'date_from' => 'nullable|date',
            'date_to'   => 'nullable|date',
        ]);

        [$from, $to] = $this->resolveDateRange($request);

        $csv      = $this->service->exportCsv($from, $to);
        $filename = 'traffic_' . $from . '_to_' . $to . '.csv';

        return response($csv, 200, [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    private function resolveDateRange(Request $request): array
    {
        $range = $request->input('range', '7');

        if ($range === 'today') {
            return [today()->toDateString(), today()->toDateString(), 'hour'];
        }

        if ($range === 'custom') {
            $from = $request->input('date_from', now()->subDays(7)->toDateString());
            $to   = $request->input('date_to', now()->toDateString());
            $days = now()->parse($from)->diffInDays(now()->parse($to));
            return [$from, $to, $days <= 2 ? 'hour' : 'day'];
        }

        $days = (int) $range;
        return [
            now()->subDays($days - 1)->toDateString(),
            now()->toDateString(),
            $days <= 1 ? 'hour' : 'day',
        ];
    }

    public function clearLogs(Request $request): JsonResponse
    {
        $request->validate([
            'type' => 'required|in:old,all',
        ]);

        $type = $request->input('type');

        try {
            if ($type === 'old') {
              
                $deleted = $this->service->clearOldLogs();
                $message = "Successfully deleted {$deleted} log entries older than 30 days.";
            } else {
                $deleted = $this->service->clearAllLogs();
                $message = "Successfully deleted all {$deleted} log entries.";
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'deleted' => $deleted,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete logs: ' . $e->getMessage(),
            ], 500);
        }
    }

}
