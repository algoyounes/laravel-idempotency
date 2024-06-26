<?php

namespace AlgoYounes\Idempotency\Providers;

use AlgoYounes\Idempotency\Config\IdempotencyConfig;
use AlgoYounes\Idempotency\Managers\Cache\IdempotencyCacheManager;
use AlgoYounes\Idempotency\Managers\IdempotencyManager;
use Illuminate\Contracts\Cache\LockProvider;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
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

        $this->app->singleton(
            IdempotencyCacheManager::class,
            function (array $app): IdempotencyCacheManager {
                $cacheStore = IdempotencyConfig::get(IdempotencyConfig::CACHE_STORE_KEY, 'default');
                $cacheRepository = $app['cache']->store($cacheStore);
                $lockProvider = $app[LockProvider::class];

                return new IdempotencyCacheManager($cacheRepository, $lockProvider);
            }
        );

        $this->app->bind(CacheRepository::class, function (array $app) {
            $cacheStore = IdempotencyConfig::get(IdempotencyConfig::CACHE_STORE_KEY, 'default');

            return $app['cache']->store($cacheStore);
        });

        $this->app->singleton(IdempotencyManager::class,
            fn ($app): IdempotencyManager => new IdempotencyManager($app->make(IdempotencyCacheManager::class))
        );
    }
}
