# Uspacy integration knowledge base

Concepts and flows for building an app (integration) for the Uspacy CRM. Written for developers and their AI coding assistants. For exact endpoint schemas, use the live API reference (below) instead of copying it here.

| Topic | File |
|---|---|
| App registration, install/uninstall, JWTs, tokens | [marketplace-app.md](marketplace-app.md) |
| Calling the Uspacy REST API: hosts, auth, CRM, pagination, errors, PHP SDK | [api.md](api.md) |
| Connecting a messenger/channel through external lines | [external-lines.md](external-lines.md) |
| Application webhooks (AWS SNS, what this app receives) vs portal webhooks (set up in the UI) | [webhooks.md](webhooks.md) |
| App UI inside the portal (Webpack Module Federation) | [frontend.md](frontend.md) |

## Looking up the API reference

The reference at <https://uspacy.readme.io> is machine-readable:

- <https://uspacy.readme.io/llms.txt> lists every reference page with a one-line summary.
- Append `.md` to any reference page URL to get Markdown with its OpenAPI block, e.g. <https://uspacy.readme.io/reference/post_crm-v1-entities-entity.md>.

Some reference schemas contradict each other (noted in the files here). When in doubt, check the PHP SDK source in `vendor/uspacy/uspacy-php-sdk/src` or try the call on a Stage portal.

## Other sources

- [uspacy-php-sdk](https://github.com/Uspacy/uspacy-php-sdk): PHP client used by this template.
- [uspacy-application-template](https://github.com/Uspacy/uspacy-application-template): React frontend template.
- [uspacy-birthday-widget](https://github.com/Uspacy/uspacy-birthday-widget): real frontend app.
- [uspacy-go-sdk](https://github.com/Uspacy/uspacy-go-sdk): Go SDK (in development).
- Knowledge base articles (Ukrainian): [external lines](https://uspacy.ua/support/knowledgebase/rozrobka-zastosunku-dlya-kramnytsi-z-vykorystannyam-zovnishnikh-linii/), [frontend app](https://uspacy.ua/support/knowledgebase/yak-rozrobyty-zastosunok-dlya-uspacy/), [webhooks](https://uspacy.ua/support/knowledgebase/vebkhuky-yak-tse-pratsyuye/), [lead via webhook](https://uspacy.ua/support/knowledgebase/webhooks-how-to-create-lead/).
- Support: support@uspacy.com.
