<?php
/** @var array $params */
$booking = Booking::findByReference($params['reference'] ?? '');

$placedHere = in_array($booking['reference'] ?? '', $_SESSION['_recent_bookings'] ?? [], true);
$owned      = $booking && auth_id() && (int) $booking['user_id'] === auth_id();

if (!$booking || !($placedHere || $owned || is_staff())) {
    abort_404();
}

$events = Booking::events((int) $booking['id']);
$price  = $booking['quoted_price'] !== null ? (float) $booking['quoted_price'] : (float) $booking['estimated_price'];

partial('header', ['title' => page_title('Booking ' . $booking['reference'])]);
?>

<section class="section-sm pb-20">
    <div class="shell-tight">

        <div class="card overflow-hidden">
            <div class="relative isolate bg-navy-800 px-6 py-10 text-center sm:px-10">
                <div class="absolute inset-0 bg-grid opacity-30"></div>
                <div class="absolute -right-20 -top-20 size-56 rounded-full bg-orange-500/25 blur-3xl"></div>

                <div class="relative">
                    <span class="mx-auto grid size-16 place-items-center rounded-2xl bg-emerald-500 text-white shadow-lift">
                        <?= icon('truck', 'size-8') ?>
                    </span>
                    <p class="mt-5 badge badge-success">Booking received</p>
                    <h1 class="mt-4 font-display text-3xl font-extrabold text-white">
                        Thank you, <?= e(explode(' ', $booking['customer_name'])[0]) ?>.
                    </h1>
                    <p class="mx-auto mt-3 max-w-lg leading-relaxed text-navy-100">
                        Dispatch is checking vehicle availability for <?= e(date_human($booking['pickup_date'])) ?>.
                        You will get a firm price before anything moves.
                    </p>

                    <button class="mt-6 inline-flex cursor-pointer items-center gap-2 rounded-xl border border-white/25 bg-white/10 px-5 py-3 font-mono text-lg font-bold text-white backdrop-blur transition-colors hover:bg-white/20"
                            data-copy="<?= e($booking['reference']) ?>">
                        <?= icon('copy', 'size-4') ?><span data-copy-label><?= e($booking['reference']) ?></span>
                    </button>
                    <p class="mt-2 text-xs text-navy-200">Keep this reference to track the job</p>
                </div>
            </div>

            <div class="flex flex-wrap items-center justify-center gap-3 border-b border-ink-100 bg-ink-50 px-6 py-4">
                <a class="btn btn-primary gap-2" href="<?= url('/track') ?>?reference=<?= e($booking['reference']) ?>">
                    <?= icon('route', 'size-4') ?>Track this booking
                </a>
                <a class="btn btn-ghost" href="<?= url('/logistics') ?>">Book another job</a>
                <button class="btn btn-ghost gap-2 no-print" onclick="window.print()">
                    <?= icon('printer', 'size-4') ?>Print
                </button>
            </div>

            <div class="p-6 sm:p-8">
                <!-- Route -->
                <div class="grid gap-4 sm:grid-cols-[1fr_auto_1fr] sm:items-center">
                    <div class="rounded-xl border border-ink-200 bg-ink-50 p-4">
                        <p class="flex items-center gap-1.5 text-xs font-bold uppercase tracking-wider text-ink-400">
                            <?= icon('map-pin', 'size-3.5 text-orange-500') ?>Pickup
                        </p>
                        <p class="mt-2 font-semibold text-navy-700"><?= e($booking['pickup_city']) ?></p>
                        <p class="text-sm text-ink-500"><?= e($booking['pickup_address']) ?></p>
                    </div>

                    <span class="mx-auto hidden text-ink-300 sm:block"><?= icon('arrow-right', 'size-6') ?></span>

                    <div class="rounded-xl border border-ink-200 bg-ink-50 p-4">
                        <p class="flex items-center gap-1.5 text-xs font-bold uppercase tracking-wider text-ink-400">
                            <?= icon('route', 'size-3.5 text-orange-500') ?>Destination
                        </p>
                        <p class="mt-2 font-semibold text-navy-700"><?= e($booking['destination_city']) ?></p>
                        <p class="text-sm text-ink-500"><?= e($booking['destination_address']) ?></p>
                    </div>
                </div>

                <!-- Job detail -->
                <div class="mt-6 flex items-center justify-between gap-4">
                    <h2 class="text-lg">Job details</h2>
                    <span class="badge badge-<?= status_tone($booking['status']) === 'brand' ? 'navy' : status_tone($booking['status']) ?>">
                        <?= e(Booking::statuses()[$booking['status']] ?? $booking['status']) ?>
                    </span>
                </div>

                <dl class="mt-4 divide-y divide-ink-100 border-y border-ink-100 text-sm">
                    <?php foreach ([
                        'Service'       => e($booking['service_type']),
                        'Vehicle'       => e($booking['vehicle_type']),
                        'Date'          => e(date_human($booking['pickup_date'])) . ' &middot; ' . e($booking['pickup_time'] ?: 'Flexible'),
                        'Distance band' => e($booking['distance_band']),
                        'Weight'        => number_format((int) $booking['weight_kg']) . 'kg',
                        'Urgency'       => e($booking['urgency']),
                        'Loading crew'  => (int) $booking['needs_labour'] ? 'Yes' : 'No',
                    ] as $term => $val): ?>
                        <div class="flex justify-between gap-4 py-3">
                            <dt class="text-ink-500"><?= $term ?></dt>
                            <dd class="text-right font-semibold text-navy-700"><?= $val ?></dd>
                        </div>
                    <?php endforeach; ?>
                </dl>

                <div class="mt-5 flex items-baseline justify-between gap-4 rounded-xl bg-navy-700 px-5 py-4 text-white">
                    <span class="font-display font-bold">
                        <?= $booking['quoted_price'] !== null ? 'Agreed price' : 'Estimate' ?>
                    </span>
                    <span class="price font-display text-2xl font-extrabold"><?= money($price) ?></span>
                </div>
                <?php if ($booking['quoted_price'] === null): ?>
                    <p class="mt-2 text-xs text-ink-400">This is the automatic estimate. Dispatch will confirm the firm price.</p>
                <?php endif; ?>
            </div>
        </div>

        <?php if ($booking['description'] || $booking['instructions']): ?>
            <div class="card card-pad mt-6">
                <?php if ($booking['description']): ?>
                    <h2 class="flex items-center gap-2 text-base"><?= icon('package', 'size-5 text-orange-500') ?>Cargo</h2>
                    <p class="mt-2 text-sm leading-relaxed text-ink-500"><?= nl2br(e($booking['description'])) ?></p>
                <?php endif; ?>
                <?php if ($booking['instructions']): ?>
                    <h2 class="mt-5 flex items-center gap-2 text-base"><?= icon('info', 'size-5 text-orange-500') ?>Instructions</h2>
                    <p class="mt-2 text-sm leading-relaxed text-ink-500"><?= nl2br(e($booking['instructions'])) ?></p>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <div class="card card-pad mt-6">
            <h2 class="text-lg">Progress</h2>
            <div class="mt-6">
                <?php partial('timeline', [
                    'events'     => $events,
                    'status'     => $booking['status'],
                    'milestones' => Booking::milestones(),
                    'labels'     => Booking::statuses(),
                ]); ?>
            </div>
        </div>
    </div>
</section>

<?php partial('footer'); ?>
