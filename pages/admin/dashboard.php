<?php
$stats     = Order::stats();
$series    = Order::dailyTotals(14);
$breakdown = Order::statusBreakdown();
$recent    = Order::recent(7);
$topItems  = Order::topProducts(5);
$lowStock  = Product::lowStock(60, 6);
$bookings  = Booking::recent(5);
$bookStats = Booking::stats();
$peak      = max(1, max(array_column($series, 'value')));
$totalOrders = max(1, array_sum($breakdown));

partial('admin_header', [
    'title'    => 'Dashboard',
    'subtitle' => date('l, j F Y'),
    'actions'  => '<a class="btn btn-ghost btn-sm" href="' . url('/admin/orders') . '">All orders</a>'
                . '<a class="btn btn-primary btn-sm" href="' . url('/admin/products/new') . '">Add product</a>',
]);
?>

<div class="stat-grid mb-3" style="grid-template-columns:repeat(5,minmax(0,1fr))">
    <div class="stat">
        <div class="k">Orders today</div>
        <div class="v"><?= $stats['orders_today'] ?></div>
        <div class="m"><?= $stats['orders_total'] ?> all time</div>
    </div>
    <div class="stat">
        <div class="k">Open orders</div>
        <div class="v"><?= $stats['open_orders'] ?></div>
        <div class="m"><?= $stats['delivered'] ?> delivered so far</div>
    </div>
    <div class="stat">
        <div class="k">Quotes waiting</div>
        <div class="v"><?= $stats['pending_quotes'] ?></div>
        <div class="m"><a href="<?= url('/admin/orders') ?>?type=quote">Price them</a></div>
    </div>
    <div class="stat">
        <div class="k">Logistics to price</div>
        <div class="v"><?= $bookStats['awaiting'] ?></div>
        <div class="m"><a href="<?= url('/admin/logistics') ?>"><?= $bookStats['open'] ?> open jobs</a></div>
    </div>
    <div class="stat accent">
        <div class="k">Revenue this month</div>
        <div class="v"><?= money_short($stats['revenue_month']) ?></div>
        <div class="m"><?= money_short($stats['unpaid_value']) ?> still unpaid</div>
    </div>
</div>

<div class="split mb-3">
    <div class="card">
        <div class="card-head">
            <h3>Order value, last 14 days</h3>
            <span class="badge badge-muted"><?= money_short(array_sum(array_column($series, 'value'))) ?> total</span>
        </div>
        <div class="card-body">
            <div class="chart">
                <?php foreach ($series as $day): ?>
                    <div class="bar" title="<?= e(date('D j M', strtotime($day['day']))) ?>: <?= $day['orders'] ?> order(s), <?= money($day['value']) ?>">
                        <span class="fill" style="height:<?= max(2, round($day['value'] / $peak * 100)) ?>%"></span>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="chart-labels">
                <?php foreach ($series as $i => $day): ?>
                    <span><?= $i % 2 === 0 ? e(date('j', strtotime($day['day']))) : '' ?></span>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-head"><h3>Pipeline</h3></div>
        <div class="card-body">
            <?php foreach ($breakdown as $status => $count): ?>
                <?php if ($count === 0 && in_array($status, ['quoted', 'cancelled'], true)) continue; ?>
                <div class="mb-2">
                    <div class="flex-between small mb-1">
                        <span><?= e(status_label($status)) ?></span>
                        <span class="strong"><?= $count ?></span>
                    </div>
                    <div class="meter"><i style="width:<?= round($count / $totalOrders * 100) ?>%"></i></div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<div class="split">
    <div class="card">
        <div class="card-head">
            <h3>Latest orders</h3>
            <a class="btn btn-ghost btn-sm" href="<?= url('/admin/orders') ?>">View all</a>
        </div>
        <div class="table-wrap">
            <table class="table">
                <thead><tr><th>Reference</th><th>Customer</th><th>Status</th><th class="num">Total</th><th class="tight"></th></tr></thead>
                <tbody>
                <?php if (!$recent): ?>
                    <tr><td colspan="5" class="center muted">No orders yet.</td></tr>
                <?php endif; ?>
                <?php foreach ($recent as $order): ?>
                    <tr>
                        <td>
                            <a class="cell-title mono" href="<?= url('/admin/orders/' . $order['id']) ?>"><?= e($order['reference']) ?></a>
                            <div class="cell-sub"><?= e(time_ago($order['created_at'])) ?></div>
                        </td>
                        <td>
                            <div class="cell-title"><?= e($order['customer_name']) ?></div>
                            <div class="cell-sub"><?= e($order['city']) ?>, <?= e($order['state']) ?></div>
                        </td>
                        <td><span class="badge badge-<?= status_tone($order['status']) ?>"><?= e(status_label($order['status'])) ?></span></td>
                        <td class="num strong"><?= money($order['total']) ?></td>
                        <td class="tight"><a class="btn btn-ghost btn-sm" href="<?= url('/admin/orders/' . $order['id']) ?>">Open</a></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div>
        <div class="card mb-3">
            <div class="card-head"><h3>Best sellers</h3></div>
            <div class="table-wrap">
                <table class="table">
                    <tbody>
                    <?php if (!$topItems): ?>
                        <tr><td class="center muted">Nothing sold yet.</td></tr>
                    <?php endif; ?>
                    <?php foreach ($topItems as $item): ?>
                        <tr>
                            <td>
                                <div class="cell-title"><?= e(excerpt($item['product_name'], 30)) ?></div>
                                <div class="cell-sub"><?= (int) $item['qty'] ?> units moved</div>
                            </td>
                            <td class="num strong nowrap"><?= money_short($item['value']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-head">
                <h3>Latest logistics bookings</h3>
                <a class="btn btn-ghost btn-sm" href="<?= url('/admin/logistics') ?>">View all</a>
            </div>
            <div class="table-wrap">
                <table class="table">
                    <tbody>
                    <?php if (!$bookings): ?>
                        <tr><td class="center muted">No bookings yet.</td></tr>
                    <?php endif; ?>
                    <?php foreach ($bookings as $booking): ?>
                        <tr>
                            <td>
                                <a class="cell-title mono" href="<?= url('/admin/logistics/' . $booking['id']) ?>"><?= e($booking['reference']) ?></a>
                                <div class="cell-sub"><?= e($booking['pickup_city']) ?> &rarr; <?= e($booking['destination_city']) ?> &middot; <?= e($booking['vehicle_type']) ?></div>
                            </td>
                            <td class="num">
                                <span class="badge badge-<?= status_tone($booking['status']) ?>">
                                    <?= e(Booking::statuses()[$booking['status']] ?? $booking['status']) ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card">
            <div class="card-head">
                <h3>Low stock</h3>
                <a class="btn btn-ghost btn-sm" href="<?= url('/admin/products') ?>?sort=stock">Restock</a>
            </div>
            <div class="table-wrap">
                <table class="table">
                    <tbody>
                    <?php if (!$lowStock): ?>
                        <tr><td class="center muted">Everything is comfortably stocked.</td></tr>
                    <?php endif; ?>
                    <?php foreach ($lowStock as $product): ?>
                        <tr>
                            <td>
                                <a class="cell-title" href="<?= url('/admin/products/' . $product['id'] . '/edit') ?>"><?= e(excerpt($product['name'], 30)) ?></a>
                                <div class="cell-sub">per <?= e($product['unit']) ?></div>
                            </td>
                            <td class="num">
                                <span class="badge badge-<?= (int) $product['stock_qty'] === 0 ? 'danger' : 'warn' ?>">
                                    <?= (int) $product['stock_qty'] ?> left
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php partial('admin_footer'); ?>
