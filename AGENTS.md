# AGENTS.md

Guidance for AI coding agents working in this repository.

## Summary

Example/template Laravel 13 (PHP 8.4) backend for a Uspacy CRM integration app. Shows how an app gets installed on a Uspacy portal, calls the Uspacy API on the portal's behalf (via `uspacy/uspacy-php-sdk`), and receives Uspacy webhooks from the Uspacy internal events emitter. API-only: routes live in [routes/api.php](routes/api.php) with no `/api` prefix; every exception renders as JSON.

**Uspacy platform docs** (registration, install flow, JWTs, tokens, REST API, external lines, webhooks, frontend): [docs/uspacy/README.md](docs/uspacy/README.md). Read the relevant file before answering questions about building a Uspacy integration.

## Commands

```bash
composer setup                              # install, .env, key, migrate (SQLite by default)
php artisan serve
composer checks                             # lint, analyse, test (same as CI)
composer test                               # or: php artisan test
php artisan test --filter=test_method_name  # single test
php artisan test tests/Feature/CrmDealsTest.php
vendor/bin/pint --dirty                     # format (Laravel preset)
composer lint                               # Pint in test mode
composer analyse                            # Larastan (level 8, phpstan.neon)
composer ide-helper:model                   # regenerate model PHPDoc, then pint
```

## Architecture

**Multi-integration routing.** Every route starts with `/{integration}/v1`, a key of `config('uspacy.integrations')` ([config/uspacy.php](config/uspacy.php)). Each entry holds `integration_code`, `client_id`, `client_secret` from env. One deployment can serve several Uspacy apps; add one by adding a config entry + env vars.

**Portal = one installation.** `Portal` model is unique per `(domain, integration_code)` and stores the Uspacy access/refresh tokens (encrypted casts) and `expiry_date`.

**Lifecycle.** The Uspacy team registers each app by hand, on Stage and then production. Each environment has its own `client_id`/`client_secret`, but `integration_code` is the same everywhere. When a portal admin or owner clicks Install/Uninstall in the Marketplace, a Uspacy internal service calls `install`/`uninstall` here. Webhook subscriptions are also set up by hand on the Uspacy side.

**Auth flow.** Uspacy sends a HS256 JWT as the Bearer token, signed with the integration's `client_secret`. `domain`, `integration_code`, `client_id` are read from the JWT **headers**, not claims.
- `IntegrationTokenDecoder` resolves the integration by route pathname, verifies the signature (`JwtService`), and checks `integration_code`. Decode failures throw `JwtDecodeException` → 401 (configured in [bootstrap/app.php](bootstrap/app.php)).
- `POST /{integration}/v1/portals/install` / `DELETE .../uninstall` (`PortalController`) additionally check `client_id` and upsert/delete the portal via `PortalInstaller`.
- Portal users can also call routes directly (e.g. `/portals/me`, `/crm/deals`) with such a JWT; `ResolvePortalMiddleware` decodes the JWT, loads the portal, and stores it in the request-scoped `PortalContext`; controllers read it with the global `portal()` helper ([app/helpers.php](app/helpers.php)).

**Calling Uspacy.** `UspacySDKFactory::fromPortal()` builds a `UspacySDK` for `https://{domain}`; signs in with client credentials if the stored token is expired, and attaches `RefreshApplicationTokenMiddleware`, which on a `401` signs in again, retries the request, and persists the new tokens back to the portal. Example usage: `CrmDealsController`.

**Webhooks.** `POST /{integration}/v1/uspacy/webhooks` (`UspacyWebhooksController`, restricted to `example-integration`) is a test `sns_subscription_url`: it decodes the envelope and its `Message` JSON string, finds the portal by `data.domain`, and logs the message. Deliveries come from Uspacy's relay, unsigned and without subscription confirmation; format in [docs/uspacy/webhooks.md](docs/uspacy/webhooks.md#application-webhooks). Always returns 200 so the relay does not retry.

Integration-specific example code lives under `App\Http\Controllers\ExampleIntegration`.

`AppServiceProvider` enables `preventLazyLoading` and `preventSilentlyDiscardingAttributes` outside production. `RequestLoggingMiddleware` is global.
