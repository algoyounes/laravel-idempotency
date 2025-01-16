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

Idempotency is a Laravel package that helps you make your requests idempotent. Idempotent requests can be safely retried without causing any side effects. This is useful when you're dealing with unreliable networks or when you want to prevent duplicate requests from being processed.

## Features ✨

- **Idempotent Requests**: Ensure that requests can be safely retried without side effects.
- **Middleware Integration**: Easily add `idempotency` middleware to routes or route groups.
- **Customizable Cache**: Configure cache TTL and store for idempotency keys.
- **Duplicate Handling**: Choose between replaying cached responses or throwing exceptions for duplicate requests.
- **Custom Resolvers**: Implement custom logic for resolving user IDs or generating cache keys.
- **Header-Based Idempotency**: Use the `Idempotency-Key` header to identify requests.
- **Race Condition Handling**: Prevent race conditions with configurable lock wait times.
- **Unauthenticated Support**: Define a default user ID name for unauthenticated requests.

## Installation

You can install the package via Composer:

```bash
composer require algoyounes/idempotency
```

You can publish the configuration file using the following command:

```bash
php artisan vendor:publish --provider="AlgoYounes\Idempotency\Providers\IdempotencyServiceProvider" --tag="config"
```

Here are the available options in the configuration file:

| Option | Default Value                               | Description |
| --- |---------------------------------------------| --- |
| `enable` | `true`                                      | Enable or disable the idempotency middleware. |
| `cache.ttl` | `86400` _(1 Day)_                           | The time-to-live for idempotency keys in minutes. |
| `cache.store` | `default`                                   | The cache store to use for idempotency keys. |
| `idempotency_header` | `Idempotency-Key`                           | The header to use for idempotency keys. |
| `idempotency_relayed_header` | `Idempotency-Relayed`                       | The header to use for relaying idempotency keys. |
| `duplicate_handling` | `replay`                                    | The action to take when a duplicate request is detected. Options are `replay` or `throw`. |
| `enforced_verbs` | `['GET', 'POST', 'PUT', 'PATCH', 'DELETE']` | The HTTP verbs to enforce idempotency on. |
| `max_lock_wait_time` | `10` _(10 seconds)_                         | The maximum time to wait for a lock in seconds. |
| `user_id_resolver` | `null`                                      | The user ID resolver to use for generating cache keys. |
| `unauthenticated_user_id` | `guest`                                     | The default user ID name for unauthenticated requests. |


## Usage

To request idempotent, try adding something like `idempotency` middleware to the route or group of routes you want to protect. The middleware will check if the request is idempotent by looking for the `Idempotency-Key` header. If the header is present, the middleware will cache the response and reuse it for subsequent requests with the same key.

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

class CustomUserIdResolver implements ResolveContract
{
    public function resolve(): string
    {
        // Your custom logic here
    }
}

// In the configuration file
'user_id_resolver' => CustomUserIdResolver::class,
```
