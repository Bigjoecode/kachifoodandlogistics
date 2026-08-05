<?php
partial('header', [
    'title'       => page_title('About us'),
    'description' => 'KACHI Foodstuff Supplies & Logistics sources staples direct, stores them properly and delivers them on schedule across Delta State.',
    'ogImage'     => 'signage.jpg',
]);
?>

<section class="relative isolate overflow-hidden bg-navy-800">
    <div class="absolute inset-0 bg-grid opacity-30"></div>
    <div class="absolute -bottom-24 -left-24 size-72 rounded-full bg-orange-500/20 blur-3xl"></div>

    <div class="shell relative py-12 sm:py-16">
        <nav class="mb-5 flex items-center gap-1.5 text-xs text-navy-200" aria-label="Breadcrumb">
            <a class="inline-flex min-h-6 items-center transition-colors hover:text-white" href="<?= url('/') ?>">Home</a>
            <?= icon('chevron-right', 'size-3') ?>
            <span class="text-white">About</span>
        </nav>
        <div class="max-w-2xl">
            <p class="eyebrow eyebrow-light"><?= icon('building', 'size-3.5') ?>Who we are</p>
            <h1 class="h-section mt-4 text-white">About KACHI Foodstuff Supplies &amp; Logistics</h1>
            <p class="mt-4 leading-relaxed text-navy-100">
                We are a food supply and distribution business built around one idea: a kitchen should
                never have to guess whether its order is coming.
            </p>
        </div>
    </div>
</section>

<!-- Story -->
<section class="section">
    <div class="shell">
        <div class="grid items-center gap-12 lg:grid-cols-2">
            <div>
                <p class="eyebrow"><?= icon('sparkle', 'size-3.5') ?>Our story</p>
                <h2 class="h-section mt-3">Started in Asaba with one truck and a rice contract</h2>
                <div class="prose-body mt-5">
                    <p>
                        We began by supplying rice and palm oil to a handful of Asaba kitchens that were tired
                        of suppliers who quoted one price and delivered another. Word travelled through Warri
                        and Sapele. Within two years we were running our own warehouse, then our own cold
                        rooms, then our own fleet.
                    </p>
                    <p>
                        Today we serve hotels, restaurants, supermarkets, schools, churches and event vendors
                        right across Delta State. What has not changed is the thing that got us here: if we
                        say it arrives Thursday, it arrives Thursday.
                    </p>
                </div>
            </div>

            <picture>
                <source srcset="<?= asset('img/photos/market.webp') ?>" type="image/webp">
                <img src="<?= asset('img/photos/market.jpg') ?>"
                     alt="The Asaba market district where KACHI sources and delivers"
                     class="w-full rounded-3xl border border-ink-200 shadow-soft" loading="lazy" width="1600" height="900">
            </picture>
        </div>
    </div>
</section>

<!-- Numbers -->
<section class="bg-navy-900 py-14 text-navy-100">
    <div class="shell">
        <dl class="grid gap-8 sm:grid-cols-2 lg:grid-cols-4">
            <?php foreach ([
                ['2018', 'Year we started'],
                ['8', 'Delta towns on scheduled routes'],
                ['1,800m&sup2;', 'Warehouse and cold storage'],
                ['400+', 'Active trade accounts'],
            ] as [$value, $label]): ?>
                <div>
                    <dt class="font-display text-4xl font-extrabold leading-none text-white"><?= $value ?></dt>
                    <dd class="mt-2 text-sm text-navy-200"><?= $label ?></dd>
                </div>
            <?php endforeach; ?>
        </dl>
    </div>
</section>

