<?php
$categories = Category::withCounts();
$featured   = Product::featured(8);
$totalSkus  = (int) Db::value('SELECT COUNT(*) FROM products WHERE is_active = 1');
$areas      = service_areas();
$whatsapp   = Setting::get('whatsapp');

partial('header', [
    'title'       => 'Foodstuff Supplies & Logistics in Asaba, Delta State | ' . APP_NAME,
    'description' => 'KACHI supplies foodstuff wholesale and retail across Delta State and runs its own fleet for truck hire, van hire and scheduled delivery. Order online or book logistics in minutes.',
]);
?>

<!-- ============================ Hero ============================ -->
<section class="relative isolate overflow-hidden bg-navy-900">
    <img src="<?= asset('img/truck.jpg') ?>" alt=""
         class="absolute inset-0 size-full object-cover object-center opacity-25" width="1200" height="800" fetchpriority="high">
    <div class="absolute inset-0 bg-gradient-to-br from-navy-950 via-navy-900/95 to-navy-800/80"></div>
    <div class="absolute inset-0 bg-grid opacity-40"></div>
    <div class="absolute -right-32 -top-32 size-[32rem] rounded-full bg-orange-500/20 blur-3xl"></div>

    <div class="shell relative py-16 lg:py-24">
        <div class="grid items-center gap-12 lg:grid-cols-12">

            <div class="lg:col-span-7">
                <p class="eyebrow eyebrow-light animate-rise">
                    <?= icon('map-pin', 'size-3.5') ?>
                    <?= implode(' &middot; ', array_map('e', array_slice($areas, 0, 5))) ?>
                </p>

                <h1 class="h-display mt-5 text-white text-balance-safe">
                    Foodstuff Supplies &amp;
                    <span class="relative whitespace-nowrap">
                        <span class="relative z-10 text-orange-400">Logistics</span>
                        <span class="absolute inset-x-0 bottom-1.5 z-0 h-3 rounded bg-orange-500/25"></span>
                    </span>
                    You Can Trust
                </h1>

                <p class="mt-6 max-w-xl text-lg leading-relaxed text-navy-100">
                    From bulk food supplies to dependable logistics, KACHI delivers quality products
                    and reliable transportation across <?= e(APP_STATE) ?>.
                </p>

                <div class="mt-9 flex flex-wrap gap-3">
                    <a class="btn btn-accent btn-lg gap-2" href="<?= url('/products') ?>">
                        <?= icon('cart', 'size-5') ?>Order food
                    </a>
                    <a class="btn btn-light btn-lg gap-2" href="<?= url('/logistics') ?>">
                        <?= icon('truck', 'size-5') ?>Book logistics
                    </a>
                    <a class="btn btn-onglass btn-lg gap-2" href="<?= url('/products') ?>">
                        Explore products<?= icon('arrow-right', 'size-4') ?>
                    </a>
                </div>

                <dl class="mt-12 grid max-w-2xl grid-cols-2 gap-x-6 gap-y-7 sm:grid-cols-4">
                    <?php foreach ([
                        [$totalSkus . '+', 'Product lines in stock'],
                        ['7',              'Vehicle classes on fleet'],
                        ['-18&deg;C',      'Unbroken cold chain'],
                        ['24h',            'Asaba turnaround'],
                    ] as [$value, $label]): ?>
                        <div>
                            <dt class="font-display text-3xl font-extrabold leading-none text-white"><?= $value ?></dt>
                            <dd class="mt-1.5 text-xs leading-snug text-navy-200"><?= $label ?></dd>
                        </div>
                    <?php endforeach; ?>
                </dl>
            </div>

            <!-- Tracking panel -->
            <div class="lg:col-span-5">
                <div class="panel-glass p-6 shadow-deep sm:p-8">
                    <div class="mb-5 flex items-center gap-3">
                        <span class="grid size-11 place-items-center rounded-xl bg-orange-500 text-white">
                            <?= icon('route', 'size-5') ?>
                        </span>
                        <div>
                            <h2 class="text-lg text-white">Track an order</h2>
                            <p class="text-xs text-navy-200">Food orders and logistics bookings</p>
                        </div>
                    </div>

                    <form method="post" action="<?= url('/track') ?>" class="space-y-4">
                        <?= csrf_field() ?>
                        <div>
                            <label class="mb-1.5 block text-sm font-semibold text-white" for="hero-ref">Reference</label>
                            <input class="input font-mono" id="hero-ref" name="reference" placeholder="KFL-20260801-1042"
                                   autocomplete="off" required>
                        </div>
                        <div>
                            <label class="mb-1.5 block text-sm font-semibold text-white" for="hero-contact">Email or phone on the order</label>
                            <input class="input" id="hero-contact" name="contact" type="email" placeholder="you@company.com" required>
                        </div>
                        <button class="btn btn-accent btn-block btn-lg gap-2" type="submit">
                            Track now<?= icon('arrow-right', 'size-4') ?>
                        </button>
                    </form>

                    <p class="mt-4 text-xs leading-relaxed text-navy-200">
                        Food orders start <span class="font-mono font-semibold text-orange-300">KFL-</span>,
                        logistics bookings start <span class="font-mono font-semibold text-orange-300">KFL-L-</span>.
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ============================ Trusted by ============================ -->
<section class="border-b border-ink-200 bg-white py-10">
    <div class="shell">
        <p class="mb-6 text-center text-xs font-bold uppercase tracking-[0.18em] text-ink-400">Trusted by</p>
        <div class="flex flex-wrap justify-center gap-2.5">
            <?php foreach ([
                ['Hotels', 'building'], ['Restaurants', 'flame'], ['Churches', 'users'], ['Schools', 'users'],
                ['Corporate offices', 'building'], ['Supermarkets', 'cart'], ['Event vendors', 'sparkle'], ['Homes', 'home'],
            ] as [$sector, $ico]): ?>
                <span class="chip gap-2"><?= icon($ico, 'size-4 text-orange-500') ?><?= $sector ?></span>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ============================ Two paths ============================ -->
