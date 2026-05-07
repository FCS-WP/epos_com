# Frontend Performance TODO

Tracking the performance work for EPOS.com — focused on page load, LCP, render-blocking assets, JS/CSS bundle size, and third-party tag overhead.

Scope: custom code only — `zippy-child` theme, `zippy-core`, `zippy-pay`, `epos-affiliate` plugins. We will work through these **one by one**, in priority order.

---

## Status legend
- [ ] pending
- [~] in progress
- [x] done

---

## 1. [x] Restrict `zippy-pay` `global_style()` to checkout-only

**Outcome:** Initial assumption (1.1 MB JS site-wide) was wrong — JS SDKs (Antom 1.1 MB, Adyen 1.2 MB, 2C2P) were already gated by `is_checkout()`. The actual leak was `web.min.css` (6.7 KB) loading site-wide because `global_style()` was called at plugin-load time instead of via `wp_enqueue_scripts`.

**Fix:** [src/wp-content/plugins/zippy-pay/core/zippy-pay-core.php:206-219](../src/wp-content/plugins/zippy-pay/core/zippy-pay-core.php#L206-L219)
- Wrapped enqueue in a `wp_enqueue_scripts` hook.
- Gated by `is_checkout()`.
- Replaced cache-busting `time()` with stable `'7.0.1'` version string.

**Open follow-ups (deferred, not blockers):**
- `adyen-test.min.js` (1.2 MB) is in the repo but never enqueued — candidate for deletion.
- Per-gateway JS splitting was unnecessary; current setup already loads only the active gateway's SDK on checkout.

---

## 2. [x] Split `epos.min.css` site-wide bundle (phased)

**Outcome — measured:**

| Bundle | Before | After | Δ |
|---|---|---|---|
| `epos.min.css` (site-wide) | 306 KB | **184 KB** | **−122 KB (−40%)** |
| `epos-360.min.css` (new, route-scoped) | — | 34 KB | only on `/my/epos360` |
| `woocommerce-flow.min.css` (new, route-scoped) | — | 57 KB | only on cart/checkout/order-received |

Every non-WC/non-epos360 page is now **122 KB lighter**. WC pages still total 241 KB (< 306 KB before) and the route bundle caches separately.

**Files changed:**
- New: [assets/sass/epos-360.scss](../src/wp-content/themes/zippy-child/assets/sass/epos-360.scss) — bundles `_main-epos360` + sections + root `_gsap` (epos360-only selectors).
- New: [assets/sass/woocommerce-flow.scss](../src/wp-content/themes/zippy-child/assets/sass/woocommerce-flow.scss) — bundles `_epos-cart-page` + `_epos-checkout` + `_thankyou`. Mirrors the `%inter-important` placeholder inline so it compiles standalone.
- [assets/sass/app.scss](../src/wp-content/themes/zippy-child/assets/sass/app.scss) — removed the 5 split-out imports.
- [webpack.config.js](../webpack.config.js) — added `epos-360` and `woocommerce-flow` entries; switched MiniCssExtractPlugin filename to `[name].min.css`.
- [includes/shin_custom.php:4-43](../src/wp-content/themes/zippy-child/includes/shin_custom.php#L4-L43) — conditional enqueue (epos360 by URI prefix matching header.php; WC by `is_cart() || is_checkout() || is_wc_endpoint_url('order-received')`).

**Notes / known artifacts:**
- Webpack emits empty `epos-360.min.js` and `woocommerce-flow.min.js` (CSS-only entries — webpack quirk). Harmless, never enqueued.
- Build first failed because `_epos-checkout.scss` / `_epos-cart-page.scss` extend `%inter-important` defined in `_fonts.scss`. Fixed by mirroring the 4-line placeholder into `woocommerce-flow.scss`.

**Deferred (Phase 2B — not done):**
- Smaller page partials (`_my-video-hub`, `_my-career-workable`, `_epos-bluetap-onboarding`, etc., ~1,500 lines) still in main bundle. Re-evaluate after measuring real-world LCP impact of phase 2A.

---

## 3. [x] Eliminated `ob_start` + DOMDocument pass on every page

**Outcome:** Removed entirely. Investigation showed the buffer pass was doing three things, only one of them potentially useful:

| Op | Status | Notes |
|---|---|---|
| Add `name="epos"` to every `<a>` and `<button>` | **Dead code** | Repo-wide grep confirmed nothing reads this attribute. 59 instances on the homepage = pure HTML bloat. |
| Add `width`/`height` to `<img>` from cached metadata | Mostly redundant | 50 of 59 homepage images already had dimensions from WP core / Flatsome / `wp_get_attachment_image()`. |
| `getimagesize()` filesystem fallback | Redundant | Per-image filesystem read on cold cache. The 9 images that lacked dimensions are either lazy-loaded data-URI placeholders (can't be helped) or below-the-fold footer icons (no LCP impact). |

**Fix:** [src/wp-content/themes/zippy-child/includes/optimize-page-speed.php](../src/wp-content/themes/zippy-child/includes/optimize-page-speed.php) — deleted the entire `template_redirect` ob_start callback + its two helpers (`custom_optimize_get_local_image_path`, `custom_optimize_get_image_dimensions`). File: 380 → 246 lines.

**Estimated savings per request:**
- Eliminates DOMDocument `loadHTML()` + `saveHTML()` over 119 KB of HTML (~30–80ms PHP CPU).
- Eliminates 5–10 MB peak memory allocation.
- Eliminates 1 DB query per uncached image (`attachment_url_to_postid()`).
- Removes ~2.5 KB of `name="epos"` HTML bytes from every response.
- Total: rough estimate of 50–150ms TTFB saved per page.

**Follow-up to watch:** if Lighthouse CLS regresses on a specific template, the targeted fix is a `the_content` regex filter for that template — much cheaper than DOMDocument over the whole page. Don't pre-empt; measure first.

---

## 4. [ ] Consolidate / defer the 3 tracking systems (GTM + FB Pixel + PostHog)

**Files:**
- `src/wp-content/themes/zippy-child/includes/gtm_tracking/`
- `src/wp-content/themes/zippy-child/includes/fb_tracking/`
- `src/wp-content/themes/zippy-child/includes/posthog_tracking/`
- Inline injection: `includes/epos_my_custom_flow/epos_custom_scripts.php:2`

**Problem:** All three trackers fire in head / early footer with no consent gating or dedup. GTM can host Pixel + PostHog instead of three separate inline scripts.

**Goal:** Reduce render-blocking scripts and total tracker JS weight.

**Actions:**
- Map what each tracker actually captures (events, conversions, user ID).
- Decide consolidation approach: route Pixel + PostHog through GTM tags, or keep separate but defer.
- Add consent gating if applicable (region/legal).
- Move what can be moved out of `<head>`; defer/async where possible.
- Verify analytics integrity (no event loss) post-change.

---

## 5. [x] Stopped shipping zippy-core/web.min.js (86 KB duplicated jQuery) on frontend

**Outcome:** Bigger win than originally scoped. Investigation revealed:

- The frontend JS source ([assets/web/js/index.js](../src/wp-content/plugins/zippy-core/assets/web/js/index.js)) is a **3-line empty jQuery wrapper** — does literally nothing.
- Webpack was bundling the **entire jQuery 3.7.1 library** (86 KB) just to satisfy the `jQuery(function($){})` reference.
- WordPress core already loads its own `wp-includes/js/jquery/jquery.min.js` on every page → **two copies of jQuery were shipping**.
- Repo-wide grep confirmed nothing depends on the `core-web-scripts` handle.

**Fix:** [src/wp-content/plugins/zippy-core/src/core/zippy-settings.php:119-130](../src/wp-content/plugins/zippy-core/src/core/zippy-settings.php#L119-L130) — removed the `wp_enqueue_script('core-web-scripts', …)` call entirely. Comment in place explaining why and how to re-add if frontend JS is ever needed.

**Bonus:** While there, gated the 896 B `core-web-styles` (built from `_custom_checkout_page.scss`) to `is_checkout()` only — was loading on every page despite being checkout-specific.

**Estimated savings on every non-checkout page:**
- −86 KB JS over the wire (one fewer round-trip; HTTP/2 multiplexes but the bytes still need parsing).
- −896 B CSS.
- One fewer `<script>` to parse and execute.

**No build needed** — both files already exist in dist/ and the change is purely on the PHP enqueue side.

---

## 6. [ ] Audit jQuery dependency on `epos.min.js`

**Files:**
- `src/wp-content/themes/zippy-child/includes/shin_custom.php:2` (enqueue)
- `src/wp-content/themes/zippy-child/assets/src/js/app.js`
- `webpack.config.js` (jQuery ProvidePlugin)

**Problem:** `epos-scripts-js` declares jQuery as a dependency. Webpack also has `ProvidePlugin` for `$`/`jQuery`. Need to confirm jQuery is still actually needed and not double-loaded with Flatsome's jQuery.

**Goal:** Drop the jQuery dep if possible; otherwise ensure single load.

**Actions:**
- Grep `assets/src/js/` for `$(`, `jQuery(`, `.ajax(`, etc.
- If usage is small, port to vanilla and remove dep.
- If kept, confirm only one jQuery is loaded site-wide.

---

## Cross-cutting
- Establish a baseline: WebPageTest / Lighthouse run on key pages (home, /shop, /product/[example], /cart, /checkout, /my/epos360) before starting #1. Re-run after each item.
- Avoid mixing items in a single PR — one perf change per PR for clean before/after.
