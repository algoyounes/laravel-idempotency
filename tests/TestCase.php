<?php

namespace AlgoYounes\Idempotency\Tests;

use AlgoYounes\Idempotency\Config\IdempotencyConfig;
use AlgoYounes\Idempotency\Middleware\IdempotencyMiddleware;
use AlgoYounes\Idempotency\Providers\IdempotencyServiceProvider;
use Illuminate\Foundation\Auth\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Orchestra\Testbench\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected IdempotencyConfig $config;

    protected function getPackageProviders($app): array
    {
        return [
            IdempotencyServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app): void
    {
        $this->config = app(IdempotencyConfig::class);

        $app->singleton(\Illuminate\Contracts\Cache\LockProvider::class, function ($app) {
            return $app->make(\Illuminate\Cache\ArrayStore::class);
        });

        $app->singleton('cache', function ($app) {
            return new \Illuminate\Cache\Repository(
                new \Illuminate\Cache\ArrayStore
            );
        });

        // setup config

        // setup middleware
        $app['router']->aliasMiddleware('idempotency', IdempotencyMiddleware::class);

        // setup route
        Route::middleware('idempotency')->group(function () {
            Route::post('/user', function (Request $request) {
                $user = array_merge(auth()->user()->toArray(), $request->all());
                foreach ($request->all() as $key => $val) {
                    auth()->user()->{$key} = $val;
                }

                return response()->json($user);
            });

            Route::get('/user', function () {
                return response()->json(auth()->user());
            });

            Route::post('/account', function () {
                return response()->json(['message' => 'success']);
            });
        });
    }

    protected function defineRoutes($router): void
    {
        $router->post('/test', function () {
            return response()->json(['message' => 'Success']);
        })->middleware('idempotency');
    }

    protected function createDefaultUser(array $options = []): User
    {
        $user = new User();
        $user->unguard();

        return $user->fill(
            [
                'id'   => 1,
                'field' => 'test',
                ...$options,
            ]
        );
    }
}
