<?php
/** @var string|null $title  @var string|null $description  @var string|null $schema  @var string|null $ogImage */
$title       = $title ?? APP_NAME;
$description = $description ?? APP_TAGLINE;
$cartCount   = cart_count();
$canonical   = rtrim(APP_DOMAIN, '/') . current_path();
$phone       = Setting::get('contact_phone', APP_PHONE);
$phoneHref   = preg_replace('/[^0-9+]/', '', $phone);
$whatsapp    = Setting::get('whatsapp');

$nav = [
    ['/',          'Home',           '/'],
    ['/products',  'Foodstuff',      '/products'],
    ['/logistics', 'Book logistics', '/logistics'],
    ['/services',  'Services',       '/services'],
    ['/category/bulk-food-packages', 'Bulk packages', '/category/bulk-food-packages'],
    ['/about',     'About',          '/about'],
    ['/contact',   'Contact',        '/contact'],
];
?>
<!doctype html>
<html lang="en" class="scroll-pt-28">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($title) ?></title>
    <meta name="description" content="<?= e($description) ?>">
    <link rel="canonical" href="<?= e($canonical) ?>">

    <meta property="og:type" content="website">
    <meta property="og:site_name" content="<?= e(Setting::get('site_name', APP_NAME)) ?>">
    <meta property="og:title" content="<?= e($title) ?>">
    <meta property="og:description" content="<?= e($description) ?>">
    <meta property="og:url" content="<?= e($canonical) ?>">
    <meta property="og:image" content="<?= e(rtrim(APP_DOMAIN, '/') . asset('img/' . ($ogImage ?? 'og-image.jpg'))) ?>">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="theme-color" content="#082C5C">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Montserrat:wght@700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= asset('css/tailwind.css') ?>">

    <link rel="icon" type="image/png" href="<?= asset('img/favicon.png') ?>">
    <link rel="apple-touch-icon" href="<?= asset('img/apple-touch-icon.png') ?>">

    <?php partial('schema_org'); ?>
    <?php if (!empty($schema)): ?>
        <script type="application/ld+json"><?= $schema ?></script>
    <?php endif; ?>
</head>
<body class="min-h-dvh flex flex-col">

<a class="skip-link" href="#main">Skip to main content</a>

<!-- Utility bar -->
<div class="bg-navy-900 text-navy-100">
    <div class="shell flex min-h-10 flex-wrap items-center justify-between gap-x-6 gap-y-1 py-1.5 text-xs">
        <p class="flex items-center gap-2">
            <?= icon('map-pin', 'size-3.5 text-orange-400') ?>
            <span class="hidden sm:inline">Wholesale &amp; retail foodstuff and logistics across <?= e(APP_STATE) ?></span>
            <span class="sm:hidden">Serving <?= e(APP_STATE) ?></span>
        </p>
        <div class="flex items-center gap-4">
            <a class="flex min-h-11 items-center gap-1.5 font-semibold transition-colors hover:text-white" href="tel:<?= e($phoneHref) ?>">
                <?= icon('phone-call', 'size-3.5') ?><?= e($phone) ?>
            </a>
            <a class="hidden min-h-11 items-center gap-1.5 transition-colors hover:text-white sm:flex" href="<?= url('/track') ?>">
                <?= icon('route', 'size-3.5') ?>Track order
            </a>
            <?php if (is_staff()): ?>
                <a class="hidden min-h-11 items-center gap-1.5 transition-colors hover:text-white lg:flex" href="<?= url('/admin') ?>">
                    <?= icon('shield', 'size-3.5') ?>Back office
                </a>
            <?php endif; ?>
        </div>
    </div>
</div>

<div data-nav-backdrop hidden class="fixed inset-0 z-30 bg-navy-950/45 backdrop-blur-[2px] lg:hidden"></div>

