<?php
/**
 * Application configuration.
 */

define('ROOT_PATH', dirname(__DIR__));

// --- Identity ---------------------------------------------------------------
define('APP_NAME', 'KACHI Foodstuff Supplies & Logistics');
define('APP_SHORT', 'KACHI');
define('APP_TAGLINE', 'Foodstuff supplies and logistics you can trust, across Delta State.');
define('APP_EMAIL', 'info@kachifoodandlogistics.com');
define('APP_PHONE', '0906 088 4920');
define('APP_PHONE_ALT', '0806 142 8556');
define('APP_ADDRESS', 'Odakpo Close, Doctor Street, off Specialist Hospital, Asaba');
defined('APP_DOMAIN') || define('APP_DOMAIN', 'https://kachifoodandlogistics.com');
define('APP_STATE', 'Delta State');
define('APP_CITY', 'Asaba');

// --- Environment --------------------------------------------------------
/**
 * Server-specific settings (database credentials, environment, domain) live in
 * config/config.local.php, which is git-ignored and never leaves the machine
 * it belongs to. Anything it defines wins; the defaults below are the local
 * XAMPP values and are safe to publish.
 *
 * See config/config.local.example.php for the template.
 */
if (is_file(__DIR__ . '/config.local.php')) {
    require __DIR__ . '/config.local.php';
}

$serverName = strtolower((string) ($_SERVER['SERVER_NAME'] ?? ''));
$localHost  = in_array($serverName, ['', 'localhost', '127.0.0.1', '::1'], true);
defined('APP_ENV')   || define('APP_ENV', $localHost ? 'local' : 'production');
defined('APP_DEBUG') || define('APP_DEBUG', APP_ENV === 'local');

// --- Database -----------------------------------------------------------
defined('DB_HOST')    || define('DB_HOST', '127.0.0.1');
defined('DB_PORT')    || define('DB_PORT', '3307');  // this XAMPP stack runs MySQL on 3307, not 3306
defined('DB_NAME')    || define('DB_NAME', 'kachi_food_logistics');
defined('DB_USER')    || define('DB_USER', 'root');
defined('DB_PASS')    || define('DB_PASS', '');
defined('DB_CHARSET') || define('DB_CHARSET', 'utf8mb4');

// --- Commerce ---------------------------------------------------------------
define('CURRENCY', '&#8358;');         // Naira
define('DELIVERY_FEE', 3500.00);       // Flat Lagos-metro fee
define('FREE_DELIVERY_FROM', 150000.00);
define('PER_PAGE', 12);

// --- Paths / URLs -----------------------------------------------------------
// Folder the app is served from, e.g. "/kachifoodandlogistics" under XAMPP.
$scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/index.php'));
define('BASE_PATH', $scriptDir === '/' ? '' : rtrim($scriptDir, '/'));

define('UPLOAD_PATH', ROOT_PATH . '/assets/uploads');
define('UPLOAD_URL', BASE_PATH . '/assets/uploads');

// --- Errors -----------------------------------------------------------------
if (APP_DEBUG) {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE);
    ini_set('display_errors', '0');
}

date_default_timezone_set('Africa/Lagos');
