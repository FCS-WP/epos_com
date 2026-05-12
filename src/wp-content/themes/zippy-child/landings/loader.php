<?php
/**
 * Landings loader.
 *
 * Auto-discovers landing pages under /landings/{slug}/ and:
 *   1. Registers each landing's template.php with WordPress so it appears
 *      in the page editor's "Template" dropdown.
 *   2. When that template is active on the rendered page, dequeues the main
 *      site bundles and enqueues only the landing's own CSS/JS.
 *   3. Provides helpers used inside partials: landing_content() to read
 *      content.json, landing_image() to render media-library images.
 *
 * Conventions for a landing folder:
 *   landings/{slug}/
 *     template.php     ← required, has WP "Template_Name:" header
 *     content.json     ← optional, key/value content used by partials
 *     style.scss       ← optional, compiled to dist/landings/{slug}.min.css
 *     script.js        ← optional, compiled to dist/landings/{slug}.min.js
 *     partials/        ← optional, included by template.php
 *     libs/            ← optional, side-loaded 3rd-party assets
 *     assets/          ← optional, page-specific committed images/fonts
 */

if (! defined('ABSPATH')) exit;

if (! defined('LANDINGS_DIR')) {
    define('LANDINGS_DIR', THEME_DIR . '-child/landings');
}
if (! defined('LANDINGS_URL')) {
    define('LANDINGS_URL', THEME_URL . '-child/landings');
}
// Webpack outputs CSS under dist/css/landings/{slug}.min.css and JS under
// dist/js/landings/{slug}.min.js (both follow the [name] template pattern).
if (! defined('LANDINGS_DIST_CSS_URL')) {
    define('LANDINGS_DIST_CSS_URL', THEME_URL . '-child/assets/dist/css/landings');
}
if (! defined('LANDINGS_DIST_CSS_DIR')) {
    define('LANDINGS_DIST_CSS_DIR', THEME_DIR . '-child/assets/dist/css/landings');
}
if (! defined('LANDINGS_DIST_JS_URL')) {
    define('LANDINGS_DIST_JS_URL', THEME_URL . '-child/assets/dist/js/landings');
}
if (! defined('LANDINGS_DIST_JS_DIR')) {
    define('LANDINGS_DIST_JS_DIR', THEME_DIR . '-child/assets/dist/js/landings');
}

/**
 * Scan landings/ once per request and return [slug => absolute_path] for
 * each subdirectory containing a template.php.
 *
 * @return array<string,string>
 */
function landings_discover()
{
    static $cache = null;
    if ($cache !== null) {
        return $cache;
    }

    $cache = array();
    $entries = glob(LANDINGS_DIR . '/*', GLOB_ONLYDIR);
    if (! is_array($entries)) {
        return $cache;
    }

    foreach ($entries as $dir) {
        $slug = basename($dir);
        // Skip _shared, _utils, etc. — anything starting with underscore.
        if (strpos($slug, '_') === 0) continue;
        if (! file_exists($dir . '/template.php')) continue;

        $cache[$slug] = $dir;
    }

    return $cache;
}

/**
 * Read the "Template_Name:" header from a landing's template.php.
 *
 * @param string $slug
 * @return string
 */
function landings_template_label($slug)
{
    $landings = landings_discover();
    if (! isset($landings[$slug])) return $slug;

    $contents = file_get_contents($landings[$slug] . '/template.php', false, null, 0, 8192);
    if ($contents === false) return $slug;

    // The needle is built from concatenated parts so the literal magic
    // string never appears in this file — otherwise Flatsome's theme
    // scanner would pick THIS file up as a selectable page template.
    $needle = 'Template' . ' Name:';
    if (preg_match('/' . preg_quote($needle, '/') . '[ \t]*([^\r\n]+)/', $contents, $m)) {
        return trim($m[1]);
    }

    return $slug;
}

/**
 * Register each landing's template.php as a selectable page template.
 * WordPress normally only scans theme root for "Template_Name:" headers;
 * we add ours via the theme_page_templates filter.
 */
add_filter('theme_page_templates', function ($templates) {
    foreach (landings_discover() as $slug => $_dir) {
        $key = 'landings/' . $slug . '/template.php';
        $templates[$key] = landings_template_label($slug);
    }
    return $templates;
});

/**
 * Tell WordPress where to find the file when a landing template is selected.
 */
