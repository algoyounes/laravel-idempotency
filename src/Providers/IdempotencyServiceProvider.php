<?php

namespace AlgoYounes\Idempotency\Providers;

use AlgoYounes\Idempotency\Config\IdempotencyConfig;
use AlgoYounes\Idempotency\Managers\Cache\IdempotencyCacheManager;
use AlgoYounes\Idempotency\Middleware\IdempotencyMiddleware;
use AlgoYounes\Idempotency\Resolvers\NullUserIdResolver;
use Illuminate\Contracts\Cache\Factory as CacheFactory;
use Illuminate\Contracts\Cache\LockProvider;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Contracts\Config\Repository as ConfigRepository;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Http\Kernel;
use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;

class IdempotencyServiceProvider extends ServiceProvider
{
    private ?IdempotencyConfig $config = null;

    /** @var array<string, class-string> */
    private array $routeMiddleware = [
        'idempotency' => IdempotencyMiddleware::class,
    ];

    public function boot(): void
    {
        $this->publishConfig();
        $this->configMiddleware();
    }

    public function register(): void
    {
        $this->registerConfig();
        $this->registerBindings();

        NullUserIdResolver::setConfig($this->getConfig());
    }

    // Boot methods

    private function publishConfig(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                dirname(__DIR__, 2).'/config/idempotency.php' => config_path('idempotency.php'),
            ], 'config');
        }
    }

    private function configMiddleware(): void
    {
        make(Kernel::class)->prependMiddleware(IdempotencyMiddleware::class);

        /** @var Router $router */
        $router = $this->app->make('router');

        foreach ($this->routeMiddleware as $alias => $middleware) {
            $router->aliasMiddleware($alias, $middleware);
        }
    }

    // Register methods

    private function registerConfig(): void
    {
        $this->mergeConfigFrom(dirname(__DIR__, 2).'/config/idempotency.php', 'idempotency');
    }

    private function registerBindings(): void
    {
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
    }

    private function getCacheRepository(Application $app): CacheRepository
    {
        /** @var CacheFactory $cacheFactory */
        $cacheFactory = $app->make(CacheFactory::class);

        return $cacheFactory->store($this->getCacheStoreName());
    }

    private function getCacheStoreName(): string
    {
        $cacheStore = $this->getConfig();
        if ($cacheStore->isDefaultCacheStore()) {
            // @phpstan-ignore-next-line
            return config('cache.default');
        }

        return $cacheStore->getCacheStore();
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
