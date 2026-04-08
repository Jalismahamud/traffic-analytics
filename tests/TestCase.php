<?php

namespace Jalismahamud\TrafficAnalytics\Tests;

use Orchestra\Testbench\TestCase;
use Jalismahamud\TrafficAnalytics\TrafficAnalyticsServiceProvider;

class TestCase extends TestCase
{
    protected function getPackageProviders($app)
    {
        return [TrafficAnalyticsServiceProvider::class];
    }

    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
        ]);
    }
}
