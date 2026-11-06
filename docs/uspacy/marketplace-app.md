# Marketplace app: registration, install, tokens

## Registration

Apps cannot be self-registered. Email support@uspacy.com; the Uspacy team registers the app by hand, first on Stage, then on production.

You provide:

- **`code`**: unique integration code, e.g. `my-app`. Same in every environment.
- **`callback_url`**: base URL. Uspacy appends `/portals/install` and `/portals/uninstall`. In this template, `https://your-server.com/{integration}/v1`.
- **`sns_subscription_url`** (optional): where application webhooks are delivered. See [webhooks.md](webhooks.md#application-webhooks).
- **Webhook types** (optional): any of `crm`, `tasks`, `messenger`, `news_feed`, each requested separately.
- **`price`**: `true` for a paid app, `false` for free.
- Names and descriptions per locale: `uk`, `en-US`, `pt-BR`, `pl`, `es-ES`, `de`.
- Frontend (optional): `remoteEntry.js` URL, Module Federation name, exposed modules. See [frontend.md](frontend.md).

You receive a `client_id` and `client_secret` per environment. The knowledge base calls the secret `secret_id`; it is the same thing.

## Lifecycle

1. A portal admin or owner clicks **Install** in the Marketplace.
2. Uspacy calls `POST {callback_url}/portals/install` with `Authorization: Bearer <JWT>` and a JSON body:
   ```json
   {"token": "...", "refresh_token": "...", "expiry_date": 1683649235}
   ```
   `expiry_date` is a Unix timestamp in seconds. `token` lives about 1 day, `refresh_token` about 30 days.
3. The app verifies the JWT, reads the portal domain, and stores the tokens per `(domain, integration_code)`.
4. The app calls the API on the portal's behalf (see [api.md](api.md)).
5. On **Uninstall**, Uspacy calls `DELETE {callback_url}/portals/uninstall` with the same kind of JWT and no body. Delete the portal's data.

Implementation: `PortalController`, `PortalInstaller`, `InstallPortalRequest`.

## JWTs sent to the app

HS256, signed with the integration's `client_secret`. The data the app needs is in the JWT **headers**, not the claims:

| Header | Meaning |
|---|---|
| `domain` | Portal host, e.g. `acme.uspacy.com`. API base is `https://{domain}`. |
| `integration_code` | Must equal your `code`. |
| `client_id` | Must equal your `client_id` (checked on install/uninstall). |

Two kinds of token reach the app:

- **Integration token**: sent by Uspacy's internal service on install/uninstall.
- **User/frontend token** (`integration_token`): your frontend gets it from the portal (via the app service, for admin users) and sends it as `Authorization: Bearer` to your backend so the backend knows which portal is calling. Example route: `GET /{integration}/v1/portals/me`.

Always verify the signature before trusting any header. Implementation: `JwtService`, `IntegrationTokenDecoder`, `ResolvePortalMiddleware`. Failures return 401.

## Access tokens

- Send as `Authorization: Bearer <token>` to `https://{domain}/...`.
- **Get a fresh token any time:** `POST /auth/v1/auth/app_sign_in` with `{"client_id": "...", "client_secret": "..."}`. Response: `{"jwt": "...", "refreshToken": "...", "expireInSeconds": 720}`. This does not need the stored refresh token, so it also recovers from an expired one.
- Refresh: `POST /auth/v1/auth/refresh_token` with the **refresh** token in `Authorization: Bearer`. Same response shape. The reference also mentions a body; the header form is what the SDK uses.
- Field names differ: install body is snake_case (`token`, `refresh_token`, `expiry_date` absolute), sign-in/refresh responses are camelCase (`jwt`, `refreshToken`, `expireInSeconds` relative).

The template signs in with client credentials when the stored token has expired, and again on any `401` (`RefreshApplicationTokenMiddleware`), then saves the new tokens. See `UspacySDKFactory`.

## Environments

Stage and production are separate registrations with separate `client_id`/`client_secret`; `code` is shared. Portal hosts end in `.uspacy.ua`, `.uspacy.eu`, `.uspacy.com.br` or `.uspacy.com`; always use the `domain` from the JWT, never hard-code a region.
