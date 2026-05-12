<?php
/**
 * Landings — integrations loader.
 *
 * Auto-discovers integration folders under landings/_shared/integrations/{vendor}/
 * and includes each vendor's bootstrap files. Each vendor folder is fully
 * self-contained — drop a folder in here and it loads on its own.
 *
 * Convention per vendor folder:
 *   _shared/integrations/{vendor}/
 *     class-*-client.php   loaded BEFORE other class files (dependency)
 *     class-*-api.php      loaded last (depends on client)
 *     class-*.php          all other class files load alphabetically between
 *     README.md            optional, config/usage notes
 */

if (! defined('ABSPATH')) exit;

if (! defined('LANDINGS_INTEGRATIONS_DIR')) {
    define('LANDINGS_INTEGRATIONS_DIR', __DIR__);
}

(function () {
    $vendors = glob(LANDINGS_INTEGRATIONS_DIR . '/*', GLOB_ONLYDIR);
    if (! is_array($vendors)) return;

    foreach ($vendors as $vendor_dir) {
        // Skip folders prefixed with _ (e.g. _utils).
        if (strpos(basename($vendor_dir), '_') === 0) continue;

        $files = glob($vendor_dir . '/class-*.php');
        if (! is_array($files)) continue;

        // Order: clients (low-level) → other classes → APIs (consumers).
        // This guarantees an API file's instantiation can reference its
        // client without "class not found" errors.
        usort($files, function ($a, $b) {
            $rank = function ($path) {
                $name = basename($path, '.php');
                if (substr($name, -7) === '-client') return 0;
                if (substr($name, -4) === '-api')    return 2;
                return 1;
            };
            $ra = $rank($a);
            $rb = $rank($b);
            if ($ra !== $rb) return $ra - $rb;
            return strcmp($a, $b);
        });

        foreach ($files as $class_file) {
            require_once $class_file;
        }
    }
})();

// Each vendor's class file should self-instantiate at the bottom of its file
// (e.g. `new Landings_HubSpot_Form_API();`) so it hooks into rest_api_init.
