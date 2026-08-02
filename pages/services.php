<?php
partial('header', [
    'title'       => page_title('Food supply and logistics services in Delta State'),
    'description' => 'Wholesale food supply, corporate procurement, cold storage, truck hire, van hire, relocations and scheduled delivery across Asaba, Warri, Sapele, Ughelli and the rest of Delta State.',
    'ogImage'     => 'signage.jpg',
]);
?>

<section class="relative isolate overflow-hidden bg-navy-800">
    <div class="absolute inset-0 bg-grid opacity-30"></div>
    <div class="absolute -right-24 -top-24 size-72 rounded-full bg-orange-500/20 blur-3xl"></div>

    <div class="shell relative py-12 sm:py-16">
        <nav class="mb-5 flex items-center gap-1.5 text-xs text-navy-200" aria-label="Breadcrumb">
            <a class="inline-flex min-h-6 items-center transition-colors hover:text-white" href="<?= url('/') ?>">Home</a>
            <?= icon('chevron-right', 'size-3') ?>
            <span class="text-white">Services</span>
        </nav>
        <div class="max-w-2xl">
            <p class="eyebrow eyebrow-light"><?= icon('sparkle', 'size-3.5') ?>What we offer</p>
            <h1 class="h-section mt-4 text-white">Our services</h1>
            <p class="mt-4 leading-relaxed text-navy-100">
                We started out moving our own foodstuff. Customers kept asking us to move theirs, so now we do both.
            </p>
        </div>
    </div>
</section>

<!-- Food supply -->
<section class="section">
    <div class="shell">
        <div class="mb-12 max-w-2xl">
            <p class="eyebrow"><?= icon('package', 'size-3.5') ?>Foodstuff supply</p>
            <h2 class="h-section mt-3">Wholesale, retail and corporate procurement</h2>
        </div>

        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            <?php foreach ([
                ['percent', 'Wholesale supply', 'Bulk pricing that applies automatically once your quantity hits the threshold on each product. No haggling and no phone calls to find out the real price.'],
                ['cart', 'Retail supply', 'Households and small kitchens buy the same graded stock as our trade accounts, in single bags, cartons and crates.'],
                ['building', 'Corporate supply', 'Standing contracts for hotels, restaurants, schools, churches, hospitals and camps, with a named contact and agreed credit terms.'],
                ['receipt', 'Bulk procurement', 'Send a list, including items we do not stock. We source it, price it in writing and tell you the honest lead time.'],
                ['snowflake', 'Cold storage', 'Freezing at -18&deg;C and chilled bays at 0-4&deg;C in Asaba, with stock reports you can actually reconcile.'],
                ['calendar', 'Scheduled replenishment', 'Set a standing order once and we deliver on the same days each week, without anyone having to raise a fresh order.'],
            ] as [$ico, $heading, $copy]): ?>
                <article class="card card-hover p-7" data-reveal>
                    <span class="grid size-12 place-items-center rounded-xl bg-navy-50 text-navy-600">
                        <?= icon($ico, 'size-6') ?>
                    </span>
                    <h3 class="mt-5 text-lg"><?= $heading ?></h3>
                    <p class="mt-2.5 text-sm leading-relaxed text-ink-500"><?= $copy ?></p>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Logistics -->
<section class="section bg-white">
    <div class="shell">
        <div class="mb-12 max-w-2xl">
            <p class="eyebrow"><?= icon('truck', 'size-3.5') ?>Logistics</p>
            <h2 class="h-section mt-3">Truck hire, van hire and haulage &mdash; with or without a food order</h2>
        </div>

        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            <?php foreach ([
                ['truck', 'Truck &amp; van hire', 'Seven vehicle classes from motorcycle to flatbed, priced before you commit using the estimator on the booking form.'],
                ['snowflake', 'Refrigerated haulage', 'Reefer vehicles with temperature logging on every trip. If the chain breaks, the log shows it and we own the problem.'],
                ['warehouse', 'Relocations', 'Office and warehouse moves with the right vehicle class and an optional two-man loading crew.'],
                ['route', 'Last-mile delivery', 'Multi-drop routes across Asaba and the Delta corridor, with proof of delivery captured at each stop and pushed to your timeline.'],
                ['map-pin', 'Interstate haulage', 'Full and part loads beyond Delta State, quoted per route. Backhaul capacity is priced lower when your route matches ours.'],
                ['package', 'Warehousing', 'Ambient racked space in Asaba with pick-and-pack, cross-docking and goods-in inspection.'],
            ] as [$ico, $heading, $copy]): ?>
                <article class="card card-hover p-7" data-reveal>
                    <span class="grid size-12 place-items-center rounded-xl bg-orange-50 text-orange-600">
                        <?= icon($ico, 'size-6') ?>
                    </span>
                    <h3 class="mt-5 text-lg"><?= $heading ?></h3>
                    <p class="mt-2.5 text-sm leading-relaxed text-ink-500"><?= $copy ?></p>
                </article>
            <?php endforeach; ?>
        </div>

        <div class="mt-12 text-center">
            <a class="btn btn-primary btn-lg gap-2" href="<?= url('/logistics') ?>">
                <?= icon('truck', 'size-5') ?>Get an instant logistics estimate
            </a>
        </div>
    </div>
