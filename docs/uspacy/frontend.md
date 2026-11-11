# App UI inside the portal

The portal loads an app's UI as a [Webpack Module Federation](https://webpack.js.org/plugins/module-federation-plugin/) remote, not an iframe. This repo is backend only; start the frontend from [uspacy-application-template](https://github.com/Uspacy/uspacy-application-template). A real example: [uspacy-birthday-widget](https://github.com/Uspacy/uspacy-birthday-widget).

Source article (Ukrainian): <https://uspacy.ua/support/knowledgebase/yak-rozrobyty-zastosunok-dlya-uspacy/>.

## Setup

Add `ModuleFederationPlugin` to the webpack config:

- `name`: the app name.
- `filename`: the entry file the portal loads, normally `remoteEntry.js`.
- `exposes`: modules the portal can render. Each must `export default` a React component.
- `shared`: packages shared with the portal as singletons. Copy the list from the template; mismatches break hooks and context.

Expose a module named `settings`: the Marketplace shows it on the app's settings page.

React must be 18.0.0 or newer.

## Publishing

Host the build publicly and send the Uspacy team:

- the `remoteEntry.js` URL, e.g. `https://your-domain.com/remoteEntry.js`;
- the `name` from the plugin config;
- the list of exposed modules.

## Talking to the backend

The frontend obtains the `integration_token` (JWT signed with your `client_secret`) and sends it as `Authorization: Bearer` to this backend. The backend resolves the portal from it; see [marketplace-app.md](marketplace-app.md#jwts-sent-to-the-app). Exactly how the frontend obtains it is in the frontend template.
