# Uspacy App Template (Laravel)

A starter backend for building an app for the [Uspacy](https://uspacy.com) CRM. It already does the parts every app needs:

- **Install and uninstall.** It keeps a record for each Uspacy portal that installs your app.
- **Calling the Uspacy API.** It calls the API on behalf of a portal (to create chats and messages, CRM entities, tasks, activities and more) and refreshes the access token automatically.
- **Webhooks.** It receives Uspacy events through an AWS SNS subscription.

Requires PHP 8.4 and Composer.

## How it works

1. **Registration.** The Uspacy team registers your app on request. See [Registering your app](#registering-your-app). After that, the app appears in the Uspacy Marketplace.
2. **Install.** A portal admin or owner clicks **Install** in the Marketplace. Uspacy's internal service then calls this backend at `POST /{integration}/v1/portals/install`. The request carries a JWT signed with your `client_secret`, plus the portal's API tokens. The backend checks the JWT and saves the portal along with its tokens.
3. **Work.** With the saved token, the backend calls the Uspacy API on the portal's behalf. It can do whatever your app needs, for example create chats and messages, CRM entities, tasks and activities. The token is refreshed automatically when it expires.
4. **Events (optional).** If your app is subscribed to webhooks, Uspacy sends events through AWS SNS to `POST /{integration}/v1/uspacy/webhooks`. See [Webhooks](#webhooks).
5. **Uninstall.** A portal admin or owner clicks **Uninstall**. Uspacy's internal service calls `DELETE /{integration}/v1/portals/uninstall`, and the backend deletes the portal.

```mermaid
sequenceDiagram
    actor Admin as Portal admin / owner
    participant M as Marketplace (acme.uspacy.com)
    participant S as Uspacy internal service
    participant A as This backend
    participant DB as Database
    participant API as Uspacy API (acme.uspacy.com)

    Admin->>M: Clicks "Install"
    M->>S: Install request
    S->>A: POST /{integration}/v1/portals/install<br/>Authorization: Bearer JWT<br/>body: token, refresh_token, expiry_date
    A->>A: Find the integration by {integration} in the URL
    A->>A: Verify the JWT signature with client_secret
    A->>A: Check integration_code and client_id in the JWT headers
    alt JWT invalid
        A-->>S: 401 Unauthorized
    else JWT valid
        A->>DB: Create or update the portal (domain + integration_code)<br/>and store the tokens (encrypted)
        A-->>S: 200 Portal installed successfully
    end

    Note over A,API: Later, whenever the app needs it
    A->>DB: Load the portal and its token
    A->>API: Create a chat, a CRM entity, a task...<br/>(token refreshed automatically when expired)

    Note over Admin,A: Uninstall follows the same path
    Admin->>M: Clicks "Uninstall"
    M->>S: Uninstall request
    S->>A: DELETE /{integration}/v1/portals/uninstall<br/>Authorization: Bearer JWT
    A->>DB: Delete the portal
    A-->>S: 200 Portal uninstalled successfully
```

## Registering your app

You can't register an app in Uspacy yourself: you contact the Uspacy team and ask them to do it.

1. **Choose an integration code**, for example `my-app`. It identifies your app and must be the same in every environment.
2. **Stage first.** The Uspacy team registers the app on the Stage (test) environment and sends you a `client_id` and `client_secret` for Stage.
3. **Then production.** Production gets its own `client_id` and `client_secret`. The integration code stays the same.
4. **Send the URLs** of your backend for each environment: `install` and `uninstall` are required, and the webhook URL is needed only if you want [webhooks](#webhooks). See [Endpoints](#endpoints).

Once registered, the app is listed in the Marketplace, where portal admins and owners can install it.

## Quick start

```bash
composer setup        # installs dependencies, creates .env, generates the app key and runs migrations
php artisan serve     # http://localhost:8000
```

Put your app's credentials in `.env` (see [Registering your app](#registering-your-app)). Each environment has its own `.env`: Stage uses the Stage credentials, production uses the production ones, and the integration code is the same in both:

```dotenv
USPACY_EXAMPLE_INTEGRATION_CODE=example
USPACY_EXAMPLE_CLIENT_ID=...
USPACY_EXAMPLE_CLIENT_SECRET=...
```

Uspacy must be able to reach the server, so it needs a public HTTPS URL. For local testing, use a tunnel such as ngrok or Cloudflare Tunnel.

## Endpoints

Every URL starts with the integration key, then the API version:

| Method | URL | Called by | Purpose | Required |
|---|---|---|---|---|
| `POST` | `/{integration}/v1/portals/install` | Uspacy internal service | App installed on a portal | **Yes** |
| `DELETE` | `/{integration}/v1/portals/uninstall` | Uspacy internal service | App removed from a portal | **Yes** |
| `GET` | `/{integration}/v1/portals/me` | Your frontend | The current portal | No |
| `GET` | `/{integration}/v1/crm/deals` | Your frontend | Example: first 10 CRM deals of the portal | No, example |
| `POST` | `/{integration}/v1/uspacy/webhooks` | AWS SNS | Example webhook receiver (logs messages only) | No, example |

Uspacy needs only `install` and `uninstall`. The CRM deals and webhook routes are examples: replace them with your own app's logic, or delete them.

`{integration}` is a key in [config/uspacy.php](config/uspacy.php), for example `example-integration`. When you ask the Uspacy team to register your app, send them URLs like these (one set for Stage, one for production):

```
https://your-server.com/example-integration/v1/portals/install
https://your-server.com/example-integration/v1/portals/uninstall
https://your-server.com/example-integration/v1/uspacy/webhooks
```

## Webhooks

Your app gets webhooks only after it is subscribed to them. You can't subscribe from the app or through an API: you ask the Uspacy team, and they set up the subscription by hand on their side. Ask for each type you need separately. The types are:

- `crm`
- `tasks`
- `messenger`
- `news_feed`

The example webhook route works for `example-integration` only (`whereIn('integration', [...])` in [routes/api.php](routes/api.php)). This is on purpose: one backend can serve several integrations, and some of them may not receive webhooks at all. To turn webhooks on for another integration, add its key to that list.

## Adding another integration

One deployment can serve several Uspacy apps:

1. Add an entry to `integrations` in [config/uspacy.php](config/uspacy.php). Its key becomes the `{integration}` part of the URL.
2. Add its `*_INTEGRATION_CODE`, `*_CLIENT_ID` and `*_CLIENT_SECRET` to `.env`.
3. Put its routes in [routes/api.php](routes/api.php) and its controllers under `App\Http\Controllers\<YourIntegration>`.

## Development

```bash
composer test                # run the tests
vendor/bin/pint --dirty      # format the code
```

