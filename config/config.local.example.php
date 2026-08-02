<?php
/**
 * Template for config/config.local.php — the git-ignored, per-server overrides.
 *
 * Copy it on each machine:
 *     cp config/config.local.example.php config/config.local.php
 *
 * Anything defined here wins over the defaults in config.php, because that file
 * guards every constant with defined(). Define only what differs; leave the rest.
 *
 * NEVER commit config.local.php. Real credentials belong on the server only.
 */

// --- Production example ------------------------------------------------------
// define('APP_ENV', 'production');     // switches APP_DEBUG off, hides stack traces
// define('APP_DOMAIN', 'https://kachifoodandlogistics.com');

// define('DB_HOST', 'localhost');
// define('DB_PORT', '3306');           // cPanel MySQL is normally 3306
// define('DB_NAME', 'kachifoo_live');  // cPanel prefixes databases with the account name
// define('DB_USER', 'kachifoo_app');
// define('DB_PASS', 'the-password-from-cpanel');

// --- Local XAMPP example -----------------------------------------------------
// define('DB_PORT', '3306');           // if your XAMPP runs MySQL on the default port
