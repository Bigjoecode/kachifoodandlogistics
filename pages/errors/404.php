<?php
$code = http_response_code() === 405 ? 405 : 404;
partial('header', ['title' => page_title($code === 405 ? 'Not allowed' : 'Page not found')]);
?>

<section class="section">
    <div class="shell">
        <div class="mx-auto max-w-lg text-center">
            <p class="font-display text-8xl font-extrabold leading-none text-ink-200"><?= $code ?></p>

            <h1 class="mt-6 h-section">
                <?= $code === 405 ? 'That action is not allowed here' : 'We could not find that page' ?>
            </h1>

            <p class="lede mt-4">
                <?= $code === 405
                    ? 'The page exists, but not for the way it was requested. Head back and try again.'
                    : 'The link may be out of date, or the product may have been retired from the catalogue.' ?>
            </p>

            <div class="mt-8 flex flex-wrap justify-center gap-3">
                <a class="btn btn-primary btn-lg gap-2" href="<?= url('/') ?>">
                    <?= icon('home', 'size-5') ?>Back to home
                </a>
                <a class="btn btn-ghost btn-lg gap-2" href="<?= url('/products') ?>">
                    <?= icon('package', 'size-5') ?>Browse the catalogue
                </a>
            </div>

            <div class="mt-12 grid gap-3 sm:grid-cols-3">
                <?php foreach ([
                    ['/logistics', 'truck', 'Book logistics'],
                    ['/track', 'route', 'Track an order'],
                    ['/contact', 'message', 'Contact us'],
                ] as [$href, $ico, $label]): ?>
                    <a class="card card-hover flex items-center gap-3 p-4 text-left" href="<?= url($href) ?>">
                        <span class="grid size-9 shrink-0 place-items-center rounded-lg bg-navy-50 text-navy-600">
                            <?= icon($ico, 'size-4') ?>
                        </span>
                        <span class="text-sm font-semibold text-navy-700"><?= $label ?></span>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</section>

<?php partial('footer'); ?>
