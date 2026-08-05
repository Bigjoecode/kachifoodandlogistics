<?php
/** @var array $params */
$order = Order::find((int) ($params['id'] ?? 0));
if (!$order) {
    abort_404();
}
$orderId = (int) $order['id'];

if (is_post()) {
    $action = (string) input('action');
    $actor  = auth_user()['name'];

    switch ($action) {
        case 'status':
            $status = (string) input('status');
            if (!array_key_exists($status, order_statuses())) {
                flash('error', 'That is not a valid status.');
                break;
            }
            Order::updateStatus($orderId, $status, (string) input('note'), (string) input('location'), $actor);
            flash('success', 'Status set to ' . status_label($status) . '.');
            break;

        case 'payment':
            $paymentStatus = (string) input('payment_status');
            if (!in_array($paymentStatus, ['unpaid', 'part_paid', 'paid'], true)) {
                flash('error', 'That is not a valid payment status.');
                break;
            }
            Order::updatePayment($orderId, $paymentStatus);
            Order::addEvent($orderId, $order['status'], 'Payment marked ' . str_replace('_', ' ', $paymentStatus) . '.', null, $actor);
            flash('success', 'Payment updated.');
            break;

        case 'reprice':
            $prices = array_map('floatval', $_POST['price'] ?? []);
            Order::repriceItems($orderId, $prices, input_float('delivery_fee', 0));
            if ($order['type'] === 'quote' && in_array($order['status'], ['pending', 'quoted'], true)) {
                Order::updateStatus($orderId, 'quoted', 'Quote priced and sent to the customer.', null, $actor);
            }
            flash('success', 'Pricing updated.');
            break;

        case 'note':
            $note = trim((string) input('note'));
            if ($note !== '') {
                Order::addEvent($orderId, $order['status'], $note, (string) input('location'), $actor);
                flash('success', 'Note added to the timeline.');
            }
            break;

        case 'delete':
            if (!is_admin()) {
                flash('error', 'Only administrators can delete orders.');
                break;
            }
            Order::delete($orderId);
            flash('success', 'Order ' . $order['reference'] . ' deleted.');
            redirect('/admin/orders');
    }

    redirect('/admin/orders/' . $orderId);
}

$items  = Order::items($orderId);
$events = Order::events($orderId);

partial('admin_header', [
    'title'    => $order['reference'],
    'subtitle' => ($order['type'] === 'quote' ? 'Quote request' : 'Order') . ' placed ' . date_human($order['created_at'], true),
    'actions'  => '<a class="btn btn-ghost btn-sm" href="' . url('/admin/orders') . '">Back to orders</a>'
                . '<button class="btn btn-ghost btn-sm no-print" onclick="window.print()">Print</button>',
]);
?>

