<?php
/** @var array $params */
$user  = auth_user();
$order = Order::findByReference($params['reference'] ?? '');

if (!$order || (int) $order['user_id'] !== (int) $user['id']) {
    abort_404();
}

$items  = Order::items((int) $order['id']);
$events = Order::events((int) $order['id']);
$tone   = status_tone($order['status']) === 'brand' ? 'navy' : status_tone($order['status']);

partial('header', ['title' => page_title('Order ' . $order['reference'])]);
?>

<section class="section-sm pb-20">
    <div class="shell">

        <nav class="mb-6 flex items-center gap-1.5 text-xs text-ink-400" aria-label="Breadcrumb">
            <a class="inline-flex min-h-6 items-center transition-colors hover:text-navy-700" href="<?= url('/account/orders') ?>">My orders</a>
            <?= icon('chevron-right', 'size-3') ?>
            <span class="font-semibold text-navy-700"><?= e($order['reference']) ?></span>
        </nav>

        <div class="mb-8 flex flex-wrap items-start justify-between gap-4">
            <div>
                <h1 class="font-mono text-2xl font-bold text-navy-700 sm:text-3xl"><?= e($order['reference']) ?></h1>
                <p class="mt-2 text-ink-500">
                    Placed <?= e(date_human($order['created_at'], true)) ?>
                    &middot; <?= count($items) ?> item<?= count($items) === 1 ? '' : 's' ?>
                </p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <span class="badge badge-<?= $tone ?>"><?= e(status_label($order['status'])) ?></span>
                <span class="badge badge-muted"><?= e(str_replace('_', ' ', ucfirst($order['payment_status']))) ?></span>
                <button class="btn btn-ghost btn-sm gap-2 no-print" onclick="window.print()">
                    <?= icon('printer', 'size-4') ?>Print
                </button>
            </div>
        </div>

        <div class="grid gap-8 lg:grid-cols-[minmax(0,1fr)_20rem] lg:items-start">

            <div class="space-y-6">
                <div class="card overflow-hidden">
                    <div class="border-b border-ink-100 px-6 py-4"><h2 class="text-base">Items</h2></div>
                    <div class="table-wrap">
                        <table class="table">
                            <thead>
                                <tr><th>Item</th><th class="num">Qty</th><th class="num">Unit price</th><th class="num">Total</th></tr>
                            </thead>
                            <tbody>
                            <?php foreach ($items as $item): ?>
                                <tr>
                                    <td>
                                        <span class="cell-title"><?= e($item['product_name']) ?></span>
                                        <span class="cell-sub block">per <?= e($item['unit']) ?></span>
                                    </td>
                                    <td class="num"><?= (int) $item['quantity'] ?></td>
                                    <td class="num"><?= $item['unit_price'] > 0 ? money($item['unit_price']) : '&mdash;' ?></td>
                                    <td class="num font-semibold text-navy-700">
                                        <?= $item['line_total'] > 0 ? money($item['line_total']) : 'To quote' ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="border-t border-ink-100 bg-ink-50 px-6 py-5">
                        <dl class="ml-auto max-w-xs space-y-2.5 text-sm">
                            <div class="flex justify-between gap-4">
                                <dt class="text-ink-500">Subtotal</dt>
                                <dd class="price font-semibold text-navy-700"><?= money($order['subtotal']) ?></dd>
                            </div>
                            <div class="flex justify-between gap-4">
                                <dt class="text-ink-500">Delivery</dt>
                                <dd class="font-semibold text-navy-700">
                                    <?= $order['delivery_fee'] > 0 ? money($order['delivery_fee']) : 'Free' ?>
                                </dd>
                            </div>
                            <div class="flex items-baseline justify-between gap-4 border-t border-ink-200 pt-3">
                                <dt class="font-display font-bold text-navy-700">Total</dt>
                                <dd class="price font-display text-xl font-extrabold text-navy-700"><?= money($order['total']) ?></dd>
                            </div>
                        </dl>
                    </div>
                </div>

                <div class="card card-pad">
                    <h2 class="text-lg">Delivery timeline</h2>
                    <div class="mt-6">
                        <?php partial('timeline', [
                            'events'     => $events,
                            'status'     => $order['status'],
                            'milestones' => tracking_milestones(),
                            'labels'     => order_statuses(),
                        ]); ?>
                    </div>
                </div>
            </div>

            <aside class="space-y-4 lg:sticky lg:top-28">
                <div class="card card-pad">
                    <h2 class="flex items-center gap-2 text-base">
                        <?= icon('map-pin', 'size-5 text-orange-500') ?>Delivering to
                    </h2>
                    <address class="mt-3 not-italic text-sm leading-relaxed text-ink-500">
                        <span class="font-semibold text-navy-700"><?= e($order['customer_name']) ?></span><br>
                        <?php if ($order['company']): ?><?= e($order['company']) ?><br><?php endif; ?>
                        <?= e($order['delivery_address']) ?><br>
                        <?= e($order['city']) ?>, <?= e($order['state']) ?><br>
                        <?= e($order['phone']) ?>
                    </address>
                </div>

                <div class="card card-pad">
                    <h2 class="flex items-center gap-2 text-base">
                        <?= icon('calendar', 'size-5 text-orange-500') ?>Schedule
                    </h2>
                    <dl class="mt-3 divide-y divide-ink-100 border-t border-ink-100 text-sm">
                        <?php foreach ([
                            'Date'    => date_human($order['delivery_date']),
                            'Window'  => $order['delivery_window'] ?: 'Any time',
                            'Service' => $order['logistics_service'] ?: 'Standard',
                            'Payment' => ucfirst($order['payment_method']),
                        ] as $term => $val): ?>
                            <div class="flex justify-between gap-4 py-3">
                                <dt class="text-ink-500"><?= $term ?></dt>
                                <dd class="text-right font-semibold text-navy-700"><?= e($val) ?></dd>
                            </div>
                        <?php endforeach; ?>
                    </dl>
                </div>

                <?php if ($order['notes']): ?>
                    <div class="card card-pad">
                        <h2 class="text-base">Your notes</h2>
                        <p class="mt-2 text-sm leading-relaxed text-ink-500"><?= nl2br(e($order['notes'])) ?></p>
                    </div>
                <?php endif; ?>

                <div class="card card-pad no-print">
                    <h2 class="text-base">Need a change?</h2>
                    <p class="mt-1.5 text-sm text-ink-500">
                        Deliveries can be rescheduled up to 24 hours before the slot.
                    </p>
                    <a class="btn btn-outline btn-block mt-4 gap-2" href="<?= url('/contact') ?>">
                        <?= icon('message', 'size-4') ?>Contact support
                    </a>
                </div>
            </aside>
        </div>
    </div>
</section>

<?php partial('footer'); ?>
