# Traffic Analytics

A powerful Laravel package for tracking and analyzing website traffic with real-time analytics dashboard.

[![Latest Stable Version](https://poser.pugx.org/jalismahamud/traffic-analytics/v)](//packagist.org/packages/jalismahamud/traffic-analytics)
[![License](https://poser.pugx.org/jalismahamud/traffic-analytics/license)](//packagist.org/packages/jalismahamud/traffic-analytics)
[![PHP Version](https://poser.pugx.org/jalismahamud/traffic-analytics/require/php)](//packagist.org/packages/jalismahamud/traffic-analytics)

## Features

- 📊 Real-time traffic tracking and logging
- 📈 Advanced analytics and reporting
- 🎯 Top URLs, IP addresses, and HTTP methods tracking
- 🔍 Detailed response time analysis
- 📉 Status code distribution
- 💾 CSV export functionality
- ⚡ Intelligent caching system
- 🛡️ Automatic filtering of static assets
- 🔐 User-aware tracking (authenticated users)
- 📱 Mobile-friendly dashboard

## Requirements

- PHP 8.1+
- Laravel 10.0+
- MySQL/MariaDB or any Eloquent-compatible database

## Installation

### Step 1: Install via Composer

```bash
composer require jalismahamud/traffic-analytics
```

### Step 2: Publish Assets

Publish the configuration, migrations, and views:

```bash
php artisan vendor:publish --provider="Jalismahamud\TrafficAnalytics\TrafficAnalyticsServiceProvider"
```

Or publish specific assets:

```bash
# Publish migrations
php artisan vendor:publish --provider="Jalismahamud\TrafficAnalytics\TrafficAnalyticsServiceProvider" --tag="traffic-analytics-migrations"

# Publish config
php artisan vendor:publish --provider="Jalismahamud\TrafficAnalytics\TrafficAnalyticsServiceProvider" --tag="traffic-analytics-config"

# Publish views
php artisan vendor:publish --provider="Jalismahamud\TrafficAnalytics\TrafficAnalyticsServiceProvider" --tag="traffic-analytics-views"
```

### Step 3: Run Migrations

```bash
php artisan migrate
```

## Configuration

The configuration file will be published to `config/traffic-analytics.php`:

```php
return [
    'table' => 'traffic_logs',            // Database table name
    'cache_ttl' => 60,                     // Cache TTL in seconds
    'enabled' => env('TRAFFIC_ANALYTICS_ENABLED', true),
    'skip_paths' => ['api/*', 'health'],  // Paths to skip logging
    'skip_extensions' => ['css', 'js', 'png', ...], // File extensions to skip
];
```

## Usage

### 1. Register the Middleware

Add the middleware to your route group in `routes/web.php`:

```php
use Jalismahamud\TrafficAnalytics\Http\Middleware\TrafficLogger;

Route::middleware([TrafficLogger::class])->group(function () {
    // Your routes here
});
```

### 2. Access the Dashboard

Once the middleware is registered and migrations are run, access the dashboard at:

```
http://your-app.local/admin/traffic/dashboard
```

### 3. Using the Service

You can use the `TrafficAnalyticsService` to get traffic data programmatically:

```php
use Jalismahamud\TrafficAnalytics\Services\TrafficAnalyticsService;

$service = app(TrafficAnalyticsService::class);

// Get summary statistics
$summary = $service->getSummary('2024-01-01', '2024-01-31');

// Get top URLs
$topUrls = $service->getTopUrls('2024-01-01', '2024-01-31', limit: 10);

// Get traffic over time
$traffic = $service->getTrafficOverTime('2024-01-01', '2024-01-31', groupBy: 'hour');

// Get status distribution
$statuses = $service->getStatusDistribution('2024-01-01', '2024-01-31');

// Get top IPs
$ips = $service->getTopIPs('2024-01-01', '2024-01-31');

// Get method distribution
$methods = $service->getMethodDistribution('2024-01-01', '2024-01-31');

// Get recent logs
$logs = $service->getRecentLogs(limit: 50);

// Export to CSV
$csv = $service->exportCsv('2024-01-01', '2024-01-31');
```

### 4. Query Builder Usage

Use the TrafficLog model directly with custom queries:

```php
use Jalismahamud\TrafficAnalytics\Models\TrafficLog;

// Get today's traffic
$today = TrafficLog::today()->get();

// Get last 7 days
$lastWeek = TrafficLog::lastDays(7)->get();

// Get specific date range
$range = TrafficLog::dateRange('2024-01-01', '2024-01-31')->get();

// Get only errors
$errors = TrafficLog::errors()->get();
```

## API Endpoints

The package provides the following API endpoints:

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/admin/traffic/dashboard` | View analytics dashboard |
| GET | `/admin/traffic/chart-data` | Get chart data (JSON) |
| GET | `/admin/traffic/export-csv` | Export data as CSV |

### Query Parameters

All data endpoints support these query parameters:

```
?range=today|7|30|custom
?date_from=YYYY-MM-DD
?date_to=YYYY-MM-DD
```

## Database Schema

The package creates a `traffic_logs` table with the following columns:

```
id              - Primary key
url             - Full request URL
method          - HTTP method (GET, POST, etc.)
ip_address      - Client IP address
status_code     - HTTP response status code
response_time   - Response time in milliseconds
user_id         - Authenticated user ID (nullable)
user_agent      - Client user agent
referrer        - HTTP referer (nullable)
created_at      - Timestamp
```

## Performance Considerations

### Caching

The package uses Laravel's caching system to cache analytics data for production-ready performance. Cache TTL is configurable in `config/traffic-analytics.php`.

### Database Indexes

For best performance, ensure you have proper indexes:

```php
// In a migration
Schema::table('traffic_logs', function (Blueprint $table) {
    $table->index('created_at');
    $table->index('url');
    $table->index('ip_address');
    $table->index('status_code');
});
```

### Pruning Old Logs

Consider adding database pruning in your `app/Console/Kernel.php`:

```php
use Jalismahamud\TrafficAnalytics\Models\TrafficLog;

protected function schedule(Schedule $schedule)
{
    $schedule->call(function () {
        TrafficLog::where('created_at', '<', now()->subMonths(3))->delete();
    })->daily();
}
```

## Development & Testing

### Running Tests

```bash
composer test
```

Or with coverage:

```bash
composer test -- --coverage-html coverage
```

## Publishing & Customization

### Custom Views

After publishing, customize views in `resources/views/vendor/traffic-analytics/`:

```bash
php artisan vendor:publish --tag="traffic-analytics-views"
```

### Custom Configuration

Edit `config/traffic-analytics.php` to:
- Change table name
- Adjust cache TTL
- Add more skip paths
- Configure skip extensions

## Troubleshooting

### No data appearing?

1. Check middleware is registered
2. Verify middleware is in correct route group
3. Check `TRAFFIC_ANALYTICS_ENABLED=true` in `.env`
4. Ensure migrations are run: `php artisan migrate`
5. Check database connection

### Dashboard shows 404?

- Verify migrations are run
- Check routes are published
- Verify middleware is applied to dashboard route

### High database usage?

- Check if data pruning is configured
- Increase cache TTL in config
- Consider adding more database indexes

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## Security

If you discover a security vulnerability, please email jalismahamud31@email.com instead of using the issue tracker.

## License

This package is open-sourced software licensed under the [MIT license](LICENSE).

## Support

For support, issues, or questions:
- GitHub Issues: [traffic-analytics/issues](https://github.com/Jalismahamud/traffic-analytics/issues)
- Email: jalismahamud31@email.com

## Changelog

See the [CHANGELOG](CHANGELOG.md) for recent changes.

## Credits

Created by [Jalis Mahamud](https://github.com/Jalismahamud)

---

Made with ❤️ for the Laravel community