<!-- How we work -->
<section class="section bg-white">
    <div class="shell">
        <div class="grid items-center gap-12 lg:grid-cols-2">
            <div class="grid gap-4 lg:order-2">
                <picture>
                    <source srcset="<?= asset('img/photos/stall-team.webp') ?>" type="image/webp">
                    <img src="<?= asset('img/photos/stall-team.jpg') ?>"
                         alt="KACHI staff at the market stall"
                         class="w-full rounded-3xl border border-ink-200 shadow-soft" loading="lazy" width="1200" height="800">
                </picture>
                <div class="grid grid-cols-2 gap-4">
                    <picture>
                        <source srcset="<?= asset('img/photos/stock-basins.webp') ?>" type="image/webp">
                        <img src="<?= asset('img/photos/stock-basins.jpg') ?>"
                             alt="Basins of assorted grains and beans"
                             class="h-full w-full rounded-2xl border border-ink-200 object-cover shadow-soft"
                             loading="lazy" width="1200" height="800">
                    </picture>
                    <picture>
                        <source srcset="<?= asset('img/photos/fleet-truck-sq.webp') ?>" type="image/webp">
                        <img src="<?= asset('img/photos/fleet-truck-sq.jpg') ?>"
                             alt="The KACHI delivery truck"
                             class="h-full w-full rounded-2xl border border-ink-200 object-cover shadow-soft"
                             loading="lazy" width="900" height="900">
                    </picture>
                </div>
            </div>

            <div class="lg:order-1">
                <p class="eyebrow"><?= icon('route', 'size-3.5') ?>How we work</p>
                <h2 class="h-section mt-3">Fewer hands between the farm and your kitchen</h2>
                <ul class="tick-list mt-6">
                    <li>We buy at the mill and the farm gate, not through three layers of traders.</li>
                    <li>Every intake lot is graded, weighed and moisture tested before it is accepted.</li>
                    <li>Frozen and chilled stock never leaves temperature, from processor to your door.</li>
                    <li>Stock rotates on a strict first-in, first-out cycle, so nothing sits and ages.</li>
                    <li>Pricing is quoted per unit in writing. There are no surprises on the invoice.</li>
                </ul>
            </div>
        </div>
    </div>
</section>

<!-- Commitments -->
<section class="section">
    <div class="shell">
        <div class="mx-auto mb-12 max-w-2xl text-center">
            <p class="eyebrow"><?= icon('shield', 'size-3.5') ?>What we stand on</p>
            <h2 class="h-section mt-3">Three commitments we do not negotiate</h2>
        </div>

        <div class="grid gap-6 lg:grid-cols-3">
            <?php foreach ([
                ['search', 'Traceability', 'Every lot carries an origin and an intake date. If something is wrong with a bag, we can tell you which mill it came from and when.'],
                ['snowflake', 'Temperature integrity', 'Cold chain is either unbroken or it is not cold chain. We log it, and we will show you the log if you ask for it.'],
                ['clock', 'Honest lead times', 'We quote the date we can actually hit, not the date that wins the order. A missed delivery costs you more than a longer quote does.'],
            ] as $i => [$ico, $heading, $copy]): ?>
                <article class="card card-hover p-7" data-reveal>
                    <div class="flex items-center gap-3">
                        <span class="grid size-12 place-items-center rounded-xl bg-orange-50 text-orange-600">
                            <?= icon($ico, 'size-6') ?>
                        </span>
                        <span class="font-display text-3xl font-extrabold text-ink-100">0<?= $i + 1 ?></span>
                    </div>
                    <h3 class="mt-5 text-lg"><?= $heading ?></h3>
                    <p class="mt-2.5 text-sm leading-relaxed text-ink-500"><?= $copy ?></p>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- CTA -->
<section class="section-sm pb-20">
    <div class="shell">
        <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-navy-700 to-navy-900 p-8 shadow-deep sm:p-12">
            <div class="absolute -right-16 -top-16 size-64 rounded-full bg-orange-500/25 blur-3xl"></div>
            <div class="relative flex flex-wrap items-center justify-between gap-8">
                <div class="max-w-xl">
                    <h2 class="h-section text-white">Open a trade account</h2>
                    <p class="mt-3 text-navy-100">
                        Volume pricing, credit terms and a named account manager for regular buyers.
                    </p>
                </div>
                <div class="flex flex-wrap gap-3">
                    <a class="btn btn-accent btn-lg gap-2" href="<?= url('/register') ?>">
                        <?= icon('user', 'size-5') ?>Create an account
                    </a>
                    <a class="btn btn-light btn-lg" href="<?= url('/contact') ?>">Talk to us</a>
                </div>
            </div>
        </div>
    </div>
</section>

<?php partial('footer'); ?>