<section class="section">
    <div class="shell">
        <div class="mx-auto mb-14 max-w-2xl text-center">
            <p class="eyebrow"><?= icon('sparkle', 'size-3.5') ?>What we do</p>
            <h2 class="h-section mt-3">Two businesses, one delivery note</h2>
            <p class="lede mt-4">
                Buy your foodstuff from us, hire our vehicles, or do both and consolidate it into a single drop.
            </p>
        </div>

        <div class="grid gap-6 lg:grid-cols-2">
            <article class="card card-hover group relative overflow-hidden" data-reveal>
                <div class="absolute right-0 top-0 size-40 rounded-full bg-orange-50 blur-2xl transition-opacity group-hover:opacity-80"></div>
                <div class="relative p-8">
                    <span class="grid size-14 place-items-center rounded-2xl bg-navy-700 text-white shadow-soft">
                        <?= icon('package', 'size-7') ?>
                    </span>
                    <h3 class="mt-6 text-2xl">Foodstuff supply</h3>
                    <p class="mt-3 text-ink-500 leading-relaxed">
                        Wholesale and retail across grains, tubers, oils, proteins, spices, frozen lines and
                        household essentials. Corporate supply for hotels, schools, churches and hospitals,
                        with volume pricing that drops automatically as your quantity rises.
                    </p>
                    <ul class="tick-list mt-6 text-sm">
                        <li>Retail and wholesale price on every product</li>
                        <li>Graded, weighed and moisture tested on intake</li>
                        <li>Scheduled replenishment for standing orders</li>
                    </ul>
                    <a class="btn btn-primary mt-8 gap-2" href="<?= url('/products') ?>">
                        Browse the catalogue<?= icon('arrow-right', 'size-4') ?>
                    </a>
                </div>
            </article>

            <article class="card card-hover group relative overflow-hidden" data-reveal>
                <div class="absolute right-0 top-0 size-40 rounded-full bg-navy-50 blur-2xl transition-opacity group-hover:opacity-80"></div>
                <div class="relative p-8">
                    <span class="grid size-14 place-items-center rounded-2xl bg-orange-500 text-white shadow-soft">
                        <?= icon('truck', 'size-7') ?>
                    </span>
                    <h3 class="mt-6 text-2xl">Logistics &amp; haulage</h3>
                    <p class="mt-3 text-ink-500 leading-relaxed">
                        Motorcycle to flatbed, priced before you commit. Truck hire, van hire, office
                        relocation, market and warehouse runs, business delivery and interstate haulage,
                        all trackable from pickup to drop-off.
                    </p>
                    <ul class="tick-list mt-6 text-sm">
                        <li>Instant estimate before you submit a booking</li>
                        <li>Driver and vehicle assigned to every job</li>
                        <li>No food order required &mdash; book transport on its own</li>
                    </ul>
                    <a class="btn btn-accent mt-8 gap-2" href="<?= url('/logistics') ?>">
                        Get an instant estimate<?= icon('arrow-right', 'size-4') ?>
                    </a>
                </div>
            </article>
        </div>
    </div>
</section>

