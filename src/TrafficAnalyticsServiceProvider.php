<?php

namespace Jalismahamud\TrafficAnalytics;

use Illuminate\Support\ServiceProvider;

class TrafficAnalyticsServiceProvider extends ServiceProvider
{
    public function boot(): void
    {

        $this->publishes([
            __DIR__ . '/../database/migrations/' => database_path('migrations'),
        ], 'traffic-analytics-migrations');

        $this->publishes([
            __DIR__ . '/../resources/views/' => resource_path('views/vendor/traffic-analytics'),
        ], 'traffic-analytics-views');

        $this->publishes([
            __DIR__ . '/../config/traffic-analytics.php' => config_path('traffic-analytics.php'),
        ], 'traffic-analytics-config');

        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'traffic-analytics');

        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
    }

    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/traffic-analytics.php',
            'traffic-analytics'
        );

        $this->app->singleton(
            \Jalismahamud\TrafficAnalytics\Services\TrafficAnalyticsService::class
        );
    }
}