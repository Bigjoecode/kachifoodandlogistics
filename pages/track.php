<?php
/**
 * One tracking form for both kinds of reference: KFL-... is a food order,
 * KFL-L-... is a logistics booking. We route on the prefix.
 */
$order   = null;
$booking = null;
$events  = [];
$items   = [];
$error   = null;
$looked  = false;

if (is_post()) {
    $looked    = true;
    $reference = strtoupper(trim((string) input('reference')));
    $contact   = (string) input('contact');

    if ($reference === '' || $contact === '') {
        $error = 'Enter both your reference and the email or phone on the order.';
    } elseif (str_starts_with($reference, 'KFL-L-')) {
        $booking = Booking::track($reference, $contact);
        if (!$booking) {
            $error = 'We could not match that booking reference with those contact details. Check both and try again.';
        }
    } else {
        $order = Order::track($reference, $contact);
        if (!$order) {
            $error = 'We could not match that reference with those contact details. Check both and try again.';
        }
    }
}

if ($order) {
    $events = Order::events((int) $order['id']);
    $items  = Order::items((int) $order['id']);
} elseif ($booking) {
    $events = Booking::events((int) $booking['id']);
}

/** Badge tone helper — the shared map uses 'brand', which is 'navy' here. */
$tone = fn(string $status) => status_tone($status) === 'brand' ? 'navy' : status_tone($status);

partial('header', [
    'title'       => page_title('Track your order'),
    'description' => 'Follow your KACHI food order or logistics booking from confirmation through dispatch to the door.',
]);
?>

<section class="relative isolate overflow-hidden bg-navy-800">
    <div class="absolute inset-0 bg-grid opacity-30"></div>
    <div class="absolute -right-24 -top-24 size-72 rounded-full bg-orange-500/20 blur-3xl"></div>

    <div class="shell relative py-12 sm:py-16">
        <nav class="mb-5 flex items-center gap-1.5 text-xs text-navy-200" aria-label="Breadcrumb">
            <a class="inline-flex min-h-6 items-center transition-colors hover:text-white" href="<?= url('/') ?>">Home</a>
            <?= icon('chevron-right', 'size-3') ?>
            <span class="text-white">Track an order</span>
        </nav>
        <div class="max-w-2xl">
            <p class="eyebrow eyebrow-light"><?= icon('route', 'size-3.5') ?>Live status</p>
            <h1 class="h-section mt-4 text-white">Track your order</h1>
            <p class="mt-4 leading-relaxed text-navy-100">
                Works for both food orders (<span class="font-mono text-orange-300">KFL-</span>) and logistics
                bookings (<span class="font-mono text-orange-300">KFL-L-</span>). Enter the reference from your
                confirmation plus the email or phone on it.
            </p>
        </div>
    </div>
</section>

