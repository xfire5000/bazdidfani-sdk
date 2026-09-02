<?php

namespace Bazdidfani\ApiClient;

use Illuminate\Http\Client\Factory;
use Illuminate\Support\ServiceProvider;

class BazdidfaniApiClientServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/bazdidfani-api.php',
            'bazdidfani-api',
        );

        $this->app->singleton(BazdidfaniApiClient::class, function ($app): BazdidfaniApiClient {
            return new BazdidfaniApiClient(
                http: $app->make(Factory::class),
                baseUrl: (string) config('bazdidfani-api.base_url', ''),
                token: (string) config('bazdidfani-api.token', ''),
                organizationCode: (string) config('bazdidfani-api.organization_code', ''),
                organizationHeader: (string) config('bazdidfani-api.organization_header', 'X-Organization-Code'),
                timeout: (int) config('bazdidfani-api.timeout', 15),
                retryTimes: (int) config('bazdidfani-api.retry_times', 2),
                retrySleepMilliseconds: (int) config('bazdidfani-api.retry_sleep_milliseconds', 200),
            );
        });

        $this->app->alias(BazdidfaniApiClient::class, 'bazdidfani-api');
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../config/bazdidfani-api.php' => config_path('bazdidfani-api.php'),
        ], 'bazdidfani-api-config');
    }
}