<!-- ============================ Categories ============================ -->
<section class="section bg-white">
    <div class="shell">
        <div class="mx-auto mb-12 max-w-2xl text-center">
            <p class="eyebrow"><?= icon('wheat', 'size-3.5') ?>Food categories</p>
            <h2 class="h-section mt-3">Everything a kitchen runs on</h2>
            <p class="lede mt-4">Ambient, fresh and frozen lines held in our Asaba warehouse and cold rooms.</p>
        </div>

        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <?php foreach ($categories as $category): ?>
                <a href="<?= url('/category/' . $category['slug']) ?>"
                   class="card card-hover group flex items-center gap-4 p-5" data-reveal>
                    <span class="grid size-12 shrink-0 place-items-center rounded-xl bg-navy-50 text-navy-600
                                 transition-colors duration-300 group-hover:bg-orange-500 group-hover:text-white">
                        <?= icon(category_icon($category['slug']), 'size-6') ?>
                    </span>
                    <span class="min-w-0 flex-1">
                        <span class="block font-display text-sm font-bold leading-snug text-navy-700"><?= e($category['name']) ?></span>
                        <span class="mt-0.5 block text-xs text-ink-400"><?= (int) $category['product_count'] ?> products</span>
                    </span>
                    <?= icon('chevron-right', 'ml-auto size-4 shrink-0 text-ink-300 transition-transform group-hover:translate-x-0.5') ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ============================ Featured ============================ -->
<section class="section">
    <div class="shell">
        <div class="mb-10 flex flex-wrap items-end justify-between gap-4">
            <div>
                <p class="eyebrow"><?= icon('star', 'size-3.5') ?>This week</p>
                <h2 class="h-section mt-3">Featured lines</h2>
            </div>
            <a class="btn btn-ghost gap-2" href="<?= url('/products') ?>">
                See all <?= $totalSkus ?> products<?= icon('arrow-right', 'size-4') ?>
            </a>
        </div>

        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
            <?php foreach ($featured as $product): ?>
                <?php partial('product_card', ['product' => $product]); ?>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ============================ Why us ============================ -->
