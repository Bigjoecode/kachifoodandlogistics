<?php
/** @var array $params */
$booking = Booking::find((int) ($params['id'] ?? 0));
if (!$booking) {
    abort_404();
}
$bookingId = (int) $booking['id'];

if (is_post()) {
    $actor = auth_user()['name'];

    switch ((string) input('action')) {
        case 'status':
            $status = (string) input('status');
            if (!array_key_exists($status, Booking::statuses())) {
                flash('error', 'That is not a valid status.');
                break;
            }
            Booking::updateStatus($bookingId, $status, (string) input('note'), (string) input('location'), $actor);
            flash('success', 'Status set to ' . (Booking::statuses()[$status]) . '.');
            break;

        case 'quote':
            $price = input_float('quoted_price', 0);
            if ($price <= 0) {
                flash('error', 'Enter a price above zero.');
                break;
            }
            Booking::setQuote($bookingId, $price);
            if (in_array($booking['status'], ['pending', 'quoted'], true)) {
                Booking::updateStatus($bookingId, 'quoted', 'Firm price of ' . money($price) . ' sent to the customer.', null, $actor);
            } else {
                Booking::addEvent($bookingId, $booking['status'], 'Price updated to ' . money($price) . '.', null, $actor);
            }
            flash('success', 'Quote saved.');
            break;

        case 'assign':
            $driver = (string) input('driver_name');
            $reg    = (string) input('vehicle_reg');
            Booking::assignDriver($bookingId, $driver, $reg);
            if ($driver !== '') {
                Booking::updateStatus($bookingId, 'assigned', trim($driver . ' assigned on ' . $reg), $booking['pickup_city'], $actor);
            }
            flash('success', 'Driver and vehicle recorded.');
            break;

        case 'note':
            $note = trim((string) input('note'));
            if ($note !== '') {
                Booking::addEvent($bookingId, $booking['status'], $note, (string) input('location'), $actor);
                flash('success', 'Note added to the timeline.');
            }
            break;

        case 'delete':
            if (!is_admin()) {
                flash('error', 'Only administrators can delete bookings.');
                break;
            }
            Booking::delete($bookingId);
            flash('success', 'Booking ' . $booking['reference'] . ' deleted.');
            redirect('/admin/logistics');
    }

    redirect('/admin/logistics/' . $bookingId);
}

$events   = Booking::events($bookingId);
$estimate = Booking::estimate(
    $booking['vehicle_type'],
    (string) $booking['distance_band'],
    (int) $booking['weight_kg'],
    $booking['urgency'],
    (bool) $booking['needs_labour']
);

partial('admin_header', [
    'title'    => $booking['reference'],
    'subtitle' => $booking['service_type'] . ' booked ' . date_human($booking['created_at'], true),
    'actions'  => '<a class="btn btn-ghost btn-sm" href="' . url('/admin/logistics') . '">Back to bookings</a>'
                . '<button class="btn btn-ghost btn-sm no-print" onclick="window.print()">Print</button>',
]);
?>

