<?php
$filters = [
    'status'         => (string) input('status', ''),
    'type'           => (string) input('type', ''),
    'payment_status' => (string) input('payment_status', ''),
    'q'              => (string) input('q', ''),
    'from'           => (string) input('from', ''),
    'to'             => (string) input('to', ''),
    'page'           => input_int('page', 1),
];
$result = Order::paginate($filters);

partial('admin_header', [
    'title'    => $filters['type'] === 'quote' ? 'Quote requests' : 'Orders',
    'subtitle' => number_format($result['total']) . ' record' . ($result['total'] === 1 ? '' : 's') . ' matching the current filters',
]);
?>

<nav class="tabs">
    <a href="<?= url('/admin/orders') ?>" class="<?= $filters['type'] === '' && $filters['status'] === '' ? 'is-active' : '' ?>">All</a>
    <a href="<?= url('/admin/orders') ?>?status=pending" class="<?= $filters['status'] === 'pending' ? 'is-active' : '' ?>">Pending</a>
    <a href="<?= url('/admin/orders') ?>?status=processing" class="<?= $filters['status'] === 'processing' ? 'is-active' : '' ?>">Processing</a>
    <a href="<?= url('/admin/orders') ?>?status=in_transit" class="<?= $filters['status'] === 'in_transit' ? 'is-active' : '' ?>">In transit</a>
    <a href="<?= url('/admin/orders') ?>?status=delivered" class="<?= $filters['status'] === 'delivered' ? 'is-active' : '' ?>">Delivered</a>
    <a href="<?= url('/admin/orders') ?>?type=quote" class="<?= $filters['type'] === 'quote' ? 'is-active' : '' ?>">Quotes</a>
</nav>

<form class="data-filters" method="get">
    <div class="field">
        <label for="q">Search</label>
        <input class="input" id="q" name="q" value="<?= e($filters['q']) ?>" placeholder="Reference, name, phone">
    </div>
    <div class="field">
        <label for="status">Status</label>
        <select class="select" id="status" name="status">
            <option value="">Any status</option>
            <?php foreach (order_statuses() as $key => $label): ?>
                <option value="<?= $key ?>" <?= $filters['status'] === $key ? 'selected' : '' ?>><?= $label ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="field">
        <label for="payment_status">Payment</label>
        <select class="select" id="payment_status" name="payment_status">
            <option value="">Any</option>
            <?php foreach (['unpaid' => 'Unpaid', 'part_paid' => 'Part paid', 'paid' => 'Paid'] as $key => $label): ?>
                <option value="<?= $key ?>" <?= $filters['payment_status'] === $key ? 'selected' : '' ?>><?= $label ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="field">
        <label for="type">Type</label>
        <select class="select" id="type" name="type">
            <option value="">Orders and quotes</option>
            <option value="order" <?= $filters['type'] === 'order' ? 'selected' : '' ?>>Orders</option>
            <option value="quote" <?= $filters['type'] === 'quote' ? 'selected' : '' ?>>Quotes</option>
        </select>
    </div>
    <div class="field">
        <label for="from">From</label>
        <input class="input" type="date" id="from" name="from" value="<?= e($filters['from']) ?>">
    </div>
    <div class="field">
        <label for="to">To</label>
        <input class="input" type="date" id="to" name="to" value="<?= e($filters['to']) ?>">
    </div>
    <div class="flex gap-sm">
        <button class="btn btn-primary" type="submit">Filter</button>
        <a class="btn btn-ghost" href="<?= url('/admin/orders') ?>">Reset</a>
    </div>
</form>

<?php if (!$result['rows']): ?>
    <div class="empty">
        <div class="mark">0</div>
        <h3>No orders match those filters</h3>
        <p>Try widening the date range or clearing the search.</p>
        <a class="btn btn-primary" href="<?= url('/admin/orders') ?>">Clear filters</a>
    </div>
<?php else: ?>
    <div class="card table-flush">
        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>Reference</th><th>Customer</th><th>Delivery</th><th>Items</th>
                        <th>Status</th><th>Payment</th><th class="num">Total</th><th class="tight"></th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($result['rows'] as $order): ?>
                    <tr>
                        <td>
                            <a class="cell-title mono" href="<?= url('/admin/orders/' . $order['id']) ?>"><?= e($order['reference']) ?></a>
                            <div class="cell-sub">
                                <?= e(date_human($order['created_at'])) ?>
                                <?php if ($order['type'] === 'quote'): ?> &middot; <span class="badge badge-accent">Quote</span><?php endif; ?>
                            </div>
                        </td>
                        <td>
                            <div class="cell-title"><?= e($order['customer_name']) ?></div>
                            <div class="cell-sub"><?= e($order['company'] ?: $order['phone']) ?></div>
                        </td>
                        <td>
                            <div class="small"><?= e($order['city']) ?>, <?= e($order['state']) ?></div>
                            <div class="cell-sub"><?= e(date_human($order['delivery_date'])) ?></div>
                        </td>
                        <td><?= (int) $order['item_count'] ?></td>
                        <td><span class="badge badge-<?= status_tone($order['status']) ?>"><?= e(status_label($order['status'])) ?></span></td>
                        <td>
                            <span class="badge badge-<?= $order['payment_status'] === 'paid' ? 'success' : ($order['payment_status'] === 'part_paid' ? 'warn' : 'muted') ?>">
                                <?= e(str_replace('_', ' ', ucfirst($order['payment_status']))) ?>
                            </span>
                        </td>
                        <td class="num strong"><?= money($order['total']) ?></td>
                        <td class="tight"><a class="btn btn-ghost btn-sm" href="<?= url('/admin/orders/' . $order['id']) ?>">Open</a></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php partial('pagination', ['page' => $result['page'], 'pages' => $result['pages']]); ?>
<?php endif; ?>

<?php partial('admin_footer'); ?>