<section class="section bg-white">
    <div class="shell">
        <div class="mx-auto mb-14 max-w-2xl text-center">
            <p class="eyebrow"><?= icon('shield', 'size-3.5') ?>Why buyers stay</p>
            <h2 class="h-section mt-3">Built for people who order by the tonne</h2>
        </div>

        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            <?php foreach ([
                ['percent', 'Wholesale and retail', 'Every line carries both prices. Hit the wholesale quantity and the cart drops to the bulk rate automatically. No haggling, no phone calls, no surprises on the invoice.'],
                ['wheat', 'Direct sourcing', 'We buy at the mill and the farm gate, so you are not funding three layers of traders. Every intake lot is graded and moisture tested before it is accepted.'],
                ['snowflake', 'Real cold chain', 'Frozen and chilled lines stay at temperature from the processor through our cold rooms to your door, on reefer vehicles that are temperature logged every trip.'],
                ['truck', 'Our own fleet', 'We do not sub-contract your delivery to whoever is free. Seven vehicle classes, our drivers, our vehicles, our responsibility when something goes wrong.'],
                ['route', 'Live tracking', 'Every order and booking carries a reference with a timestamped timeline, from confirmation through dispatch to the signature at delivery.'],
                ['banknote', 'Credit terms', 'Established trade accounts order on 14 or 30 day terms instead of paying up front on every single drop.'],
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
    </div>
</section>

<!-- ============================ Logistics teaser ============================ -->
<section class="section">
    <div class="shell">
        <div class="overflow-hidden rounded-3xl bg-navy-800 shadow-deep">
            <div class="grid lg:grid-cols-2">
                <div class="relative min-h-64 lg:min-h-full">
                    <img src="<?= asset('img/truck.jpg') ?>" alt="A KACHI refrigerated truck ready for dispatch"
                         class="absolute inset-0 size-full object-cover" loading="lazy" width="1200" height="800">
                    <div class="absolute inset-0 bg-gradient-to-t from-navy-900/70 to-transparent lg:bg-gradient-to-r"></div>
                </div>

                <div class="p-8 sm:p-12">
                    <p class="eyebrow eyebrow-light"><?= icon('truck', 'size-3.5') ?>Logistics</p>
                    <h2 class="h-section mt-3 text-white">Know the price before you book</h2>
                    <p class="mt-4 leading-relaxed text-navy-100">
                        Pick a vehicle, a route and a weight, and the estimate updates as you type.
                        Dispatch confirms the firm price against the real load before anything moves.
                    </p>

                    <div class="mt-8 grid gap-3 sm:grid-cols-2">
                        <?php foreach (array_slice(Booking::vehicleTypes(), 0, 4, true) as $vehicle => [$base, $blurb, $capacity]): ?>
                            <div class="rounded-xl border border-white/15 bg-white/5 p-4">
                                <p class="font-display text-sm font-bold text-white"><?= e($vehicle) ?></p>
                                <p class="mt-1 text-xs text-navy-200">Up to <?= number_format($capacity) ?>kg</p>
                                <p class="mt-2 font-display text-lg font-extrabold text-orange-400"><?= money($base) ?></p>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <a class="btn btn-accent btn-lg mt-8 gap-2" href="<?= url('/logistics') ?>">
                        Book a vehicle<?= icon('arrow-right', 'size-4') ?>
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ============================ How it works ============================ -->
<section class="section bg-navy-900 text-navy-100">
    <div class="shell">
        <div class="mb-14 max-w-2xl">
            <p class="eyebrow eyebrow-light"><?= icon('route', 'size-3.5') ?>How it works</p>
            <h2 class="h-section mt-3 text-white">From order to doorstep in four steps</h2>
        </div>

        <ol class="grid gap-8 sm:grid-cols-2 lg:grid-cols-4">
            <?php foreach ([
                ['Build your order', 'Add from the catalogue, or send a bulk list and let us price it.'],
                ['Pick your slot', 'Choose a delivery date, a time window and the service level you need.'],
                ['We pick and load', 'Stock is picked, weighed and loaded on the right vehicle for the goods.'],
                ['Track to delivery', 'Follow the timeline until it is signed for at your location.'],
            ] as $i => [$heading, $copy]): ?>
                <li class="relative" data-reveal>
                    <span class="grid size-12 place-items-center rounded-2xl bg-orange-500 font-display text-lg font-extrabold text-white">
                        <?= $i + 1 ?>
                    </span>
                    <h3 class="mt-5 text-lg text-white"><?= $heading ?></h3>
                    <p class="mt-2 text-sm leading-relaxed text-navy-200"><?= $copy ?></p>
                </li>
            <?php endforeach; ?>
        </ol>
    </div>
</section>

<!-- ============================ Coverage + trust ============================ -->
<section class="section bg-white">
    <div class="shell">
        <div class="grid items-center gap-12 lg:grid-cols-2">
            <div>
                <p class="eyebrow"><?= icon('map-pin', 'size-3.5') ?>Coverage</p>
                <h2 class="h-section mt-3">Where we deliver</h2>
                <p class="lede mt-4">
                    Scheduled routes run daily across <?= e(APP_STATE) ?>, with interstate haulage
                    available on request. If your town is not listed, ask us anyway.
                </p>

                <div class="mt-7 flex flex-wrap gap-2">
                    <?php foreach ($areas as $area): ?>
                        <span class="chip gap-2 text-sm"><?= icon('map-pin', 'size-3.5 text-orange-500') ?><?= e($area) ?></span>
                    <?php endforeach; ?>
                </div>

                <div class="mt-8 rounded-2xl border border-ink-200 bg-ink-50 p-6">
                    <h3 class="text-base">Trust signals</h3>
                    <ul class="tick-list mt-4 text-sm">
                        <li>Registered in Nigeria &mdash; <?= e(Setting::get('cac_number', 'RC 1234567')) ?></li>
                        <li>NAFDAC-registered water and packaged lines only</li>
                        <li>Aflatoxin screening on every groundnut and grain intake</li>
                        <li>Temperature logs available on request for any frozen delivery</li>
                        <li>Written quotes with lead times, before you commit</li>
                    </ul>
                </div>
            </div>

            <div class="grid gap-4">
                <img src="<?= asset('img/signage.jpg') ?>" alt="KACHI branded signage at the Asaba head office"
                     class="w-full rounded-2xl border border-ink-200 object-cover shadow-soft" loading="lazy" width="1200" height="800">
                <img src="<?= asset('img/merch.jpg') ?>" alt="KACHI branded uniforms, stationery and staff identification"
                     class="w-full rounded-2xl border border-ink-200 object-cover shadow-soft" loading="lazy" width="1400" height="600">
            </div>
        </div>
    </div>
</section>

<!-- ============================ CTA ============================ -->
<section class="section-sm pb-20">
    <div class="shell">
        <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-navy-700 to-navy-900 p-8 shadow-deep sm:p-12">
            <div class="absolute -right-16 -top-16 size-64 rounded-full bg-orange-500/25 blur-3xl"></div>
            <div class="relative flex flex-wrap items-center justify-between gap-8">
                <div class="max-w-xl">
                    <h2 class="h-section text-white">Supplying a hotel, school, church or event?</h2>
                    <p class="mt-3 text-navy-100">
                        Send your list and we will come back with wholesale pricing, lead times and a delivery plan.
                    </p>
                </div>
                <div class="flex flex-wrap gap-3">
                    <a class="btn btn-accent btn-lg gap-2" href="<?= url('/quote') ?>">
                        <?= icon('receipt', 'size-5') ?>Request a quote
                    </a>
                    <?php if ($whatsapp): ?>
                        <a class="btn btn-light btn-lg gap-2" href="https://wa.me/<?= e($whatsapp) ?>" rel="noopener">
                            <?= icon('message', 'size-5') ?>WhatsApp sales
                        </a>
                    <?php else: ?>
                        <a class="btn btn-light btn-lg" href="<?= url('/contact') ?>">Talk to sales</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<?php partial('footer'); ?>
