<?php
/**
 * View + request helpers used everywhere.
 */

/** Escape for HTML output. */
function e($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/** Build an application URL: url('/products') => /kachifoodandlogistics/products */
function url(string $path = '/'): string
{
    return BASE_PATH . '/' . ltrim($path, '/');
}

function asset(string $path): string
{
    return BASE_PATH . '/assets/' . ltrim($path, '/');
}

function redirect(string $path): void
{
    header('Location: ' . (str_starts_with($path, 'http') ? $path : url($path)));
    exit;
}

function back(): void
{
    redirect($_SERVER['HTTP_REFERER'] ?? '/');
}

/** Current request path without the base folder, always starting with "/". */
function current_path(): string
{
    $uri  = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
    $path = substr($uri, strlen(BASE_PATH));
    return '/' . trim($path === false ? '' : $path, '/');
}

function is_post(): bool
{
    return ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST';
}

/** Read a request value, trimmed. */
function input(string $key, $default = null)
{
    $value = $_POST[$key] ?? $_GET[$key] ?? $default;
    return is_string($value) ? trim($value) : $value;
}

function input_int(string $key, int $default = 0): int
{
    $value = input($key);
    return is_numeric($value) ? (int) $value : $default;
}

function input_float(string $key, float $default = 0.0): float
{
    $value = input($key);
    return is_numeric($value) ? (float) $value : $default;
}

// --- Old input (repopulate forms after a validation failure) -----------------

function flash_old(array $data): void
{
    $_SESSION['_old'] = $data;
}

function old(string $key, $default = '')
{
    return $_SESSION['_old'][$key] ?? $default;
}

function clear_old(): void
{
    unset($_SESSION['_old']);
}

// --- Flash messages ---------------------------------------------------------

function flash(string $type, string $message): void
{
    $_SESSION['_flash'][] = ['type' => $type, 'message' => $message];
}

function take_flashes(): array
{
    $messages = $_SESSION['_flash'] ?? [];
    unset($_SESSION['_flash']);
    return $messages;
}

// --- CSRF -------------------------------------------------------------------

function csrf_token(): string
{
    if (empty($_SESSION['_csrf'])) {
        $_SESSION['_csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['_csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="_token" value="' . e(csrf_token()) . '">';
}

function csrf_valid(): bool
{
    $token = $_POST['_token'] ?? '';
    return is_string($token) && $token !== '' && hash_equals(csrf_token(), $token);
}

// --- Formatting -------------------------------------------------------------

function money($amount, bool $withSymbol = true): string
{
    $formatted = number_format((float) $amount, 2);
    return $withSymbol ? CURRENCY . $formatted : $formatted;
}

/** Compact money for tiles: N1.2m / N845k */
function money_short($amount): string
{
    $amount = (float) $amount;
    if ($amount >= 1000000) {
        return CURRENCY . rtrim(rtrim(number_format($amount / 1000000, 1), '0'), '.') . 'm';
    }
    if ($amount >= 1000) {
        return CURRENCY . rtrim(rtrim(number_format($amount / 1000, 1), '0'), '.') . 'k';
    }
    return CURRENCY . number_format($amount, 0);
}

function slugify(string $text): string
{
    $text = strtolower(trim($text));
    $text = preg_replace('/[^a-z0-9]+/', '-', $text) ?? '';
    return trim($text, '-') ?: 'item';
}

function excerpt(?string $text, int $limit = 120): string
{
    $text = trim((string) $text);
    if (mb_strlen($text) <= $limit) {
        return $text;
    }
    return rtrim(mb_substr($text, 0, $limit)) . '...';
}

function date_human(?string $datetime, bool $withTime = false): string
{
    if (!$datetime) {
        return '--';
    }
    $ts = strtotime($datetime);
    return $ts ? date($withTime ? 'd M Y, g:ia' : 'd M Y', $ts) : '--';
}

function time_ago(?string $datetime): string
{
    if (!$datetime || !($ts = strtotime($datetime))) {
        return '--';
    }
    $diff = time() - $ts;
    if ($diff < 60)    return 'just now';
    if ($diff < 3600)  return floor($diff / 60) . 'm ago';
    if ($diff < 86400) return floor($diff / 3600) . 'h ago';
    if ($diff < 604800) return floor($diff / 86400) . 'd ago';
    return date('d M Y', $ts);
}

/**
 * URL for a product photo.
 *
 * Two sources share the same column. Photos shipped with the repository are
 * stored as a path under assets/ (e.g. "img/photos/prod-rice-white.jpg") and
 * are deployed with the code. Anything uploaded through the back office is a
 * bare filename living in assets/uploads, which is server-owned and excluded
 * from deploys.
 */
function product_image_url(?string $image): ?string
{
    $image = trim((string) $image);
    if ($image === '') {
        return null;
    }
    return str_starts_with($image, 'img/') ? asset($image) : UPLOAD_URL . '/' . $image;
}

/** Matching WebP for a bundled photo, or null when there is not one. */
function product_image_webp(?string $image): ?string
{
    $image = trim((string) $image);
    if ($image === '' || !str_starts_with($image, 'img/') || !str_ends_with($image, '.jpg')) {
        return null;
    }
    $webp = substr($image, 0, -4) . '.webp';
    return is_file(ROOT_PATH . '/assets/' . $webp) ? asset($webp) : null;
}

/** Initials used for the generated product/category tiles. */
function initials(string $name, int $max = 2): string
{
    $words = preg_split('/\s+/', trim($name)) ?: [];
    $out   = '';
    foreach ($words as $word) {
        if ($word === '' || !preg_match('/[A-Za-z0-9]/', $word[0])) {
            continue;
        }
        $out .= strtoupper($word[0]);
        if (strlen($out) >= $max) {
            break;
        }
    }
    return $out ?: '#';
}

// --- Order status vocabulary ------------------------------------------------

function order_statuses(): array
{
    return [
        'pending'    => 'Pending',
        'quoted'     => 'Quoted',
        'confirmed'  => 'Confirmed',
        'processing' => 'Processing',
        'dispatched' => 'Dispatched',
        'in_transit' => 'In transit',
        'delivered'  => 'Delivered',
        'cancelled'  => 'Cancelled',
    ];
}

function status_label(string $status): string
{
    return order_statuses()[$status] ?? ucfirst(str_replace('_', ' ', $status));
}

/** Maps an order or booking status onto a badge modifier class. */
function status_tone(string $status): string
{
    return [
        'pending'    => 'warn',
        'quoted'     => 'info',
        'confirmed'  => 'info',
        'processing' => 'info',
        'assigned'   => 'info',
        'dispatched' => 'brand',
        'in_transit' => 'brand',
        'delivered'  => 'success',
        'completed'  => 'success',
        'cancelled'  => 'danger',
    ][$status] ?? 'muted';
}

/** Ordered milestones shown on the public tracking timeline. */
function tracking_milestones(): array
{
    return ['confirmed', 'processing', 'dispatched', 'in_transit', 'delivered'];
}

/** Towns we run scheduled routes to, used across the site and in forms. */
function service_areas(): array
{
    $configured = Setting::get('service_areas', 'Asaba, Warri, Sapele, Ughelli, Abraka, Agbor, Oghara, Effurun');
    return array_values(array_filter(array_map('trim', explode(',', (string) $configured))));
}

function nigerian_states(): array
{
    return [
        'Abia', 'Adamawa', 'Akwa Ibom', 'Anambra', 'Bauchi', 'Bayelsa', 'Benue', 'Borno',
        'Cross River', 'Delta', 'Ebonyi', 'Edo', 'Ekiti', 'Enugu', 'FCT - Abuja', 'Gombe',
        'Imo', 'Jigawa', 'Kaduna', 'Kano', 'Katsina', 'Kebbi', 'Kogi', 'Kwara', 'Lagos',
        'Nasarawa', 'Niger', 'Ogun', 'Ondo', 'Osun', 'Oyo', 'Plateau', 'Rivers', 'Sokoto',
        'Taraba', 'Yobe', 'Zamfara',
    ];
}

function delivery_windows(): array
{
    return ['8:00am - 12:00pm', '12:00pm - 4:00pm', '4:00pm - 7:00pm', 'Any time'];
}

function logistics_services(): array
{
    return ['Standard delivery', 'Refrigerated delivery', 'Same-day express', 'Bulk haulage', 'Warehouse pickup'];
}

// --- Rendering --------------------------------------------------------------

/** Include a partial with variables scoped to it. */
function partial(string $name, array $data = []): void
{
    extract($data, EXTR_SKIP);
    include ROOT_PATH . '/partials/' . $name . '.php';
}

/** Marks the active nav item. */
function nav_active(string $prefix): string
{
    $path = current_path();
    $hit  = $prefix === '/' ? $path === '/' : str_starts_with($path, $prefix);
    return $hit ? ' is-active' : '';
}

/** Rebuild the current query string with some keys replaced. */
function query_with(array $params): string
{
    $query = array_merge($_GET, $params);
    $query = array_filter($query, fn($v) => $v !== '' && $v !== null);
    return $query ? '?' . http_build_query($query) : '';
}

function page_title(string $title): string
{
    return $title . ' | ' . APP_NAME;
}

function abort_404(): void
{
    http_response_code(404);
    include ROOT_PATH . '/pages/errors/404.php';
    exit;
}
