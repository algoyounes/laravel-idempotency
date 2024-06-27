<?php

namespace AlgoYounes\Idempotency\Providers;

use AlgoYounes\Idempotency\Config\IdempotencyConfig;
use AlgoYounes\Idempotency\Managers\Cache\IdempotencyCacheManager;
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
                $cacheRepository = $app['cache']->store($this->getCacheStoreName());
                $lockProvider = $app[LockProvider::class];

                return new IdempotencyCacheManager($cacheRepository, $lockProvider);
            }
        );

        $this->app->bind(CacheRepository::class, fn (array $app) => $app['cache']->store($this->getCacheStoreName()));

        $this->app->bind(CacheRepository::class, fn (array $app) => $app['cache']->store($this->getCacheStoreName()));
    }

    private function getCacheStoreName(): string
    {
        $cacheStore = IdempotencyConfig::get(IdempotencyConfig::CACHE_STORE_KEY, 'default');
        if ($cacheStore === 'default') {
            // @phpstan-ignore-next-line
            return config('cache.default');
        }

        return $cacheStore;
    }
}
