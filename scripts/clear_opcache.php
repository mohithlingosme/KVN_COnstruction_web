<?php

declare(strict_types=1);

/**
 * KVN Construction - Clear OPcache
 *
 * Resets PHP's OPcache so that newly deployed PHP files are picked up
 * without restarting the web server. Safe to run repeatedly and no-op
 * when OPcache is not enabled (e.g. CLI/FPM without opcache).
 *
 * Usage:
 *   php scripts/clear_opcache.php
 *
 * NOTE: If OPcache is enabled with `opcache.validate_timestamps=0`, a CLI
 * invocation may not clear the FPM cache. In that case the web server must
 * reload its pool (e.g. `systemctl reload php*-fpm` / restart Apache).
 * This script performs the best-effort reset and reports what it did.
 */

if (!function_exists('opcache_reset')) {
    echo "OPcache: extension not loaded. Nothing to clear (OK).\n";
    exit(0);
}

if (!ini_get('opcache.enable') && PHP_SAPI !== 'cli') {
    echo "OPcache: disabled. Nothing to clear (OK).\n";
    exit(0);
}

$before = function_exists('opcache_get_status') ? opcache_get_status(false) : null;
$reset = opcache_reset();

if ($reset) {
    echo "OPcache: cache reset successfully.\n";
} else {
    echo "OPcache: reset not required or not available (OK).\n";
}

if ($before && isset($before['opcache_enabled'])) {
    echo "OPcache: enabled=" . ($before['opcache_enabled'] ? 'true' : 'false') .
        ", validate_timestamps=" . (ini_get('opcache.validate_timestamps') ? 'true' : 'false') . "\n";
    if (!$before['opcache_enabled'] || !ini_get('opcache.validate_timestamps')) {
        echo "NOTE: OPcache is active with timestamp validation disabled. PHP files changed on disk\n";
        echo "      will not be refreshed by this script. Reload the PHP-FPM pool or web server to force a repopulation.\n";
    }
}

exit(0);