<!-- Masthead -->
<header class="sticky top-0 z-40 border-b border-ink-200 bg-white/90 backdrop-blur-lg">
    <div class="shell flex min-h-[4.5rem] items-center gap-4">
        <a href="<?= url('/') ?>" class="min-w-0 shrink" aria-label="<?= e(APP_NAME) ?> — home">
            <img src="<?= asset('img/logo.png') ?>" alt="<?= e(APP_NAME) ?>"
                 class="h-8 w-auto max-w-full sm:h-11" width="1136" height="392" fetchpriority="high">
        </a>

        <nav class="ml-auto hidden items-center gap-0.5 lg:flex" aria-label="Primary">
            <?php foreach ($nav as [$href, $label, $match]): ?>
                <a href="<?= url($href) ?>"
                   class="nav-link <?= nav_active($match) ? 'nav-link-active' : '' ?>"
                   <?= nav_active($match) ? 'aria-current="page"' : '' ?>><?= $label ?></a>
            <?php endforeach; ?>
        </nav>

        <div class="ml-auto flex shrink-0 items-center gap-1.5 sm:gap-2 lg:ml-0">
            <a href="<?= url('/cart') ?>"
               class="btn btn-ghost relative gap-2 px-3"
               aria-label="Cart<?= $cartCount ? ', ' . $cartCount . ' items' : ', empty' ?>">
                <?= icon('cart', 'size-5') ?>
                <span class="hidden sm:inline">Cart</span>
                <?php if ($cartCount): ?>
                    <span class="absolute -right-1.5 -top-1.5 grid min-w-5 place-items-center rounded-full bg-orange-500 px-1.5 py-0.5 text-xs font-bold leading-none text-white"><?= $cartCount ?></span>
                <?php endif; ?>
            </a>

            <?php if (auth_check()): ?>
                <a class="btn btn-outline gap-2 px-3" href="<?= url('/account') ?>">
                    <?= icon('user', 'size-5') ?>
                    <span class="hidden sm:inline"><?= e(explode(' ', auth_user()['name'])[0]) ?></span>
                </a>
            <?php else: ?>
                <a class="btn btn-primary hidden sm:inline-flex" href="<?= url('/login') ?>">Sign in</a>
            <?php endif; ?>

            <button type="button" data-nav-toggle aria-expanded="false" aria-controls="mobile-nav"
                    class="btn btn-ghost gap-2 px-3 lg:hidden" aria-label="Open menu">
                <span data-nav-icon="open"><?= icon('menu', 'size-5') ?></span>
                <span data-nav-icon="close" class="hidden"><?= icon('close', 'size-5') ?></span>
                <span data-nav-label class="text-sm font-bold">Menu</span>
            </button>
        </div>
    </div>

    <!-- Mobile navigation -->
    <nav id="mobile-nav" data-nav hidden
         class="absolute inset-x-0 top-full max-h-[calc(100dvh-4.5rem)] overflow-y-auto border-t border-ink-200 bg-white shadow-deep lg:hidden"
         aria-label="Mobile">
        <div class="shell grid gap-1 py-4 sm:py-5">
            <?php foreach ($nav as [$href, $label, $match]): ?>
                <a href="<?= url($href) ?>"
                   class="flex min-h-11 items-center justify-between rounded-xl px-3 text-base font-semibold
                          <?= nav_active($match) ? 'bg-navy-50 text-navy-700' : 'text-ink-700 hover:bg-ink-100' ?>"
                   <?= nav_active($match) ? 'aria-current="page"' : '' ?>>
                    <?= $label ?><?= icon('chevron-right', 'size-4 text-ink-300') ?>
                </a>
            <?php endforeach; ?>

            <div class="mt-3 grid gap-2 border-t border-ink-200 pt-4">
                <a class="btn btn-primary btn-block" href="<?= url(auth_check() ? '/account' : '/login') ?>">
                    <?= auth_check() ? 'My account' : 'Sign in' ?>
                </a>
                <a class="btn btn-ghost btn-block" href="<?= url('/track') ?>">Track an order</a>
            </div>
        </div>
    </nav>
</header>

<?php $flashes = take_flashes(); ?>
<?php if ($flashes): ?>
    <div class="fixed right-4 top-24 z-50 w-[min(24rem,calc(100vw-2rem))] space-y-2 no-print" role="status" aria-live="polite">
        <?php foreach ($flashes as $flash): ?>
            <?php
            $tone = ['success' => 'success', 'error' => 'error', 'warn' => 'warn'][$flash['type']] ?? 'info';
            $mark = ['success' => 'check-circle', 'error' => 'alert', 'warn' => 'alert'][$flash['type']] ?? 'info';
            ?>
            <div data-flash class="alert alert-<?= $tone ?> mb-0 animate-slide-in shadow-lift">
                <?= icon($mark, 'size-5 shrink-0 mt-0.5') ?>
                <span class="flex-1"><?= e($flash['message']) ?></span>
                <button type="button" data-flash-close class="shrink-0 cursor-pointer opacity-60 hover:opacity-100" aria-label="Dismiss">
                    <?= icon('close', 'size-4') ?>
                </button>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<main id="main" class="flex-1">
