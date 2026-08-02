<?php
/** @var array $params */
$order = Order::findByReference($params['reference'] ?? '');

/** Only the buyer, the session that placed it, or staff may open this page. */
$placedHere = in_array($order['reference'] ?? '', $_SESSION['_recent_orders'] ?? [], true);
$owned      = $order && auth_id() && (int) $order['user_id'] === auth_id();

if (!$order || !($placedHere || $owned || is_staff())) {
    abort_404();
}

$items    = Order::items((int) $order['id']);
$isQuote  = $order['type'] === 'quote';
$bankName = Setting::get('bank_name');

partial('header', ['title' => page_title('Order ' . $order['reference'])]);
?>

<section class="section-sm pb-20">
    <div class="shell-tight">

        <!-- Receipt header -->
        <div class="card overflow-hidden">
            <div class="relative isolate bg-navy-800 px-6 py-10 text-center sm:px-10">
                <div class="absolute inset-0 bg-grid opacity-30"></div>
                <div class="absolute -right-20 -top-20 size-56 rounded-full bg-orange-500/25 blur-3xl"></div>

                <div class="relative">
                    <span class="mx-auto grid size-16 place-items-center rounded-2xl bg-emerald-500 text-white shadow-lift">
                        <?= icon('check', 'size-8') ?>
                    </span>
                    <p class="mt-5 badge badge-success">
                        <?= $isQuote ? 'Quote request received' : 'Order received' ?>
                    </p>
                    <h1 class="mt-4 font-display text-3xl font-extrabold text-white">
                        Thank you, <?= e(explode(' ', $order['customer_name'])[0]) ?>.
                    </h1>
                    <p class="mx-auto mt-3 max-w-lg leading-relaxed text-navy-100">
                        <?= $isQuote
                            ? 'Our sales desk is pricing your list now. Expect a response within one business day.'
                            : 'We have your order and are confirming stock. You will get a call or email shortly.' ?>
                    </p>

                    <button class="mt-6 inline-flex cursor-pointer items-center gap-2 rounded-xl border border-white/25 bg-white/10 px-5 py-3 font-mono text-lg font-bold text-white backdrop-blur transition-colors hover:bg-white/20"
                            data-copy="<?= e($order['reference']) ?>">
                        <?= icon('copy', 'size-4') ?><span data-copy-label><?= e($order['reference']) ?></span>
                    </button>
                    <p class="mt-2 text-xs text-navy-200">Keep this reference to track your delivery</p>
                </div>
            </div>

            <div class="flex flex-wrap items-center justify-center gap-3 border-b border-ink-100 bg-ink-50 px-6 py-4">
                <a class="btn btn-primary gap-2" href="<?= url('/track') ?>?reference=<?= e($order['reference']) ?>">
                    <?= icon('route', 'size-4') ?>Track this order
                </a>
                <a class="btn btn-ghost" href="<?= url('/products') ?>">Keep shopping</a>
                <button class="btn btn-ghost gap-2 no-print" onclick="window.print()">
                    <?= icon('printer', 'size-4') ?>Print
                </button>
            </div>

            <!-- Items -->
            <div class="p-6 sm:p-8">
                <div class="mb-4 flex items-center justify-between gap-4">
                    <h2 class="text-lg">What you ordered</h2>
                    <span class="badge badge-<?= status_tone($order['status']) === 'brand' ? 'navy' : status_tone($order['status']) ?>">
                        <?= e(status_label($order['status'])) ?>
                    </span>
                </div>

                <div class="table-wrap rounded-xl border border-ink-200">
                    <table class="table">
                        <thead>
                            <tr><th>Item</th><th class="num">Qty</th><th class="num">Unit</th><th class="num">Total</th></tr>
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

                <dl class="ml-auto mt-5 max-w-xs space-y-2.5 text-sm">
                    <div class="flex justify-between gap-4">
                        <dt class="text-ink-500">Subtotal</dt>
                        <dd class="price font-semibold text-navy-700"><?= money($order['subtotal']) ?></dd>
                    </div>
                    <div class="flex justify-between gap-4">
                        <dt class="text-ink-500">Delivery</dt>
                        <dd class="font-semibold text-navy-700">
                            <?= $order['delivery_fee'] > 0 ? money($order['delivery_fee']) : ($isQuote ? 'To quote' : 'Free') ?>
                        </dd>
                    </div>
                    <div class="flex items-baseline justify-between gap-4 border-t border-ink-200 pt-3">
                        <dt class="font-display font-bold text-navy-700">Total</dt>
                        <dd class="price font-display text-xl font-extrabold text-navy-700"><?= money($order['total']) ?></dd>
                    </div>
                </dl>
            </div>
        </div>

        <!-- Details -->
        <div class="mt-6 grid gap-6 sm:grid-cols-2">
            <div class="card card-pad">
                <h2 class="flex items-center gap-2 text-base">
                    <?= icon('map-pin', 'size-5 text-orange-500') ?>Delivering to
                </h2>
                <address class="mt-3 not-italic text-sm leading-relaxed text-ink-500">
                    <span class="font-semibold text-navy-700"><?= e($order['customer_name']) ?></span><br>
                    <?php if ($order['company']): ?><?= e($order['company']) ?><br><?php endif; ?>
                    <?= e($order['delivery_address']) ?><br>
                    <?= e($order['city']) ?>, <?= e($order['state']) ?><br>
                    <a class="link-quiet" href="tel:<?= e($order['phone']) ?>"><?= e($order['phone']) ?></a><br>
                    <a class="link-quiet" href="mailto:<?= e($order['email']) ?>"><?= e($order['email']) ?></a>
                </address>
            </div>

            <div class="card card-pad">
                <h2 class="flex items-center gap-2 text-base">
                    <?= icon('calendar', 'size-5 text-orange-500') ?>Schedule
                </h2>
                <dl class="mt-3 space-y-2 text-sm">
                    <?php foreach ([
                        'Date'    => date_human($order['delivery_date']),
                        'Window'  => $order['delivery_window'] ?: 'Any time',
                        'Service' => $order['logistics_service'] ?: 'Standard delivery',
                        'Payment' => ucfirst($order['payment_method']),
                    ] as $term => $val): ?>
                        <div class="flex justify-between gap-4">
                            <dt class="text-ink-500"><?= $term ?></dt>
                            <dd class="text-right font-semibold text-navy-700"><?= e($val) ?></dd>
                        </div>
                    <?php endforeach; ?>
                </dl>
            </div>
        </div>

        <?php if (!$isQuote && $order['payment_method'] === 'transfer' && $bankName): ?>
            <div class="card card-pad mt-6">
                <h2 class="flex items-center gap-2 text-base">
                    <?= icon('banknote', 'size-5 text-orange-500') ?>Payment details
                </h2>
                <p class="mt-2 text-sm text-ink-500">Transfer the invoice total using your reference as the narration.</p>
                <dl class="mt-4 divide-y divide-ink-100 border-y border-ink-100 text-sm">
                    <?php foreach ([
                        'Bank'           => e($bankName),
                        'Account name'   => e(Setting::get('bank_account_name', APP_NAME)),
                        'Account number' => '<span class="font-mono">' . e(Setting::get('bank_account_no', '')) . '</span>',
                        'Narration'      => '<span class="font-mono">' . e($order['reference']) . '</span>',
                    ] as $term => $val): ?>
                        <div class="flex justify-between gap-4 py-3">
                            <dt class="text-ink-500"><?= $term ?></dt>
                            <dd class="font-semibold text-navy-700"><?= $val ?></dd>
                        </div>
                    <?php endforeach; ?>
                </dl>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php partial('footer'); ?>
