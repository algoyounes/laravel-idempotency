# Changelog

All notable changes to `idempotency` will be documented in this file

## v1.1.1 - 2025-12-02

### What's Changed

* chore(deps): bump stefanzweifel/git-auto-commit-action from 5 to 6 in https://github.com/algoyounes/laravel-idempotency/pull/8
* chore(deps): bump actions/checkout from 4 to 5 by @dependabot[bot] in https://github.com/algoyounes/laravel-idempotency/pull/9
* chore(deps): bump stefanzweifel/git-auto-commit-action from 6 to 7 by in https://github.com/algoyounes/laravel-idempotency/pull/10
* chore(deps): bump actions/checkout from 5 to 6 by @dependabot[bot] in https://github.com/algoyounes/laravel-idempotency/pull/11
* Fix(ServiceProvider): Resolve LockProvider binding for Laravel 12 by @algoyounes in https://github.com/algoyounes/laravel-idempotency/pull/13

**Full Changelog**: https://github.com/algoyounes/laravel-idempotency/compare/v1.1.0...v1.1.1

## v1.1.0 - 2025-04-17

### What's Changed

* feat: add support php8.4 by @algoyounes in https://github.com/algoyounes/laravel-idempotency/pull/7

**Full Changelog**: https://github.com/algoyounes/laravel-idempotency/compare/v1.0.7...v1.1.0

## v1.0.7 - 2025-03-27

### Changes

- chore: add console check to publishes method
- chore(CheckSum): normalize JSON encoding with UNESCAPED_SLASHES and UNESCAPED_UNICODE
- fix(Checksum): remove JSON_PARTIAL_OUTPUT_ON_ERROR to enforce data integrity

**Full Changelog**: https://github.com/algoyounes/laravel-idempotency/compare/v1.0.6...v1.0.7

## v1.0.5 - 2025-03-05

**Full Changelog**: https://github.com/algoyounes/laravel-idempotency/compare/v1.0.4...v1.0.5

## v1.0.4 - 2025-03-03

### Changes :

- Refactored `IdempotencyServiceProvider` for better readability

**Full Changelog**: https://github.com/algoyounes/idempotency/compare/v1.0.3...v1.0.4

## v1.0.3 - 2025-02-27

**Full Changelog**: https://github.com/algoyounes/idempotency/compare/v1.0.2...v1.0.3

## v1.0.2 - 2025-02-21

**Full Changelog**: https://github.com/algoyounes/idempotency/compare/v1.0.0...v1.0.2

## v1.0.0 - 2025-02-01

🎉 **Idempotency v1.0.0** is here!

The first stable release of **Idempotency v1.0.0** is now available, bringing advanced Idempotent request management to your applications.

### Release Features ✨

* **Idempotent Requests**: Ensure requests can be safely retried without side effects.
* **Middleware Integration**: Easily add idempotency middleware to routes or route groups.
* **Customizable Cache**: Configure cache TTL and store for idempotency keys.
* **Duplicate Handling**: Choose between replaying cached responses or throwing exceptions for duplicate requests.
* **Custom Resolvers**: Implement custom logic for resolving user IDs or generating cache keys.
* **Header-Based Idempotency**: Use the Idempotency-Key header to identify requests.
* **Race Condition Handling**: Prevent race conditions with configurable lock wait times.
* **Unauthenticated Support**: Define a default user ID name for unauthenticated requests.

### Contributors

* @algoyounes 🤠

**Full Changelog**: https://github.com/algoyounes/idempotency/commits/v1.0.0
