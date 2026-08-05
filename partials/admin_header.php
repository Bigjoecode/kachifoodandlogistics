<?php
/** @var string $title @var string|null $subtitle @var string|null $actions */
$title    = $title ?? 'Dashboard';
$subtitle = $subtitle ?? null;
$actions  = $actions ?? '';
$unread   = Message::unreadCount();
$openQuotes   = (int) Db::value("SELECT COUNT(*) FROM orders WHERE type = 'quote' AND status IN ('pending','quoted')");
$openBookings = (int) Db::value("SELECT COUNT(*) FROM logistics_bookings WHERE status = 'pending'");
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($title) ?> | <?= e(APP_NAME) ?> admin</title>
    <meta name="robots" content="noindex">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= asset('css/app.css') ?>">
    <link rel="icon" type="image/png" href="<?= asset('img/favicon.png') ?>">
</head>
<body>
<div class="admin">

    <!-- Small screens only: the drawer trigger, always within reach. -->
    <div class="admin-bar">
        <button type="button" class="admin-burger" data-drawer-toggle
                aria-expanded="false" aria-controls="admin-drawer" aria-label="Open navigation menu">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"
                 stroke-linecap="round" aria-hidden="true"><path d="M4 7h16M4 12h16M4 17h16"/></svg>
        </button>
        <span class="brand-mark">KF</span>
        <span class="title"><?= e($title) ?></span>
        <span class="spacer"></span>
        <?php if ($unread + $openQuotes + $openBookings > 0): ?>
            <span class="pill" title="Items needing attention"><?= $unread + $openQuotes + $openBookings ?></span>
        <?php endif; ?>
    </div>

    <div class="admin-scrim" data-drawer-scrim hidden></div>

    <aside class="admin-side" id="admin-drawer" data-drawer>
        <div class="flex-between">
            <a class="brand" href="<?= url('/admin') ?>">
                <span class="brand-mark">KF</span>
                <span class="brand-text">
                    <span class="brand-name">Kachi</span>
                    <span class="brand-sub">Back office</span>
                </span>
            </a>
            <button type="button" class="admin-burger admin-drawer-close" data-drawer-close
                    aria-label="Close navigation menu">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"
                     stroke-linecap="round" aria-hidden="true"><path d="M18 6 6 18M6 6l12 12"/></svg>
            </button>
        </div>

        <nav class="admin-nav">
            <span class="group-label">Overview</span>
            <a href="<?= url('/admin') ?>" class="<?= current_path() === '/admin' ? 'is-active' : '' ?>">Dashboard</a>

            <span class="group-label">Sales</span>
            <a href="<?= url('/admin/orders') ?>" class="<?= trim(nav_active('/admin/orders')) ?>">Orders</a>
            <a href="<?= url('/admin/orders') ?>?type=quote" class="">
                Quotes <?php if ($openQuotes): ?><span class="pill"><?= $openQuotes ?></span><?php endif; ?>
            </a>
            <a href="<?= url('/admin/logistics') ?>" class="<?= trim(nav_active('/admin/logistics')) ?>">
                Logistics <?php if ($openBookings): ?><span class="pill"><?= $openBookings ?></span><?php endif; ?>
            </a>

            <span class="group-label">Catalogue</span>
            <a href="<?= url('/admin/products') ?>" class="<?= trim(nav_active('/admin/products')) ?>">Products</a>
            <a href="<?= url('/admin/categories') ?>" class="<?= trim(nav_active('/admin/categories')) ?>">Categories</a>

            <span class="group-label">People</span>
            <a href="<?= url('/admin/customers') ?>" class="<?= trim(nav_active('/admin/customers')) ?>">Customers</a>
            <a href="<?= url('/admin/messages') ?>" class="<?= trim(nav_active('/admin/messages')) ?>">
                Messages <?php if ($unread): ?><span class="pill"><?= $unread ?></span><?php endif; ?>
            </a>

            <?php if (is_admin()): ?>
                <span class="group-label">System</span>
                <a href="<?= url('/admin/settings') ?>" class="<?= trim(nav_active('/admin/settings')) ?>">Settings</a>
            <?php endif; ?>
        </nav>

        <div class="admin-side-foot">
            <div class="strong" style="color:#fff"><?= e(auth_user()['name']) ?></div>
            <div style="color:var(--brand-200)" class="tiny"><?= e(ucfirst(auth_user()['role'])) ?></div>
            <div class="flex gap-sm mt-1">
                <a href="<?= url('/') ?>" class="tiny">View site</a>
                <form method="post" action="<?= url('/logout') ?>" style="display:inline">
                    <?= csrf_field() ?>
                    <button class="btn-link tiny" style="color:var(--brand-100)" type="submit">Sign out</button>
                </form>
            </div>
        </div>
    </aside>

    <div class="admin-main">
        <header class="admin-top">
            <div>
                <h1><?= e($title) ?></h1>
                <?php if ($subtitle): ?><div class="sub"><?= e($subtitle) ?></div><?php endif; ?>
            </div>
            <?php if ($actions): ?><div class="admin-actions"><?= $actions ?></div><?php endif; ?>
        </header>

        <?php $flashes = take_flashes(); ?>
        <?php if ($flashes): ?>
            <div class="flash-stack">
                <?php foreach ($flashes as $flash): ?>
                    <div class="alert alert-<?= e($flash['type']) ?>" data-flash><?= e($flash['message']) ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <div class="admin-content">