add_filter('template_include', function ($template) {
    if (! is_page()) return $template;

    $assigned = get_page_template_slug();
    if (! $assigned) return $template;

    if (strpos($assigned, 'landings/') !== 0) return $template;

    $candidate = THEME_DIR . '-child/' . $assigned;
    if (file_exists($candidate)) {
        return $candidate;
    }

    return $template;
});

/**
 * Detect which landing slug (if any) is rendering the current request.
 *
 * @return string|null
 */
function landings_current_slug()
{
    static $resolved = false;
    static $slug = null;
    if ($resolved) return $slug;
    $resolved = true;

    if (! is_page()) return null;
    $assigned = get_page_template_slug();
    if (! $assigned) return null;
    if (strpos($assigned, 'landings/') !== 0) return null;

    $parts = explode('/', $assigned);
    $slug = isset($parts[1]) ? $parts[1] : null;
    return $slug;
}

// Load integrations (HubSpot form bridge, future vendors).
require_once __DIR__ . '/_shared/integrations/loader.php';

/**
 * Minimal <head> output for landing templates.
 *
 * REPLACES wp_head() entirely. We do not call wp_head() because Flatsome,
 * WooCommerce, and dozens of plugins hook into it to inject CSS, JS, fonts,
 * mobile-menu markup, and tracking. Landings are bare single pages.
 *
 * Emits only:
 *   - charset + viewport
 *   - <title> from WP page title
 *   - <link rel="canonical">
 *   - <meta name="description"> from page excerpt (if set)
 *   - Poppins font (preconnect + stylesheet)
 *   - landing's compiled CSS bundle
 *   - intl-tel-input CSS (if landing's content.json declares phone_countries)
 *
 * If a campaign needs tracking (GTM, FB Pixel, GA), paste the snippet
 * directly into the landing's template.php — do NOT wire it through here.
 *
 * @return void
 */
function landing_head()
{
    $slug = landings_current_slug();
    if (! $slug) return;

    $landing_data = landing_content($slug);
    $needs_phone  = ! empty($landing_data['form']['phone_countries']);

    // Charset / viewport
    echo '<meta charset="' . esc_attr(get_bloginfo('charset')) . '">' . "\n";
    echo '<meta name="viewport" content="width=device-width, initial-scale=1">' . "\n";

    // Title
    echo '<title>' . esc_html(wp_get_document_title()) . "</title>\n";

    // Canonical
    if (function_exists('rel_canonical')) {
        rel_canonical();
    }

    // Description from page excerpt (set in WP page editor → Excerpt panel).
    $excerpt = get_the_excerpt();
    if ($excerpt) {
        echo '<meta name="description" content="' . esc_attr(wp_strip_all_tags($excerpt)) . '">' . "\n";
    }

    // Poppins (form spec) — preconnect first so the font fetch isn't TCP-blocked.
    echo '<link rel="preconnect" href="https://fonts.googleapis.com">' . "\n";
    echo '<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>' . "\n";
    echo '<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap">' . "\n";

    // intl-tel-input CSS (only if landing needs phone)
    if ($needs_phone) {
        $iti_css_url = THEME_URL . '-child/assets/lib/intl-tel-input/css/intlTelInput.min.css';
        echo '<link rel="stylesheet" href="' . esc_url($iti_css_url) . '?ver=18.5.2">' . "\n";
    }

    // Optional shared library CSS, declared in content.json["libs"].
    $libs = isset($landing_data['libs']) && is_array($landing_data['libs'])
        ? array_map('strval', $landing_data['libs']) : array();

    if (in_array('lenis', $libs, true)) {
        $lenis_css = THEME_DIR . '-child/assets/lib/lenis/lenis.css';
        if (file_exists($lenis_css)) {
            printf(
                '<link rel="stylesheet" href="%s?ver=%s">' . "\n",
                esc_url(THEME_URL . '-child/assets/lib/lenis/lenis.css'),
                esc_attr((string) filemtime($lenis_css))
            );
        }
    }

    if (in_array('slick', $libs, true)) {
        $slick_dir = THEME_DIR . '-child/assets/lib/slick';
        $slick_url = THEME_URL . '-child/assets/lib/slick';
        foreach (array('slick.css', 'slick-theme.css') as $f) {
            $p = $slick_dir . '/' . $f;
            if (file_exists($p)) {
                printf(
                    '<link rel="stylesheet" href="%s?ver=%s">' . "\n",
                    esc_url($slick_url . '/' . $f),
                    esc_attr((string) filemtime($p))
                );
            }
        }
    }

    // Landing's compiled CSS — filemtime cache-bust so browsers cache long-term
    // but pick up changes on rebuild.
    $css_path = LANDINGS_DIST_CSS_DIR . '/' . $slug . '.min.css';
    if (file_exists($css_path)) {
        $css_url = LANDINGS_DIST_CSS_URL . '/' . $slug . '.min.css';
        printf(
            '<link rel="stylesheet" href="%s?ver=%s">' . "\n",
            esc_url($css_url),
            esc_attr((string) filemtime($css_path))
        );
    }
}

