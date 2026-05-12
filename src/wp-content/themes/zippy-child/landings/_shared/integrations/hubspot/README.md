# HubSpot integration (landings)

Server-side proxy that lets landing-page forms submit directly to HubSpot
through our own WP REST endpoint, instead of loading HubSpot's heavy embed
script in the browser.

## Architecture

```
Browser                       WordPress                    HubSpot
─────────                     ─────────                    ───────
<form>  ──submit──>  POST /wp-json/landings/v1/      ──>   POST submissions/v3/
                     hubspot/form-submit                    integration/submit/
  ^                  (nonce + origin + honeypot                {portal}/{form}
  │                   + rate limit + sanitize)
  └────── response ── result + field errors ◄──── HS response
```

Why a proxy and not a direct browser → HubSpot call:
- We can validate, rate-limit, and add bot defenses before HubSpot sees the
  submission.
- Future hooks (notify Slack, log to a custom table, branch by UTM) plug in
  here without changing landings.
- The HubSpot portal/form ids stay server-side; the browser sees only the
  endpoint URL + a per-request WP nonce.

## Files

| file | role |
| --- | --- |
| `class-form-client.php` | HTTP client. Wraps the HubSpot v3 submission endpoint. No WP coupling beyond `wp_remote_post`. |
| `class-form-api.php` | REST controller. Registers `POST /wp-json/landings/v1/hubspot/form-submit`. Self-instantiates at file bottom. |

## Configuring a landing

Each landing's `content.json` declares its HubSpot form:

```json
{
  "form": {
    "section_title": "Talk to a specialist",
    "intro": "We will reply within one business day.",
    "submit_label": "Get started",
    "success_message": "Thanks — we'll be in touch.",
    "hubspot_portal_id": "12345",
    "hubspot_form_id":   "abc-123-def-456"
  }
}
```

The portal/form ids are read **server-side** from `content.json`, not trusted
from the request. A landing without these set returns a 500 from the API.

## Form HTML — the landing controls it

Each landing writes its own `<form>` markup so design is fully free. Only
the behavior is shared. Conventions:

- The form must have `data-landing-form="hubspot"` (the landing's
  `script.js` queries by that attribute).
- Field `name` attributes are passed through to HubSpot **as-is**, so they
  must match the HubSpot internal property names (`firstname`, `lastname`,
  `email`, `company`, etc., or your custom property internal names).
- A honeypot input named `website_url` should be present and hidden via CSS.

Example minimum form:

```html
<form data-landing-form="hubspot" novalidate>
  <input type="email" name="email" required />
  <input type="text" name="firstname" />
  <div class="landing__form-honeypot" aria-hidden="true">
    <input type="text" name="website_url" tabindex="-1" autocomplete="off" />
  </div>
  <button type="submit">Submit</button>
  <p class="landing__form-status" role="status" aria-live="polite"></p>
</form>
```

## Wiring up the JS

Each landing's `script.js` imports and binds the bridge:

```js
import { LandingForm } from "../_shared/form-bridge";

const formEl = document.querySelector('form[data-landing-form="hubspot"]');
new LandingForm({
  formElement: formEl,
  onSubmitStart: ()      => { /* show "Submitting…" */ },
  onSuccess:     (data)  => { /* hide form, show thank-you */ },
  onError:       (msg, fieldErrors) => { /* show error UI */ },
}).bind();
```

The `LANDINGS_FORM_BRIDGE` global (endpoint, nonce, slug, page meta) is
injected automatically by `landings/loader.php`. Don't set it yourself.

## Defenses at the API layer

- **WP nonce** (`X-WP-Nonce` header) — CSRF protection. Auto-rotated per
  user session, scope `wp_rest`.
- **Origin check** — request `Origin` header host must match site host.
  Stops cross-domain bots that have stolen a nonce.
- **Honeypot field** — `honeypot` field in payload must be empty. If filled,
  we return 200 (faking success) so bots don't learn the trap.
- **Per-IP rate limit** — 10 submissions per 10 minutes, transient-backed.
  Tune via the `RATE_LIMIT` / `RATE_WINDOW` constants in
  `class-form-api.php`.
- **Sanitize** — `sanitize_email` on `email`, `sanitize_text_field` on the
  rest. No HTML, no scripts.

## What this integration does NOT do

- **Does not load HubSpot's tracking cookie.** The `hutk` cookie is only
  read if HubSpot's tracking script is loaded separately on the page.
  Our bare landings don't load it, so attribution is by IP/UTM only.
- **Does not use the HubSpot CRM API.** This is the public form-submit
  endpoint — anyone with portal+form ids can submit. CRM operations
  (create-contact, attach-to-deal, custom property updates beyond the form
  schema) need a private app token; not implemented.
- **Does not retry failed submissions.** If HubSpot returns 5xx, the
  submission is lost. If reliability matters, add a leads-log table.

## Testing locally

Submit via curl (you'll need a valid nonce — easiest is to copy one from
a real landing page's source):

```bash
curl -X POST http://localhost:68/wp-json/landings/v1/hubspot/form-submit \
  -H "Content-Type: application/json" \
  -H "Origin: http://localhost:68" \
  -H "X-WP-Nonce: <copy-from-page-source>" \
  --data '{
    "landing_slug": "subscription",
    "fields": {"email": "test@example.com", "firstname": "Test"},
    "honeypot": ""
  }'
```

Expect `{"success": true, ...}` if the landing has portal/form ids set.

## Adding another vendor

Drop a sibling folder, e.g. `landings/_shared/integrations/salesforce/`,
with its own `class-*.php` files. The integrations loader auto-discovers
it on next request — no registration step.
