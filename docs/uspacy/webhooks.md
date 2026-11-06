# Webhooks and events

Two unrelated things are both called "webhooks" in Uspacy:

| | Application webhooks | Portal webhooks |
|---|---|---|
| Used by | Marketplace apps, including this template | One portal's own automations |
| Set up by | Uspacy team, at app registration | Portal owner/admin in the UI (Automation → Webhooks) |
| Delivery | Uspacy relay POSTs to `sns_subscription_url`, for every portal that installed the app | HTTP POST to a URL entered in that portal's settings |
| Received by this template | Yes: `POST /{integration}/v1/uspacy/webhooks` | No |

If a question is about an app or this template, it is about application webhooks. Portal webhooks are documented below only so they are not confused with them.

## Application webhooks

Uspacy services publish events to AWS SNS. An internal Uspacy relay reads them and POSTs each event to the `sns_subscription_url` of every app that is subscribed to that event's service and installed on the event's portal. The app never talks to AWS.

### Subscribing

The Uspacy team adds the app to a list per service, by hand: `crm`, `tasks`, `messenger`, `news_feed`. There is no API and no confirmation step. Events go only to active apps, only for portals where the app is installed.

`messenger` events are forwarded only when they concern an external line (see [external-lines.md](external-lines.md)). Internal chats between portal users are never sent to apps.

### Request

- `POST {sns_subscription_url}`, no `Content-Type` header: read the raw body and `json_decode` it.
- No `Authorization` header, no signature to verify, no `SubscriptionConfirmation` to handle.
- The body is the SNS notification envelope. The event itself is a **JSON string** in `Message`, so decode twice:

```json
{
  "Type": "Notification",
  "MessageId": "...",
  "TopicArn": "...",
  "Message": "{\"data\":{\"service\":\"crm\",\"domain\":\"acme.uspacy.com\", ...}}",
  "Timestamp": "...",
  "SignatureVersion": "...",
  "Signature": "...",
  "SigningCertURL": "...",
  "UnsubscribeURL": "..."
}
```

- Inside `Message`: `data.service` (one of the four services), `data.domain` (the portal; find it together with your integration code). For `messenger`, `data.entity` is the message or chat, with `externalLine` or `externalLines`. Other fields depend on the service and are not published: log real events on Stage.
- The envelope's `Signature` fields are copied from SNS; do not rely on them.

### Response and retries

- Return `2xx` quickly and queue slow work.
- The relay retries on network errors, `408`, `429` and `5xx` (except `501`): up to 10 retries, 10–60 s apart. Any other status, including `4xx`, is final, and the event is lost.
- Deliveries run concurrently, so order is not guaranteed, and a retry after a timeout can deliver an event twice. Deduplicate by `MessageId`.
- Return `200` for events you ignore (e.g. unknown portal), so they are not retried.

### Security

The endpoint is unauthenticated: anyone who knows the URL can post a fake event. Use a hard-to-guess URL, and treat the event as a notification. Before acting on it, fetch the current state of the record from the Uspacy API with the portal's token.

## Portal outgoing webhooks

A portal owner/admin creates them by hand in the UI (Automation → Webhooks), per portal. They are not part of a Marketplace app. The same settings are exposed in the admin-only API `/company/v1/webhooks`:

```json
POST /company/v1/webhooks
{"url": "https://example.com/hook", "active": true, "events": [{"service": "crm", "type": "entity_crm", "table_name": "leads", "actions": ["create", "update", "update_stage"]}]}
```

- `service`: `crm`, `tasks`, `news_feed`, `comments`. `type`: `post`, `comment`, `task`, `entity_crm`. `actions`: `create`, `update`, `delete`, `update_stage`.
- UI objects: lead, deal, contact, company, case, task, post, comment. Stage change is not available for contacts, companies and cases.
- `url` is unique per portal, public, with protocol.
- A new webhook is **unconfirmed**: Uspacy POSTs a confirmation link to the URL and your server must open it (SNS-style confirmation).
- Manage: `GET /company/v1/webhooks`, `PATCH .../{id}/toggle`, `PATCH .../{id}/repeat`, `DELETE .../{id}`.

## Portal incoming webhooks

A portal owner/admin creates one by hand in the UI (Automation → Webhooks), selects which tools it can access (CRM, Tasks, Chats, ...), and gets a unique URL. That URL replaces the base URL and token:

```
GET  {webhook_url}/tasks/v1/tasks/{taskId}
POST {webhook_url}/crm/v1/entities/leads
```

- The creating user's CRM permissions still apply (e.g. they need rights to create leads).
- Limit: 120 requests per minute.
- Treat the URL as a secret.
- Example: creating a lead from a website form. See [api.md](api.md#crm) for the body.