/**
 * Minimal pre-</body> output for landing templates.
 *
 * REPLACES wp_footer() entirely. Same reasoning as landing_head().
 *
 * Emits only:
 *   - intl-tel-input JS (if landing needs phone)
 *   - LANDINGS_FORM_BRIDGE inline config (so client form-bridge can find the
 *     REST endpoint + nonce)
 *   - landing's compiled JS bundle
 *   - Flatsome's lazy-load JS (single standalone file — keeps images lazy
 *     without dragging in the rest of Flatsome)
 *
 * WP Rocket continues to work: page cache, CSS/JS minify, defer JS, and
 * critical CSS all run via output buffering on template_redirect — they
 * don't need wp_footer to fire.
 *
 * @return void
 */
function landing_footer()
{
    $slug = landings_current_slug();
    if (! $slug) return;

    $landing_data = landing_content($slug);
    $needs_phone  = ! empty($landing_data['form']['phone_countries']);

    // Inline bridge config — must come BEFORE the landing JS so the bundle
    // can read window.LANDINGS_FORM_BRIDGE on init.
    $bridge_config = array(
        'endpoint' => esc_url_raw(rest_url('landings/v1/hubspot/form-submit')),
        'nonce'    => wp_create_nonce('wp_rest'),
        'slug'     => $slug,
        'pageUri'  => isset($_SERVER['REQUEST_URI'])
            ? esc_url_raw(home_url((string) $_SERVER['REQUEST_URI']))
            : '',
        'pageName' => wp_get_document_title(),
    );
    if ($needs_phone) {
        $iti_utils_path = THEME_DIR . '-child/assets/lib/intl-tel-input/js/utils.js';
        if (file_exists($iti_utils_path)) {
            $bridge_config['intlTelInputUtilsUrl'] =
                THEME_URL . '-child/assets/lib/intl-tel-input/js/utils.js?ver='
                . filemtime($iti_utils_path);
        }
    }
    echo '<script>window.LANDINGS_FORM_BRIDGE = '
        . wp_json_encode($bridge_config) . ';</script>' . "\n";

    // intl-tel-input library — must load BEFORE the landing JS uses it.
    if ($needs_phone) {
        $iti_js_url = THEME_URL . '-child/assets/lib/intl-tel-input/js/intlTelInput.min.js';
        echo '<script src="' . esc_url($iti_js_url) . '?ver=18.5.2"></script>' . "\n";
    }

    // Optional shared libraries declared in content.json["libs"].
    // Emitted as separate <script> tags so the browser caches them across
    // landings and so each lib can be excluded individually from WP Rocket
    // Delay JS if needed. Order matters — jQuery before plugins that depend
    // on it (Slick).
    $libs = isset($landing_data['libs']) && is_array($landing_data['libs'])
        ? array_map('strval', $landing_data['libs']) : array();

    if (in_array('jquery', $libs, true)) {
        // WP ships jQuery at /wp-includes/js/jquery/jquery.min.js. Reuse it so
        // any other plugin that's also using jQuery doesn't load a duplicate.
        echo '<script src="' . esc_url(includes_url('js/jquery/jquery.min.js')) . '"></script>' . "\n";
    }
    if (in_array('lenis', $libs, true)) {
        $lenis_dir = THEME_DIR . '-child/assets/lib/lenis';
        $lenis_url = THEME_URL . '-child/assets/lib/lenis';
        $lenis_js  = $lenis_dir . '/lenis.min.js';
        if (file_exists($lenis_js)) {
            printf(
                '<script src="%s?ver=%s"></script>' . "\n",
                esc_url($lenis_url . '/lenis.min.js'),
                esc_attr((string) filemtime($lenis_js))
            );
        }
    }
    if (in_array('gsap', $libs, true)) {
        $gsap_dir = THEME_DIR . '-child/assets/js/gsap';
        $gsap_url = THEME_URL . '-child/assets/js/gsap';
        // Load order: gsap core → ScrollTrigger. ScrollSmoother not needed (Lenis handles smooth scroll).
        foreach (array('gsap.min.js', 'ScrollTrigger.min.js') as $f) {
            $p = $gsap_dir . '/' . $f;
            if (file_exists($p)) {
                printf(
                    '<script src="%s?ver=%s"></script>' . "\n",
                    esc_url($gsap_url . '/' . $f),
                    esc_attr((string) filemtime($p))
                );
            }
        }
    }
    if (in_array('slick', $libs, true)) {
        $slick_dir = THEME_DIR . '-child/assets/lib/slick';
        $slick_url = THEME_URL . '-child/assets/lib/slick';
        if (file_exists($slick_dir . '/slick.min.js')) {
            printf(
                '<script src="%s?ver=%s"></script>' . "\n",
                esc_url($slick_url . '/slick.min.js'),
                esc_attr((string) filemtime($slick_dir . '/slick.min.js'))
            );
        }
    }

    // Landing JS bundle.
    $js_path = LANDINGS_DIST_JS_DIR . '/' . $slug . '.min.js';
    if (file_exists($js_path)) {
        $js_url = LANDINGS_DIST_JS_URL . '/' . $slug . '.min.js';
        printf(
            '<script src="%s?ver=%s"></script>' . "\n",
            esc_url($js_url),
            esc_attr((string) filemtime($js_path))
        );
    }

    // Lazy-loading is handled via the browser-native loading="lazy"
    // attribute (added automatically by wp_get_attachment_image since WP 5.5).
    // We do NOT include flatsome-lazy-load.js — it depends on the Flatsome
    // global + jQuery + imagesLoaded, all of which we strip from landings.
}