<section class="section-sm pb-20">
    <div class="shell">
        <div class="grid gap-8 lg:grid-cols-[22rem_minmax(0,1fr)] lg:items-start">

            <!-- Lookup -->
            <div class="card card-pad lg:sticky lg:top-28">
                <h2 class="text-lg">Find my delivery</h2>
                <form method="post" class="mt-5">
                    <?= csrf_field() ?>
                    <div class="field">
                        <label class="label" for="reference">Reference</label>
                        <input class="input font-mono<?= $error ? ' input-error' : '' ?>" id="reference" name="reference"
                               value="<?= e(input('reference', '')) ?>" placeholder="KFL-20260801-1042"
                               autocomplete="off" spellcheck="false" required>
                    </div>
                    <div class="field">
                        <label class="label" for="contact">Email or phone on the order</label>
                        <input class="input<?= $error ? ' input-error' : '' ?>" id="contact" name="contact"
                               value="<?= e(input('contact', '')) ?>" required>
                    </div>
                    <button class="btn btn-primary btn-block btn-lg gap-2" type="submit">
                        <?= icon('search', 'size-5') ?>Track order
                    </button>
                </form>

                <div class="divider"></div>

                <p class="text-sm leading-relaxed text-ink-500">
                    Lost the reference? <a class="link-quiet" href="<?= url('/contact') ?>">Message us</a> with the
                    delivery date and company name, or <a class="link-quiet" href="<?= url('/login') ?>">sign in</a>
                    to see everything on your account.
                </p>
            </div>

            <!-- Result -->
            <div>
                <?php if ($error): ?>
                    <div class="alert alert-error" role="alert">
                        <?= icon('alert', 'size-5 shrink-0') ?><span><?= e($error) ?></span>
                    </div>
                <?php endif; ?>

                <?php if ($order): ?>
                    <div class="card overflow-hidden">
                        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-ink-100 bg-ink-50 px-6 py-5">
                            <div>
                                <h2 class="font-mono text-lg font-bold text-navy-700"><?= e($order['reference']) ?></h2>
                                <p class="text-xs text-ink-400">Placed <?= e(date_human($order['created_at'], true)) ?></p>
                            </div>
                            <span class="badge badge-<?= $tone($order['status']) ?>"><?= e(status_label($order['status'])) ?></span>
                        </div>

                        <div class="p-6 sm:p-8">
                            <?php if ($order['status'] === 'cancelled'): ?>
                                <div class="alert alert-error">
                                    <?= icon('alert', 'size-5 shrink-0') ?>
                                    <span>This order was cancelled. Contact us if that is unexpected.</span>
                                </div>
                            <?php endif; ?>

                            <?php partial('timeline', [
                                'events'     => $events,
                                'status'     => $order['status'],
                                'milestones' => tracking_milestones(),
                                'labels'     => order_statuses(),
                            ]); ?>
                        </div>

                        <div class="grid gap-4 border-t border-ink-100 bg-ink-50 p-6 sm:grid-cols-2">
                            <div>
                                <p class="text-xs font-bold uppercase tracking-wider text-ink-400">Delivering to</p>
                                <p class="mt-1.5 text-sm text-ink-600">
                                    <?= e($order['delivery_address']) ?><br>
                                    <?= e($order['city']) ?>, <?= e($order['state']) ?>
                                </p>
                            </div>
                            <div>
                                <p class="text-xs font-bold uppercase tracking-wider text-ink-400">Scheduled</p>
                                <p class="mt-1.5 text-sm text-ink-600">
                                    <?= e(date_human($order['delivery_date'])) ?><br>
                                    <?= e($order['delivery_window'] ?: 'Any time') ?>
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="card mt-6 overflow-hidden">
                        <div class="border-b border-ink-100 px-6 py-4">
                            <h2 class="text-base"><?= count($items) ?> item<?= count($items) === 1 ? '' : 's' ?></h2>
                        </div>
                        <div class="table-wrap">
                            <table class="table">
                                <thead><tr><th>Item</th><th class="num">Qty</th><th class="num">Total</th></tr></thead>
                                <tbody>
                                <?php foreach ($items as $item): ?>
                                    <tr>
                                        <td>
                                            <span class="cell-title"><?= e($item['product_name']) ?></span>
                                            <span class="cell-sub block">per <?= e($item['unit']) ?></span>
                                        </td>
                                        <td class="num"><?= (int) $item['quantity'] ?></td>
                                        <td class="num font-semibold text-navy-700">
                                            <?= $item['line_total'] > 0 ? money($item['line_total']) : 'To quote' ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="flex items-baseline justify-between gap-4 border-t border-ink-100 bg-ink-50 px-6 py-4">
                            <span class="font-display font-bold text-navy-700">Order total</span>
                            <span class="price font-display text-xl font-extrabold text-navy-700"><?= money($order['total']) ?></span>
                        </div>
                    </div>

                <?php elseif ($booking): ?>
                    <div class="card overflow-hidden">
                        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-ink-100 bg-ink-50 px-6 py-5">
                            <div>
                                <h2 class="font-mono text-lg font-bold text-navy-700"><?= e($booking['reference']) ?></h2>
                                <p class="text-xs text-ink-400">
                                    <?= e($booking['service_type']) ?> booked <?= e(date_human($booking['created_at'], true)) ?>
                                </p>
                            </div>
                            <span class="badge badge-<?= $tone($booking['status']) ?>">
                                <?= e(Booking::statuses()[$booking['status']] ?? $booking['status']) ?>
                            </span>
                        </div>

                        <div class="p-6 sm:p-8">
                            <?php if ($booking['status'] === 'cancelled'): ?>
                                <div class="alert alert-error">
                                    <?= icon('alert', 'size-5 shrink-0') ?>
                                    <span>This booking was cancelled. Contact us if that is unexpected.</span>
                                </div>
                            <?php endif; ?>

                            <?php partial('timeline', [
                                'events'     => $events,
                                'status'     => $booking['status'],
                                'milestones' => Booking::milestones(),
                                'labels'     => Booking::statuses(),
                            ]); ?>
                        </div>

                        <div class="border-t border-ink-100 bg-ink-50 p-6">
                            <div class="grid gap-4 sm:grid-cols-2">
                                <div>
                                    <p class="text-xs font-bold uppercase tracking-wider text-ink-400">Route</p>
                                    <p class="mt-1.5 flex items-center gap-2 text-sm text-ink-600">
                                        <?= e($booking['pickup_city']) ?>
                                        <?= icon('arrow-right', 'size-3.5 text-ink-300') ?>
                                        <?= e($booking['destination_city']) ?>
                                    </p>
                                    <p class="text-sm text-ink-500"><?= e($booking['vehicle_type']) ?></p>
                                </div>
                                <div>
                                    <p class="text-xs font-bold uppercase tracking-wider text-ink-400">Pickup</p>
                                    <p class="mt-1.5 text-sm text-ink-600">
                                        <?= e(date_human($booking['pickup_date'])) ?><br>
                                        <?= e($booking['pickup_time'] ?: 'Flexible') ?>
                                    </p>
                                </div>
                            </div>

                            <?php if ($booking['driver_name']): ?>
                                <div class="mt-4 flex flex-wrap items-center gap-3 rounded-xl border border-ink-200 bg-white p-4">
                                    <span class="grid size-10 place-items-center rounded-lg bg-navy-50 text-navy-600">
                                        <?= icon('user', 'size-5') ?>
                                    </span>
                                    <div>
                                        <p class="text-xs font-bold uppercase tracking-wider text-ink-400">Driver</p>
                                        <p class="font-semibold text-navy-700"><?= e($booking['driver_name']) ?></p>
                                    </div>
                                    <?php if ($booking['vehicle_reg']): ?>
                                        <span class="badge badge-muted ml-auto font-mono"><?= e($booking['vehicle_reg']) ?></span>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>

                            <div class="mt-4 flex items-baseline justify-between gap-4 border-t border-ink-200 pt-4">
                                <span class="font-display font-bold text-navy-700">
                                    <?= $booking['quoted_price'] !== null ? 'Agreed price' : 'Estimate' ?>
                                </span>
                                <span class="price font-display text-xl font-extrabold text-navy-700">
                                    <?= money($booking['quoted_price'] ?? $booking['estimated_price']) ?>
                                </span>
                            </div>
                        </div>
                    </div>

                <?php elseif (!$looked): ?>
                    <div class="card card-pad">
                        <h2 class="text-lg">What the statuses mean</h2>
                        <ol class="mt-6 space-y-5">
                            <?php foreach ([
                                ['check-circle', 'Confirmed', 'Stock is reserved and your order is locked in.'],
                                ['package', 'Processing', 'Being picked, weighed and packed at the depot.'],
                                ['truck', 'Dispatched', 'Loaded onto the vehicle assigned to your route.'],
                                ['route', 'In transit', 'On the road. Our team can give you an ETA on request.'],
                                ['home', 'Delivered', 'Signed for at your location.'],
                            ] as [$ico, $heading, $copy]): ?>
                                <li class="flex gap-4">
                                    <span class="grid size-10 shrink-0 place-items-center rounded-xl bg-navy-50 text-navy-600">
                                        <?= icon($ico, 'size-5') ?>
                                    </span>
                                    <span>
                                        <span class="block font-display font-bold text-navy-700"><?= $heading ?></span>
                                        <span class="block text-sm text-ink-500"><?= $copy ?></span>
                                    </span>
                                </li>
                            <?php endforeach; ?>
                        </ol>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<?php partial('footer'); ?>
