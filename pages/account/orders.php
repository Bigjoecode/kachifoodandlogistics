<?php
$user   = auth_user();
$orders = Order::forUser((int) $user['id']);

$spend = 0.0;
$open  = 0;
foreach ($orders as $order) {
    if (!in_array($order['status'], ['cancelled', 'pending', 'quoted'], true)) {
        $spend += (float) $order['total'];
    }
    if (!in_array($order['status'], ['delivered', 'cancelled'], true)) {
        $open++;
    }
}

$tone = fn(string $status) => status_tone($status) === 'brand' ? 'navy' : status_tone($status);

partial('header', ['title' => page_title('My orders')]);
partial('account_head', ['heading' => 'My orders', 'sub' => 'Every order and quote request placed on this account.']);
?>

<section class="section-sm pb-20">
    <div class="shell">
        <?php partial('account_nav'); ?>

        <div class="mb-8 grid gap-4 sm:grid-cols-3">
            <?php foreach ([
                ['receipt', 'Total orders', number_format(count($orders)), 'Across all time', false],
                ['clock', 'Open right now', number_format($open), 'Not yet delivered', false],
                ['banknote', 'Lifetime value', money_short($spend), 'Excluding cancelled', true],
            ] as [$ico, $label, $value, $meta, $accent]): ?>
                <div class="<?= $accent ? 'rounded-2xl bg-navy-700 p-6 text-white shadow-soft' : 'card p-6' ?>">
                    <div class="flex items-center gap-2">
                        <span class="<?= $accent ? 'text-orange-400' : 'text-orange-500' ?>"><?= icon($ico, 'size-4') ?></span>
                        <span class="text-xs font-bold uppercase tracking-wider <?= $accent ? 'text-navy-200' : 'text-ink-400' ?>"><?= $label ?></span>
                    </div>
                    <p class="price mt-2 font-display text-3xl font-extrabold <?= $accent ? 'text-white' : 'text-navy-700' ?>"><?= $value ?></p>
                    <p class="mt-1 text-xs <?= $accent ? 'text-navy-200' : 'text-ink-400' ?>"><?= $meta ?></p>
                </div>
            <?php endforeach; ?>
        </div>

        <?php if (!$orders): ?>
            <div class="card flex flex-col items-center px-6 py-16 text-center">
                <span class="grid size-16 place-items-center rounded-2xl bg-ink-100 text-ink-400">
                    <?= icon('receipt', 'size-8') ?>
                </span>
                <h2 class="mt-5 text-xl">No orders yet</h2>
                <p class="mt-2 max-w-sm text-ink-500">Once you place an order it will show up here with a live status.</p>
                <a class="btn btn-primary mt-6 gap-2" href="<?= url('/products') ?>">
                    <?= icon('package', 'size-5') ?>Browse the catalogue
                </a>
            </div>
        <?php else: ?>
            <div class="card overflow-hidden">
                <div class="table-wrap">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Reference</th><th>Placed</th><th>Type</th><th class="num">Items</th>
                                <th>Status</th><th>Payment</th><th class="num">Total</th><th></th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($orders as $order): ?>
                            <tr>
                                <td>
                                    <a class="cell-title font-mono transition-colors hover:text-orange-600"
                                       href="<?= url('/account/orders/' . $order['reference']) ?>"><?= e($order['reference']) ?></a>
                                    <span class="cell-sub block"><?= e($order['city']) ?>, <?= e($order['state']) ?></span>
                                </td>
                                <td class="whitespace-nowrap">
                                    <span class="text-sm text-ink-600"><?= e(date_human($order['created_at'])) ?></span>
                                    <span class="cell-sub block"><?= e(time_ago($order['created_at'])) ?></span>
                                </td>
                                <td>
                                    <span class="badge <?= $order['type'] === 'quote' ? 'badge-orange' : 'badge-muted' ?>">
                                        <?= e(ucfirst($order['type'])) ?>
                                    </span>
                                </td>
                                <td class="num text-ink-600"><?= (int) $order['item_count'] ?></td>
                                <td><span class="badge badge-<?= $tone($order['status']) ?>"><?= e(status_label($order['status'])) ?></span></td>
                                <td class="text-sm text-ink-500"><?= e(str_replace('_', ' ', ucfirst($order['payment_status']))) ?></td>
                                <td class="num font-semibold text-navy-700"><?= money($order['total']) ?></td>
                                <td class="text-right">
                                    <a class="btn btn-ghost btn-sm" href="<?= url('/account/orders/' . $order['reference']) ?>">View</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php partial('footer'); ?>
