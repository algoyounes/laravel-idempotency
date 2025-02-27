# Changelog

All notable changes to `idempotency` will be documented in this file

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