/**
 * Read a landing's content.json.
 * Cached per-request to avoid repeated disk reads inside partials.
 *
 * @param string|null $slug Defaults to current landing.
 * @return array
 */
function landing_content($slug = null)
{
    static $cache = array();

    if ($slug === null) $slug = landings_current_slug();
    if (! $slug) return array();

    if (isset($cache[$slug])) return $cache[$slug];

    $path = LANDINGS_DIR . '/' . $slug . '/content.json';
    if (! file_exists($path)) {
        $cache[$slug] = array();
        return $cache[$slug];
    }

    $raw = file_get_contents($path);
    $data = json_decode($raw, true);
    $cache[$slug] = is_array($data) ? $data : array();

    return $cache[$slug];
}

/**
 * Render an image referenced by attachment ID from content.json.
 * Falls back to nothing if the ID is missing or the attachment is gone.
 *
 * @param int $attachment_id
 * @param string $size WP image size, e.g. 'full', 'large', 'medium'.
 * @param array $attrs Extra attributes for the <img> tag.
 * @return void
 */
function landing_image($attachment_id, $size = 'full', $attrs = array())
{
    $attachment_id = (int) $attachment_id;
    if ($attachment_id <= 0) return;

    echo wp_get_attachment_image($attachment_id, $size, false, $attrs);
}

/**
 * Include a partial from the current landing's partials/ folder.
 *
 * The optional $data array is exposed to the partial as $data. Partials
 * that prefer a domain-specific variable name (e.g. $sub_v2) should copy
 * it on their first line: $sub_v2 = $data ?? array();
 *
 * Why explicit-pass instead of `global $sub_v2`: keeps each partial
 * self-contained, no hidden coupling. Mirrors WP's own get_template_part()
 * which added an $args parameter in 5.5 for this same reason.
 *
 * @param string $name Partial filename without extension.
 * @param array  $data Optional data passed to the partial as $data.
 * @return void
 */
function landing_partial($name, $data = array())
{
    $slug = landings_current_slug();
    if (! $slug) return;

    $path = LANDINGS_DIR . '/' . $slug . '/partials/' . $name . '.php';
    if (! file_exists($path)) return;

    // $data is in scope for the included file.
    include $path;
}