<div class="split">
    <div>
        <div class="card mb-3">
            <div class="card-head">
                <h3>Items</h3>
                <div class="flex gap-sm">
                    <span class="badge badge-<?= status_tone($order['status']) ?>"><?= e(status_label($order['status'])) ?></span>
                    <span class="badge badge-<?= $order['payment_status'] === 'paid' ? 'success' : 'muted' ?>">
                        <?= e(str_replace('_', ' ', ucfirst($order['payment_status']))) ?>
                    </span>
                </div>
            </div>

            <form method="post">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="reprice">

                <div class="table-wrap">
                    <table class="table">
                        <thead><tr><th>Item</th><th class="num">Qty</th><th class="nowrap">Unit price</th><th class="num">Line total</th></tr></thead>
                        <tbody>
                        <?php foreach ($items as $item): ?>
                            <tr>
                                <td>
                                    <div class="cell-title"><?= e($item['product_name']) ?></div>
                                    <div class="cell-sub">per <?= e($item['unit']) ?></div>
                                </td>
                                <td class="num"><?= (int) $item['quantity'] ?></td>
                                <td>
                                    <input class="input" type="number" step="0.01" min="0"
                                           name="price[<?= (int) $item['id'] ?>]" value="<?= e(number_format((float) $item['unit_price'], 2, '.', '')) ?>">
                                </td>
                                <td class="num strong"><?= money($item['line_total']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div class="card-foot">
                    <div class="flex-between flex-wrap">
                        <div class="field mb-0" style="max-width:min(200px, 100%)">
                            <label for="delivery_fee">Delivery fee</label>
                            <input class="input" type="number" step="0.01" min="0" id="delivery_fee"
                                   name="delivery_fee" value="<?= e(number_format((float) $order['delivery_fee'], 2, '.', '')) ?>">
                        </div>
                        <div style="flex:1 1 220px;min-width:0">
                            <div class="summary-row"><span class="label">Subtotal</span><span><?= money($order['subtotal']) ?></span></div>
                            <div class="summary-row"><span class="label">Delivery</span><span><?= money($order['delivery_fee']) ?></span></div>
                            <div class="summary-total"><span>Total</span><span><?= money($order['total']) ?></span></div>
                        </div>
                    </div>
                    <button class="btn btn-primary mt-2" type="submit">
                        <?= $order['type'] === 'quote' ? 'Save pricing and mark as quoted' : 'Save pricing' ?>
                    </button>
                </div>
            </form>
        </div>

        <div class="card">
            <div class="card-head"><h3>Timeline</h3></div>
            <div class="card-body">
                <?php partial('order_timeline', ['order' => $order, 'events' => $events]); ?>

                <hr>
                <form method="post" class="no-print">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="note">
                    <div class="field-row">
                        <div class="field">
                            <label for="note">Add a note to the timeline</label>
                            <input class="input" id="note" name="note" placeholder="Customer called to confirm the gate code" required>
                        </div>
                        <div class="field">
                            <label for="note_location">Location (optional)</label>
                            <input class="input" id="note_location" name="location" placeholder="Amuwo-Odofin">
                        </div>
                    </div>
                    <button class="btn btn-ghost btn-sm" type="submit">Add note</button>
                </form>
            </div>
        </div>
    </div>

    <aside>
        <div class="card card-pad mb-3 no-print">
            <h4>Move this order on</h4>
            <form method="post">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="status">
                <div class="field">
                    <label for="status">New status</label>
                    <select class="select" id="status" name="status">
                        <?php foreach (order_statuses() as $key => $label): ?>
                            <option value="<?= $key ?>" <?= $order['status'] === $key ? 'selected' : '' ?>><?= $label ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="field">
                    <label for="status_note">Note for the customer</label>
                    <input class="input" id="status_note" name="note" placeholder="Loaded on truck KFL-04">
                </div>
                <div class="field">
                    <label for="status_location">Location</label>
                    <input class="input" id="status_location" name="location" placeholder="Berger">
                </div>
                <button class="btn btn-primary btn-block" type="submit">Update status</button>
            </form>

            <hr>

            <form method="post">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="payment">
                <div class="field">
                    <label for="payment_status">Payment status</label>
                    <select class="select" id="payment_status" name="payment_status">
                        <?php foreach (['unpaid' => 'Unpaid', 'part_paid' => 'Part paid', 'paid' => 'Paid'] as $key => $label): ?>
                            <option value="<?= $key ?>" <?= $order['payment_status'] === $key ? 'selected' : '' ?>><?= $label ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button class="btn btn-ghost btn-block" type="submit">Save payment status</button>
            </form>
        </div>

        <div class="card card-pad mb-3">
            <h4>Customer</h4>
            <p class="small mb-2">
                <span class="strong"><?= e($order['customer_name']) ?></span><br>
                <?php if ($order['company']): ?><?= e($order['company']) ?><br><?php endif; ?>
                <a href="mailto:<?= e($order['email']) ?>"><?= e($order['email']) ?></a><br>
                <a href="tel:<?= e($order['phone']) ?>"><?= e($order['phone']) ?></a>
            </p>
            <?php if ($order['user_id']): ?>
                <a class="btn btn-ghost btn-sm btn-block" href="<?= url('/admin/customers') ?>?q=<?= e($order['email']) ?>">View customer record</a>
            <?php else: ?>
                <span class="badge badge-muted">Guest checkout</span>
            <?php endif; ?>
        </div>

        <div class="card card-pad mb-3">
            <h4>Delivery</h4>
            <dl class="spec-list" style="margin:0">
                <div><dt>Address</dt><dd><?= e($order['delivery_address']) ?></dd></div>
                <div><dt>City</dt><dd><?= e($order['city']) ?>, <?= e($order['state']) ?></dd></div>
                <div><dt>Date</dt><dd><?= e(date_human($order['delivery_date'])) ?></dd></div>
                <div><dt>Window</dt><dd><?= e($order['delivery_window'] ?: 'Any') ?></dd></div>
                <div><dt>Service</dt><dd><?= e($order['logistics_service'] ?: 'Standard') ?></dd></div>
                <div><dt>Payment</dt><dd><?= e(ucfirst($order['payment_method'])) ?></dd></div>
            </dl>
            <?php if ($order['notes']): ?>
                <p class="small muted mt-2 mb-0"><span class="strong">Customer notes:</span><br><?= nl2br(e($order['notes'])) ?></p>
            <?php endif; ?>
        </div>

        <?php if (is_admin()): ?>
            <div class="card card-pad no-print">
                <h4>Danger zone</h4>
                <p class="small muted">Deleting removes the order, its items and its timeline for good.</p>
                <form method="post" data-confirm="Permanently delete <?= e($order['reference']) ?>?">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="delete">
                    <button class="btn btn-danger btn-block btn-sm" type="submit">Delete order</button>
                </form>
            </div>
        <?php endif; ?>
    </aside>
</div>

<?php partial('admin_footer'); ?>
