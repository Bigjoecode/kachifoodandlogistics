<?php
/**
 * Front controller. Every request that is not a real file lands here
 * (see .htaccess) and is matched against the route table below.
 */

require __DIR__ . '/config/config.php';
require __DIR__ . '/config/database.php';

spl_autoload_register(static function (string $class): void {
    $file = ROOT_PATH . '/app/models/' . $class . '.php';
    if (is_file($file)) {
        require $file;
    }
});

// FTP-only hosting cannot run post-deploy shell commands. Migrations are
// idempotent and are applied before the first request reaches a page handler.
require ROOT_PATH . '/database/migrate.php';
apply_database_migrations();

session_set_cookie_params([
    'httponly' => true,
    'samesite' => 'Lax',
    'path'     => BASE_PATH === '' ? '/' : BASE_PATH,
]);
session_name('kachi_session');
session_start();

require ROOT_PATH . '/app/helpers.php';
require ROOT_PATH . '/app/icons.php';
require ROOT_PATH . '/app/auth.php';
require ROOT_PATH . '/app/cart.php';

/**
 * method | pattern | page file (relative to /pages, no .php) | guard
 * Guards: null | 'auth' | 'staff' | 'admin'
 */
$routes = [
    // --- Storefront ---------------------------------------------------------
    ['GET',      '/',                        'home'],
    ['GET',      '/products',                'products'],
    ['GET',      '/products/{slug}',         'product'],
    ['GET',      '/category/{slug}',         'products'],
    ['GET|POST', '/quote',                   'quote'],
    ['GET',      '/services',                'services'],
    ['GET',      '/about',                   'about'],
    ['GET',      '/faqs',                    'faqs'],
    ['GET|POST', '/contact',                 'contact'],
    ['GET',      '/sitemap.xml',             'sitemap'],

    // --- Logistics booking (independent of the food catalogue) --------------
    ['GET|POST', '/logistics',               'logistics'],
    ['GET',      '/logistics/{reference}',   'booking_confirmation'],

    // --- Cart & checkout ----------------------------------------------------
    ['GET',      '/cart',                    'cart'],
    ['POST',     '/cart/add',                'cart_action'],
    ['POST',     '/cart/update',             'cart_action'],
    ['POST',     '/cart/remove',             'cart_action'],
    ['POST',     '/cart/clear',              'cart_action'],
    ['GET|POST', '/checkout',                'checkout'],
    ['GET',      '/order/{reference}',       'order_confirmation'],

    // --- Tracking -----------------------------------------------------------
    ['GET|POST', '/track',                   'track'],

    // --- Accounts -----------------------------------------------------------
    // First-run administrator creation. 404s once any staff account exists.
    ['GET|POST', '/setup',                   'setup'],
    ['GET|POST', '/login',                   'auth/login'],
    ['GET|POST', '/register',                'auth/register'],
    ['POST',     '/logout',                  'auth/logout'],
    ['GET|POST', '/account',                 'account/profile',        'auth'],
    ['GET|POST', '/account/password',        'account/password',       'auth'],
    ['GET',      '/account/orders',          'account/orders',         'auth'],
    ['GET',      '/account/orders/{reference}', 'account/order',       'auth'],
    ['GET',      '/account/bookings',        'account/bookings',       'auth'],

    // --- Back office --------------------------------------------------------
    ['GET|POST', '/admin/login',             'admin/login'],
    ['GET',      '/admin',                   'admin/dashboard',        'staff'],
    ['GET',      '/admin/orders',            'admin/orders',           'staff'],
    ['GET|POST', '/admin/orders/{id}',       'admin/order_view',       'staff'],
    ['GET',      '/admin/logistics',         'admin/logistics',        'staff'],
    ['GET|POST', '/admin/logistics/{id}',    'admin/booking_view',     'staff'],
    ['GET',      '/admin/products',          'admin/products',         'staff'],
    ['GET|POST', '/admin/products/new',      'admin/product_form',     'staff'],
    ['GET|POST', '/admin/products/{id}/edit','admin/product_form',     'staff'],
    ['POST',     '/admin/products/{id}/delete', 'admin/product_delete','staff'],
    ['GET|POST', '/admin/categories',        'admin/categories',       'staff'],
    ['POST',     '/admin/categories/{id}/delete', 'admin/category_delete', 'staff'],
    ['GET',      '/admin/customers',         'admin/customers',        'staff'],
    ['POST',     '/admin/customers/{id}',    'admin/customer_update',  'admin'],
    ['GET',      '/admin/messages',          'admin/messages',         'staff'],
    ['POST',     '/admin/messages/{id}',     'admin/message_update',   'staff'],
    ['GET|POST', '/admin/settings',          'admin/settings',         'admin'],
];

$method = $_SERVER['REQUEST_METHOD'] === 'HEAD' ? 'GET' : $_SERVER['REQUEST_METHOD'];
$path   = current_path();

// Reject cross-site form posts before any handler runs.
if ($method === 'POST' && !csrf_valid()) {
    http_response_code(419);
    flash('error', 'Your session expired. Please try that again.');
    back();
}

$matched   = null;
$guard     = null;
$params    = [];
$pathFound = false;

foreach ($routes as $route) {
    [$methods, $pattern, $page] = $route;

    $regex = '#^' . preg_replace('#\{([a-z_]+)\}#', '(?P<$1>[^/]+)', $pattern) . '$#i';
    if (!preg_match($regex, $path, $matches)) {
        continue;
    }
    $pathFound = true;

    if (!in_array($method, explode('|', $methods), true)) {
        continue;
    }

    $matched = $page;
    $guard   = $route[3] ?? null;
    $params  = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
    break;
}

if ($matched === null) {
    http_response_code($pathFound ? 405 : 404);
    include ROOT_PATH . '/pages/errors/404.php';
    exit;
}

switch ($guard) {
    case 'auth':  require_login(); break;
    case 'staff': require_staff(); break;
    case 'admin': require_admin(); break;
}

$file = ROOT_PATH . '/pages/' . $matched . '.php';
if (!is_file($file)) {
    http_response_code(500);
    exit('Missing page handler: ' . e($matched));
}

// Category pages reuse the products handler with the slug pre-applied.
if ($matched === 'products' && isset($params['slug'])) {
    $_GET['category'] = $params['slug'];
}

include $file;
clear_old();
