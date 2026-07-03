<?php

namespace Waha;

use Illuminate\Support\ServiceProvider;

class WahaServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/waha.php', 'waha');

        $this->app->singleton(WahaClient::class, function ($app) {
            $c = $app['config']['waha'];

            return new WahaClient(
                (string) ($c['url'] ?? ''),
                (string) ($c['api_key'] ?? ''),
                (string) ($c['session'] ?? 'default'),
                (string) ($c['default_cc'] ?? '60'),
                (int) ($c['timeout'] ?? 15),
            );
        });
    }

    public function boot(): void
    {
        $this->publishes([__DIR__.'/../config/waha.php' => config_path('waha.php')], 'waha-config');
    }
}