</section>

<!-- Service levels + coverage -->
<section class="section">
    <div class="shell">
        <div class="grid items-start gap-12 lg:grid-cols-2">
            <div>
                <p class="eyebrow"><?= icon('shield', 'size-3.5') ?>Service levels</p>
                <h2 class="h-section mt-3">Pick the level that matches the cargo</h2>
                <p class="lede mt-4">
                    Not everything needs a reefer, and not everything survives without one. We price four
                    service levels so you are not paying cold-chain rates on a pallet of salt.
                </p>

                <div class="mt-8 space-y-3">
                    <?php foreach ([
                        ['package', 'Standard', 'Ambient dry goods, 24 to 72 hour lead time.'],
                        ['snowflake', 'Refrigerated', 'Chilled and frozen, temperature logged end to end.'],
                        ['clock', 'Same-day express', 'Within Asaba, ordered before 10am.'],
                        ['truck', 'Bulk haulage', 'Full or part load, interstate, quoted by route.'],
                    ] as [$ico, $name, $copy]): ?>
                        <div class="flex items-start gap-4 rounded-xl border border-ink-200 bg-white p-4">
                            <span class="grid size-10 shrink-0 place-items-center rounded-lg bg-navy-50 text-navy-600">
                                <?= icon($ico, 'size-5') ?>
                            </span>
                            <div>
                                <p class="font-display font-bold text-navy-700"><?= $name ?></p>
                                <p class="text-sm text-ink-500"><?= $copy ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="card overflow-hidden">
                <div class="border-b border-ink-100 px-6 py-5">
                    <h2 class="text-lg">Coverage and lead times</h2>
                </div>
                <div class="table-wrap">
                    <table class="table">
                        <thead><tr><th>Zone</th><th>Lead time</th><th class="num">Delivery from</th></tr></thead>
                        <tbody>
                        <?php foreach ([
                            ['Asaba and environs', 'Same day - 24h', money(Setting::get('delivery_fee', (string) DELIVERY_FEE))],
                            ['Warri, Effurun, Sapele', '24h', money(8000)],
                            ['Ughelli, Abraka, Agbor, Oghara', '24 - 48h', money(11000)],
                            ['Edo, Anambra, Bayelsa, Rivers', '48h', money(24000)],
                            ['Rest of Nigeria', '48 - 96h', money(38000)],
                        ] as [$zone, $lead, $from]): ?>
                            <tr>
                                <td class="cell-title"><?= $zone ?></td>
                                <td class="text-sm text-ink-500"><?= $lead ?></td>
                                <td class="num font-semibold text-navy-700"><?= $from ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <p class="border-t border-ink-100 bg-ink-50 px-6 py-4 text-xs text-ink-400">
                    Indicative rates for a part load. Full loads and standing routes are quoted per contract.
                </p>
            </div>
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
                    <h2 class="h-section text-white">Need capacity next week?</h2>
                    <p class="mt-3 text-navy-100">
                        Tell us the route, the cargo and the temperature it needs to travel at.
                    </p>
                </div>
                <div class="flex flex-wrap gap-3">
                    <a class="btn btn-accent btn-lg gap-2" href="<?= url('/logistics') ?>">
                        <?= icon('truck', 'size-5') ?>Book a vehicle
                    </a>
                    <a class="btn btn-light btn-lg" href="<?= url('/quote') ?>">Request a food quote</a>
                </div>
            </div>
        </div>
    </div>
</section>

<?php partial('footer'); ?>
