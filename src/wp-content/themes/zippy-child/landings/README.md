# Landings

Self-contained landing pages for ad campaigns and lead capture. Each landing
is a folder with its own template, content, styles, scripts, and assets.
Webpack auto-discovers each folder; PHP auto-registers each as a selectable
WordPress page template.

## Why this folder exists

Marketing pages have different needs than the main site:

- They iterate **fast** and have **diverse layouts** — no two campaigns
  look alike.
- They must be **lightweight** — no Flatsome chrome, no main-site CSS
  baggage. Ad clicks are paid, fast loads matter.
- Their **content changes frequently**, but the markup logic doesn't.

This folder is set up to make all of those easy.

## Creating a new landing

1. **Copy `subscription/` as a starting point**, rename to your slug:
   ```
   cp -r landings/subscription landings/my-new-campaign
   ```
2. **Edit `template.php`** — change the `Template Name:` header at the top.
   That's the label that shows up in the WP page editor's "Template" dropdown.
   **Convention:** prefix with `Ads —` so all landings cluster together in
   the dropdown (e.g. `Ads — Subscription`, `Ads — 360 Launch`).
3. **Edit `content.json`** — replace text, image IDs, URLs.
4. **Edit `style.scss`** and **`script.js`** if the design diverges.
5. **Edit/add partials** in `partials/` — keep each one small (< 50 lines).
6. **Build:** `npm run build`. Webpack picks up the new folder automatically.
7. **In WP admin:** create a Page → set its slug → choose your template
   from the "Template" dropdown → publish.

## Folder structure

```
landings/
├─ loader.php                      ← shared infra; don't edit per-landing
├─ subscription/                   ← one folder per campaign
│  ├─ template.php                 ← required: WP "Template Name:" header
│  ├─ content.json                 ← optional: page copy + image IDs
│  ├─ style.scss                   ← optional: page-only styles
│  ├─ script.js                    ← optional: page-only JS
│  ├─ partials/                    ← optional: chunked markup
│  │  ├─ hero.php
│  │  ├─ features.php
│  │  ├─ form.php
│  │  └─ footer-cta.php
│  ├─ libs/                        ← optional: 3rd-party files (HubSpot,
│  │                                  Calendly snippets) included manually
│  └─ assets/                      ← optional: committed static images
└─ _shared/                        ← (optional) folders prefixed with _
                                      are skipped by webpack and the loader,
                                      use them for shared partials.
```

## What the loader gives you

`loader.php` exposes these helpers usable inside `template.php` and partials:

- **`landing_content($slug = null)`** — returns `content.json` as an array
  for the current landing (or the slug you pass).
- **`landing_partial($name)`** — includes `partials/{$name}.php` from the
  current landing's folder.
- **`landing_image($attachment_id, $size = 'full', $attrs = [])`** —
  renders a Media Library image as `<img>` with WP's standard `srcset`,
  `alt`, etc. Use this anywhere you'd use `<img>` so images stay
  responsive and lazy.
- **`landings_current_slug()`** — returns the active landing's slug
  (or `null` outside a landing).

## How content.json works

Content lives in JSON, not in PHP/markup. To swap copy or images:

1. Open `content.json`.
2. Edit any string.
3. Commit + deploy.

**No rebuild required for content changes** — JSON is read live on each
request. Rebuild only when SCSS or JS changes.

### Images

- Upload via WP admin → Media Library.
- Click the uploaded image, copy the numeric **attachment ID** (visible in
  the URL or sidebar).
- Paste into the `image_id` field in `content.json`.
- The partial renders it via `landing_image($id)` — full WP `srcset`
  support, lazy loading, alt text from Media Library.

To remove an image, set `"image_id": 0` — the partial gracefully omits it.

### Schema is whatever you want

Each landing's `content.json` shape is defined by its own partials. There
is **no central schema** — when a design changes, edit the JSON shape and
the partials together. No DB migration, no admin UI to update.

## Asset isolation

The loader does two things on a landing page:

1. **Dequeues** the main-site CSS (`epos-style-css`) and JS
   (`epos-scripts-js`).
2. **Enqueues** only the landing's compiled bundle.

So `dist/landings/subscription.min.css` (≈ 5 KB) loads instead of the main
`epos.min.css` (≈ 186 KB). Subscription's `script.js` is the only JS on
the page (no jQuery, no Slick, no checkout machinery).

If you need a specific WP/WC feature on a landing, enqueue it explicitly
in `template.php` after `wp_head()`.

## Shared design tokens

Brand colors, fonts, spacing, breakpoints, and radius live in
[`_shared/_tokens.scss`](_shared/_tokens.scss). Each landing's `style.scss`
should `@import "../_shared/tokens"` and use the variables
(e.g. `$ld-primary`, `$ld-space-4`, `$ld-bp-tablet`) instead of hardcoding.

```scss
// landings/{slug}/style.scss
@import "../_shared/tokens";

.landing.landing--my-campaign {
  background: $ld-bg;
  color: $ld-text;

  .my-cta { background: $ld-primary; padding: $ld-space-3 $ld-space-5; }
}
```

These are SCSS `$variables` (compile-time), so each landing only ships
the tokens it actually references — no shared runtime cost.

**To diverge for one campaign**, override a token *before* importing:

```scss
$ld-primary: #ff6600;
@import "../_shared/tokens";  // picks the override
```

**To change the brand**, edit `_shared/_tokens.scss` and rebuild.
All landings update.

## Animations and 3rd-party libraries

Two patterns:

### Bundled in via webpack
Use this for libraries you control (GSAP, Lottie, Framer-style helpers):
```js
// landings/subscription/script.js
import { gsap } from "gsap";
gsap.from(".hero", { y: 40, opacity: 0, duration: 0.8 });
```
Webpack bundles only what this landing imports — the rest of the site is
unaffected.

### Side-loaded snippets
For embeds that ship as standalone scripts (HubSpot, Calendly, Typeform):
- Drop the snippet into `template.php` directly, OR
- Put a file in `libs/` and `<script src="...">` it from `template.php`.

The included `subscription/script.js` already shows a pattern for **lazy-
mounting the HubSpot form** — load the embed script only when the form
section enters the viewport. Use the same pattern for any heavy third-party.

## Caching

- **Browser cache:** the loader uses `filemtime()` for the asset version,
  so URLs change only when the file changes. Browser caches forever.
- **WP Rocket:** these are real WP page renders — Rocket caches them
  automatically. No special config.
- **CDN:** the dist files have content-derived versions, safe for any
  CDN long-cache policy.

## Conventions

- **One folder = one landing.** Don't share between folders. Copy-paste
  is fine; the cost of diverging styles later is lower than over-engineering
  shared abstractions now.
- **Partials < 50 lines each.** If a partial grows, split it.
- **Content in JSON, logic in PHP, styles in SCSS.** Keep the boundaries
  clean.
- **Folders starting with `_`** are skipped by both webpack and the loader.
  Use this for shared utilities that aren't themselves landings.

## Removing a landing

```
rm -rf landings/old-campaign
npm run build      # CleanWebpackPlugin removes the dist files too
```
The page in WP admin will fall back to the default template when its
template file is gone — review/delete the WP page separately.
