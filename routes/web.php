<?php

use Illuminate\Support\Facades\Route;
use Jalismahamud\TrafficAnalytics\Http\Controllers\TrafficAnalyticsController;

Route::middleware(['web', 'auth'])->prefix('admin/traffic')->name('traffic.')->group(function () {
    Route::get('dashboard', [TrafficAnalyticsController::class, 'dashboard'])
        ->name('dashboard');

    Route::get('chart-data', [TrafficAnalyticsController::class, 'getChartData'])
        ->name('chart-data');

    Route::get('export-csv', [TrafficAnalyticsController::class, 'exportCsv'])
        ->name('export-csv');

    Route::delete('traffic-logs/clear', [TrafficAnalyticsController::class, 'clearLogs'])
        ->name('traffic.clear');
});
