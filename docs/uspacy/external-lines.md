# External lines: connecting a messenger or channel

An **external line** is the CRM side of a connection to an outside channel (Telegram bot, Instagram account, WhatsApp number, SMS gateway, your own widget). Messages flow between the channel and Uspacy chats through it. Usually one connection = one line; an app that lets a user add three Instagram accounts creates three lines.

Source article (Ukrainian): <https://uspacy.ua/support/knowledgebase/rozrobka-zastosunku-dlya-kramnytsi-z-vykorystannyam-zovnishnikh-linii/>.

## Prerequisites

- A registered Marketplace app ([marketplace-app.md](marketplace-app.md)) with `sns_subscription_url` and the `messenger` webhook type, so you receive messages written by Uspacy users.

## Flow

1. **Create the line** when the user connects an account:
   ```json
   POST /messenger/v1/external-lines
   {"name": "Support Line", "icon": "https://example.com/icon.png", "phoneNumber": "+1234567890", "externalId": "support-line-001"}
   ```
   - `name`: unique.
   - `icon`: image URL, shown on the chat icon in the external lines tab.
   - `phoneNumber`: if the channel has none, repeat `name`.
   - `externalId`: your own unique ID for the connection.
   - Store the returned `id`.
   Other operations: `GET /messenger/v1/external-lines`, `GET|PATCH|DELETE /messenger/v1/external-lines/{id}`. Deleting a line disconnects it.

2. **Find or create a chat** when a message arrives from an external sender:
   - Look up: `GET /messenger/v1/chats/findByMeta?type=telegram&value=12345`.
   - Create if missing:
     ```json
     POST /messenger/v1/chats
     {"type": "EXTERNAL", "members": [], "externalLines": ["<line id>"], "meta": [{"type": "telegram", "value": "12345"}], "name": "John Doe"}
     ```
   - `meta` links the chat to CRM records. `meta[].type`: `phone`, `widgetUser`, `username`, `facebook`, `instagram`, `telegram`, `twitter`, `viber`, `whatsapp`. `meta[].value`: phone number or the sender's ID.
   - Store the mapping between the external conversation and the chat `id`.

3. **Upload attachments** (if any): `POST /files/v1/files`, `multipart/form-data` with `files[]` and `entityType`. Use the returned file objects in `attachedFiles`.

4. **Post the incoming message** into the chat:
   ```json
   POST /messenger/v1/messages
   {"chatId": "<chat id>", "message": "Hello", "type": "MESSAGE", "externalAuthorId": "12345", "externalId": "<message id in the channel>", "parentMessage": "<uspacy message id, for replies>", "attachedFiles": []}
   ```
   `type`: `MESSAGE`, `AUDIO`, `VIDEO`, `GIF`, `SYSTEM`. Markdown is allowed in `message`.

5. **Receive replies written in Uspacy.** They arrive at `sns_subscription_url` ([webhooks.md](webhooks.md#application-webhooks)); only external-line messenger events are forwarded. Decode `Message`, check it is a message on your external line, find your conversation by `chatId`, and send it to the channel. Skip messages your app posted itself, or you will echo them back.

6. **Uninstall**: delete or stop using the portal's lines and mappings.

## Unverified

- Required `members` value for `EXTERNAL` chats (the schema requires the field; an empty array is an assumption).
- Exact messenger event payload: log a real one on Stage (the template's example webhook route does this).
- `phoneNumber` is required on create but absent from the update schema.