<div class="split">
    <div>
        <div class="card mb-3">
            <div class="card-head">
                <h3>Job sheet</h3>
                <span class="badge badge-<?= status_tone($booking['status']) ?>">
                    <?= e(Booking::statuses()[$booking['status']] ?? $booking['status']) ?>
                </span>
            </div>
            <div class="card-body">
                <div class="grid grid-2" style="gap:1.5rem">
                    <div>
                        <h4>Pickup</h4>
                        <p class="small mb-0">
                            <?= e($booking['pickup_address']) ?><br>
                            <span class="strong"><?= e($booking['pickup_city']) ?></span><br>
                            <?= e(date_human($booking['pickup_date'])) ?> &middot; <?= e($booking['pickup_time'] ?: 'Flexible') ?>
                        </p>
                    </div>
                    <div>
                        <h4>Destination</h4>
                        <p class="small mb-0">
                            <?= e($booking['destination_address']) ?><br>
                            <span class="strong"><?= e($booking['destination_city']) ?></span><br>
                            <?= e($booking['distance_band']) ?>
                        </p>
                    </div>
                </div>

                <hr>

                <dl class="spec-list" style="margin:0">
                    <div><dt>Service</dt><dd><?= e($booking['service_type']) ?></dd></div>
                    <div><dt>Vehicle</dt><dd><?= e($booking['vehicle_type']) ?></dd></div>
                    <div><dt>Weight</dt><dd><?= number_format((int) $booking['weight_kg']) ?>kg</dd></div>
                    <div><dt>Urgency</dt><dd><?= e($booking['urgency']) ?></dd></div>
                    <div><dt>Loading crew</dt><dd><?= (int) $booking['needs_labour'] ? 'Requested' : 'Not requested' ?></dd></div>
                </dl>

                <?php if ($booking['description']): ?>
                    <h4 class="mt-3">Cargo</h4>
                    <p class="small muted mb-0"><?= nl2br(e($booking['description'])) ?></p>
                <?php endif; ?>
                <?php if ($booking['instructions']): ?>
                    <h4 class="mt-3">Special instructions</h4>
                    <p class="small muted mb-0"><?= nl2br(e($booking['instructions'])) ?></p>
                <?php endif; ?>
            </div>
        </div>

        <div class="card">
            <div class="card-head"><h3>Timeline</h3></div>
            <div class="card-body">
                <?php partial('booking_timeline', ['booking' => $booking, 'events' => $events]); ?>

                <hr>
                <form method="post" class="no-print">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="note">
                    <div class="field-row">
                        <div class="field">
                            <label for="note">Add a note to the timeline</label>
                            <input class="input" id="note" name="note" placeholder="Customer moved pickup to 9am" required>
                        </div>
                        <div class="field">
                            <label for="note_location">Location (optional)</label>
                            <input class="input" id="note_location" name="location" placeholder="Asaba">
                        </div>
                    </div>
                    <button class="btn btn-ghost btn-sm" type="submit">Add note</button>
                </form>
            </div>
        </div>
    </div>

    <aside>
        <div class="card card-pad mb-3">
            <h4>Pricing</h4>
            <div class="summary-row"><span class="label">Base fare</span><span><?= money($estimate['base']) ?></span></div>
            <div class="summary-row"><span class="label">Distance</span><span><?= money($estimate['distance']) ?></span></div>
            <div class="summary-row"><span class="label">Weight surcharge</span><span><?= money($estimate['weight']) ?></span></div>
            <div class="summary-row"><span class="label">Urgency</span><span><?= money($estimate['urgency']) ?></span></div>
            <div class="summary-row"><span class="label">Loading crew</span><span><?= money($estimate['labour']) ?></span></div>
            <div class="summary-total"><span>System estimate</span><span><?= money($booking['estimated_price']) ?></span></div>

            <form method="post" class="mt-3 no-print">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="quote">
                <div class="field">
                    <label for="quoted_price">Firm price to charge</label>
                    <input class="input" type="number" step="0.01" min="0" id="quoted_price" name="quoted_price"
                           value="<?= e(number_format((float) ($booking['quoted_price'] ?? $booking['estimated_price']), 2, '.', '')) ?>">
                </div>
                <button class="btn btn-primary btn-block" type="submit">Save quote and notify</button>
            </form>
        </div>

        <div class="card card-pad mb-3 no-print">
            <h4>Move this job on</h4>
            <form method="post">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="status">
                <div class="field">
                    <label for="status">New status</label>
                    <select class="select" id="status" name="status">
                        <?php foreach (Booking::statuses() as $key => $label): ?>
                            <option value="<?= $key ?>" <?= $booking['status'] === $key ? 'selected' : '' ?>><?= $label ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="field">
                    <label for="status_note">Note for the customer</label>
                    <input class="input" id="status_note" name="note" placeholder="Vehicle loaded and departing">
                </div>
                <div class="field">
                    <label for="status_location">Location</label>
                    <input class="input" id="status_location" name="location" placeholder="Ughelli">
                </div>
                <button class="btn btn-primary btn-block" type="submit">Update status</button>
            </form>

            <hr>

            <form method="post">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="assign">
                <div class="field-row">
                    <div class="field">
                        <label for="driver_name">Driver</label>
                        <input class="input" id="driver_name" name="driver_name" value="<?= e($booking['driver_name'] ?? '') ?>">
                    </div>
                    <div class="field">
                        <label for="vehicle_reg">Vehicle reg</label>
                        <input class="input mono" id="vehicle_reg" name="vehicle_reg" value="<?= e($booking['vehicle_reg'] ?? '') ?>" placeholder="DEL-123-XA">
                    </div>
                </div>
                <button class="btn btn-ghost btn-block" type="submit">Assign driver</button>
            </form>
        </div>

        <div class="card card-pad mb-3">
            <h4>Customer</h4>
            <p class="small mb-0">
                <span class="strong"><?= e($booking['customer_name']) ?></span><br>
                <?php if ($booking['company']): ?><?= e($booking['company']) ?><br><?php endif; ?>
                <a href="mailto:<?= e($booking['email']) ?>"><?= e($booking['email']) ?></a><br>
                <a href="tel:<?= e($booking['phone']) ?>"><?= e($booking['phone']) ?></a>
            </p>
        </div>

        <?php if (is_admin()): ?>
            <div class="card card-pad no-print">
                <h4>Danger zone</h4>
                <p class="small muted">Deleting removes the booking and its timeline permanently.</p>
                <form method="post" data-confirm="Permanently delete <?= e($booking['reference']) ?>?">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="delete">
                    <button class="btn btn-danger btn-block btn-sm" type="submit">Delete booking</button>
                </form>
            </div>
        <?php endif; ?>
    </aside>
</div>

<?php partial('admin_footer'); ?>
