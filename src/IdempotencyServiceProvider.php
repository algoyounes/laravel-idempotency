<?php

namespace AlgoYounes\Idempotency;

use Illuminate\Support\ServiceProvider;

class IdempotencyServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/config/idempotency.php' => config_path('idempotency.php'),
        ], 'config');
    }

    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/config/idempotency.php', 'idempotency');
    }
}
