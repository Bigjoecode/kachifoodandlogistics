<?php $path = current_path(); ?>
<nav class="mb-8 flex gap-1 overflow-x-auto border-b border-ink-200" aria-label="Account">
    <?php
    $tabs = [
        ['/account/orders',   'My orders',   'receipt',  str_starts_with($path, '/account/orders')],
        ['/account/bookings', 'My bookings', 'truck',    $path === '/account/bookings'],
        ['/account',          'Profile',     'user',     $path === '/account'],
        ['/account/password', 'Password',    'shield',   $path === '/account/password'],
    ];
    if (is_staff()) {
        $tabs[] = ['/admin', 'Back office', 'building', false];
    }
    ?>
    <?php foreach ($tabs as [$href, $label, $ico, $active]): ?>
        <a href="<?= url($href) ?>"
           class="-mb-px flex min-h-11 shrink-0 items-center gap-2 border-b-2 px-4 text-sm font-semibold transition-colors
                  <?= $active
                        ? 'border-orange-500 text-navy-700'
                        : 'border-transparent text-ink-500 hover:border-ink-300 hover:text-navy-700' ?>"
           <?= $active ? 'aria-current="page"' : '' ?>>
            <?= icon($ico, 'size-4') ?><?= $label ?>
        </a>
    <?php endforeach; ?>
</nav>
