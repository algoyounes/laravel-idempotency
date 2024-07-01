<?php

namespace AlgoYounes\Idempotency\Providers;

use AlgoYounes\Idempotency\Config\IdempotencyConfig;
use AlgoYounes\Idempotency\Managers\Cache\IdempotencyCacheManager;
use AlgoYounes\Idempotency\Resolvers\NullUserIdResolver;
use Illuminate\Contracts\Cache\Factory as CacheFactory;
use Illuminate\Contracts\Cache\LockProvider;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Contracts\Config\Repository as ConfigRepository;
use Illuminate\Foundation\Application;
use Illuminate\Support\ServiceProvider;

class IdempotencyServiceProvider extends ServiceProvider
{
    private ?IdempotencyConfig $config = null;

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
            IdempotencyConfig::class,
            function (Application $app): IdempotencyConfig {
                /** @var ConfigRepository $configRepository */
                $configRepository = $app->make(ConfigRepository::class);
                $config = (array) $configRepository->get('idempotency', []);

                return IdempotencyConfig::createFromArray($config);
            }
        );

        $this->app->singleton(
            IdempotencyCacheManager::class,
            function (Application $app): IdempotencyCacheManager {
                /** @var LockProvider $lockProvider */
                $lockProvider = $app->make(LockProvider::class);

                return new IdempotencyCacheManager($this->getCacheRepository($app), $lockProvider, $this->getConfig($app));
            }
        );

        $this->app->bind(CacheRepository::class, fn (Application $app): CacheRepository => $this->getCacheRepository($app));

        NullUserIdResolver::setConfig($this->getConfig());
    }
    private function getCacheRepository(Application $app): CacheRepository
    {
        /** @var CacheFactory $cacheFactory */
        $cacheFactory = $app->make(CacheFactory::class);

        return $cacheFactory->store($this->getCacheStoreName());
    }

    private function getCacheStoreName(): string
    {
        $cacheStore = $this->getConfig()->getCacheStore();
        if ($cacheStore === 'default') {
            // @phpstan-ignore-next-line
            return config('cache.default');
        }

        return $cacheStore;
    }

    private function getConfig(?Application $app = null): IdempotencyConfig
    {
        if ($this->config instanceof IdempotencyConfig) {
            return $this->config;
        }

        /** @var IdempotencyConfig $config */
        $config = ($app ?? $this->app)->make(IdempotencyConfig::class);

        return $this->config = $config;
    }
}
