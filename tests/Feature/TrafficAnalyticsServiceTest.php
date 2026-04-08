<?php

namespace Jalismahamud\TrafficAnalytics\Tests\Feature;

use Jalismahamud\TrafficAnalytics\Tests\TestCase;
use Jalismahamud\TrafficAnalytics\Models\TrafficLog;
use Jalismahamud\TrafficAnalytics\Services\TrafficAnalyticsService;

class TrafficAnalyticsServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        
        // Create tables for testing
        $this->artisan('migrate', [
            '--database' => 'testing',
        ]);
    }

    public function test_get_summary_returns_array(): void
    {
        // Create sample data
        TrafficLog::create([
            'url' => 'http://localhost/test',
            'method' => 'GET',
            'ip_address' => '127.0.0.1',
            'status_code' => 200,
            'response_time' => 100.5,
            'user_agent' => 'Laravel Test',
            'created_at' => now(),
        ]);

        $service = app(TrafficAnalyticsService::class);
        $summary = $service->getSummary(now()->toDateString(), now()->toDateString());

        $this->assertIsArray($summary);
        $this->assertArrayHasKey('total_requests', $summary);
        $this->assertArrayHasKey('unique_visitors', $summary);
        $this->assertArrayHasKey('avg_response_time', $summary);
        $this->assertArrayHasKey('error_rate', $summary);
    }

    public function test_get_top_urls_returns_array(): void
    {
        TrafficLog::create([
            'url' => 'http://localhost/page1',
            'method' => 'GET',
            'ip_address' => '127.0.0.1',
            'status_code' => 200,
            'response_time' => 50.0,
            'user_agent' => 'Laravel Test',
            'created_at' => now(),
        ]);

        $service = app(TrafficAnalyticsService::class);
        $urls = $service->getTopUrls(now()->toDateString(), now()->toDateString());

        $this->assertIsArray($urls);
    }

    public function test_traffic_log_model_query_scopes(): void
    {
        TrafficLog::create([
            'url' => 'http://localhost/test',
            'method' => 'GET',
            'ip_address' => '127.0.0.1',
            'status_code' => 200,
            'response_time' => 100.0,
            'user_agent' => 'Laravel Test',
            'created_at' => now(),
        ]);

        $today = TrafficLog::today()->count();
        $this->assertGreaterThan(0, $today);

        $range = TrafficLog::dateRange(now()->toDateString(), now()->toDateString())->count();
        $this->assertGreaterThan(0, $range);
    }
}
