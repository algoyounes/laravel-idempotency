<p align="center">
<img width="150" height="150" src="assets/logo.png" alt="Laravel Idempotency Logo"/>
<br><b>Idempotency</b>
</p>
<p align="center">
<a href="https://github.com/algoyounes/idempotency/actions"><img src="https://github.com/algoyounes/idempotency/actions/workflows/unit-tests.yml/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/algoyounes/idempotency"><img src="https://img.shields.io/packagist/dt/algoyounes/idempotency" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/algoyounes/idempotency"><img src="https://img.shields.io/packagist/v/algoyounes/idempotency" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/algoyounes/idempotency"><img src="https://img.shields.io/packagist/l/algoyounes/idempotency" alt="License"></a>
</p>

Idempotency is a Laravel package that helps you make your requests idempotent. Idempotent requests are requests that can be safely retried without causing any side effects. This is useful when you're dealing with unreliable networks or when you want to prevent duplicate requests from being processed.

## Installation

You can install the package via composer :

```bash
composer require algoyounes/idempotency
```

You can publish the configuration file using the following command :

```bash
php artisan vendor:publish --provider="AlgoYounes\Idempotency\Providers\IdempotencyServiceProvider" --tag="config"
```

## Usage

To make a request idempotent, try to add something like `idempotency` middleware to the route or group of routes you want to protect. The middleware will check if the request is idempotent by looking for the `Idempotency-Key` header. If the header is present, the middleware will cache the response and reuse it for subsequent requests with the same key.

```php
Route::middleware('idempotency')->post('/orders', 'OrderController@store');
```

You can also add the middleware to a group of routes:

```php
Route::middleware('idempotency')->group(function () {
    Route::post('/orders', 'OrderController@store');
    Route::get('/orders/{id}', 'OrderController@show');
});
```

## Custom Resolver 🔧

You can create your own resolver by implementing the `ResolveContract` interface. 
This is useful when you want to store the cache in a different store or when you want to customize the key generation logic.

```php
use AlgoYounes\Idempotency\Contracts\ResolveContract;

class CustomResolver implements ResolveContract
{
    public function resolve(): string
    {
        // Your custom logic here
    }
}

// In the configuration file
'user_id_resolver' => CustomResolver::class,

```
