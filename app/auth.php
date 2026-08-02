<?php
/**
 * Session-backed authentication.
 */

function auth_login(array $user): void
{
    session_regenerate_id(true);
    $_SESSION['user_id'] = (int) $user['id'];
    auth_user(true);
}

function auth_logout(): void
{
    unset($_SESSION['user_id']);
    session_regenerate_id(true);
    auth_user(true);
}

/**
 * The signed-in user row, or null. Cached for the request; pass true to drop
 * the cache after the session identity changes.
 */
function auth_user(bool $reload = false): ?array
{
    static $user = null;
    static $loaded = false;

    if ($reload) {
        $user   = null;
        $loaded = false;
    }
    if ($loaded) {
        return $user;
    }
    $loaded = true;

    if (empty($_SESSION['user_id'])) {
        return null;
    }
    $user = User::find((int) $_SESSION['user_id']);

    if ($user && !(int) $user['is_active']) {   // deactivated mid-session
        auth_logout();
        $user = null;
    }
    return $user;
}

function auth_check(): bool
{
    return auth_user() !== null;
}

function auth_id(): ?int
{
    $user = auth_user();
    return $user ? (int) $user['id'] : null;
}

/** Staff and admins both reach the back office; only admins get settings + user management. */
function is_staff(): bool
{
    $user = auth_user();
    return $user !== null && in_array($user['role'], ['admin', 'staff'], true);
}

function is_admin(): bool
{
    $user = auth_user();
    return $user !== null && $user['role'] === 'admin';
}

function require_login(): void
{
    if (!auth_check()) {
        $_SESSION['_intended'] = current_path();
        flash('warn', 'Please sign in to continue.');
        redirect('/login');
    }
}

function require_staff(): void
{
    if (!auth_check()) {
        $_SESSION['_intended'] = current_path();
        redirect('/admin/login');
    }
    if (!is_staff()) {
        http_response_code(403);
        flash('error', 'That area is restricted to staff accounts.');
        redirect('/');
    }
}

function require_admin(): void
{
    require_staff();
    if (!is_admin()) {
        flash('error', 'Only administrators can do that.');
        redirect('/admin');
    }
}

/** Where to send someone after signing in. */
function intended(string $fallback = '/account'): string
{
    $path = $_SESSION['_intended'] ?? $fallback;
    unset($_SESSION['_intended']);

    // A guest who bounced off /admin earlier should not be sent there after
    // signing up as a customer — the stored path outlives the attempt.
    if (str_starts_with($path, '/admin') && !is_staff()) {
        return $fallback;
    }
    return $path;
}
