# Calling the Uspacy REST API

## Basics

- Base URL: `https://{domain}` where `{domain}` comes from the portal (e.g. `acme.uspacy.com`).
- Auth: `Authorization: Bearer <access token>`. For apps, see [marketplace-app.md](marketplace-app.md#access-tokens). Scripts outside the Marketplace can use `POST /auth/v1/auth/sign_in` (`email`, `password`, optional 2FA `code`) or a portal incoming webhook URL created in the UI (see [webhooks.md](webhooks.md#portal-incoming-webhooks)).
- Paths are `/{service}/v1/...`.
- Find any endpoint in <https://uspacy.readme.io/llms.txt>; append `.md` to a reference URL for its schema.

| Service | Prefix | Examples |
|---|---|---|
| Auth | `/auth/v1` | `auth/app_sign_in`, `auth/refresh_token` |
| Users, departments, roles, webhooks | `/company/v1` | `users`, `users/me`, `departments`, `webhooks`, `incoming-webhooks` |
| CRM | `/crm/v1` | `entities/{entity}`, `entities/{entity}/funnel`, `static/tasks` |
| Tasks | `/tasks/v1` | `tasks`, `tasks/{taskId}`, `stages` |
| Messenger | `/messenger/v1` | `chats`, `messages`, `external-lines` |
| Files | `/files/v1` | `files` (multipart upload) |
| Activities | `/activities/v1` | `activities` |
| News feed | `/newsfeed/v1` | `posts` |
| Groups | `/groups/v1` | `groups` |
| Email | `/email/v1` | `emails`, `letters` |
| Apps | `/apps/v1` | `apps` |
| Search | `/search/v1` | `search` |

## CRM

Entities are addressed by plural code: `leads`, `deals`, `contacts`, `companies`, or a custom entity code (`GET /crm/v1/entity` lists them).

| Action | Request |
|---|---|
| List | `GET /crm/v1/entities/{entity}?page=1&list=20` |
| Get | `GET /crm/v1/entities/{entity}/{id}` |
| Create | `POST /crm/v1/entities/{entity}` (201) |
| Update (partial) | `PATCH /crm/v1/entities/{entity}/{id}` |
| Delete (to trash) | `DELETE /crm/v1/entities/{entity}/{id}` |
| Related items | `GET /crm/v1/entities/{entity}/{id}/related/{entityCode}` |
| Fields (incl. custom) | `GET /crm/v1/entities/{entity}/fields` |
| Dropdown values | `GET /crm/v1/entities/{entity}/lists/{field}` |
| Funnels | `GET /crm/v1/entities/{entity}/funnel` |
| Stages | `GET /crm/v1/entities/{entity}/kanban/stage?funnel_id=1` |
| Move to stage | `POST /crm/v1/entities/{entity}/{id}/move/stage/{stageId}` |
| Convert lead | `POST /crm/v1/entities/leads/converting/{id}` |
| CRM activities (tasks, calls, meetings) | `/crm/v1/static/tasks` |
| Call events | `/crm/v1/events/call`; caller lookup `GET /crm/v1/events/caller?phone=...` |
| Products | `/crm/v1/static/products` |

Create example:

```json
POST /crm/v1/entities/leads
{
  "title": "New lead from website",
  "owner": 1,
  "source": "website",
  "funnel_id": 5,
  "email": [{"type": "main", "value": "john@example.com", "main": true}],
  "phone": [{"type": "MOBILE", "value": "+380501234567"}],
  "messengers": [{"type": "telegram", "link": "https://t.me/johndoe"}]
}
```

- `phone[].type`: `WORK`, `MOBILE`, `HOME`.
- `source` and other list fields are sent as the value code (string) but returned as objects (`{title, value, color, ...}`).
- Custom fields are sent by their field code; get codes from `GET /crm/v1/entities/{entity}/fields` or in the UI under Settings → Sections → CRM → (entity), tabs *Standard fields* / *Custom fields*.
- Timestamps are Unix seconds.

## Pagination

Lists take `page` and `list` (page size). Filter syntax beyond that is not documented per entity; check the reference page or the SDK.

## Errors

```json
{"errors": {"text": ["Forbidden message"]}, "status": true}
```

Validation errors (422) key `errors` by field name. `401` means the access token expired or is invalid: get a new token and retry. Rate limits are not documented for the REST API; incoming webhooks are limited to 120 requests per minute.

## PHP SDK

This template uses [uspacy-php-sdk](https://github.com/Uspacy/uspacy-php-sdk). Get a client for the current portal:

```php
$sdk = app(UspacySDKFactory::class)->fromPortal(\portal());
$deals = $sdk->crmDeals()->getEntities(['page' => 1, 'list' => 10]);
```

Services live in `vendor/uspacy/uspacy-php-sdk/src/Services` (CRM, tasks, messenger, files, webhooks, users, and more). Read a service class to find the method for an endpoint. If the SDK lacks an endpoint, send a raw request through the same client so token refresh still applies.
