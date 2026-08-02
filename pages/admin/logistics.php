<?php
$filters = [
    'status'       => (string) input('status', ''),
    'vehicle_type' => (string) input('vehicle_type', ''),
    'q'            => (string) input('q', ''),
    'page'         => input_int('page', 1),
];
$result = Booking::paginate($filters);
$stats  = Booking::stats();

partial('admin_header', [
    'title'    => 'Logistics bookings',
    'subtitle' => number_format($result['total']) . ' booking' . ($result['total'] === 1 ? '' : 's') . ' matching the current filters',
]);
?>

<div class="stat-grid mb-3">
    <div class="stat"><div class="k">Booked today</div><div class="v"><?= $stats['today'] ?></div><div class="m"><?= $stats['open'] ?> open jobs</div></div>
    <div class="stat"><div class="k">Awaiting quote</div><div class="v"><?= $stats['awaiting'] ?></div><div class="m">Price these first</div></div>
    <div class="stat"><div class="k">Completed</div><div class="v"><?= $stats['completed'] ?></div><div class="m">All time</div></div>
    <div class="stat accent"><div class="k">Value this month</div><div class="v"><?= money_short($stats['value_month']) ?></div><div class="m">Quoted or estimated</div></div>
</div>

<nav class="tabs">
    <a href="<?= url('/admin/logistics') ?>" class="<?= $filters['status'] === '' ? 'is-active' : '' ?>">All</a>
    <?php foreach (['pending' => 'Pending', 'quoted' => 'Quoted', 'assigned' => 'Assigned', 'in_transit' => 'In transit', 'completed' => 'Completed'] as $key => $label): ?>
        <a href="<?= url('/admin/logistics') ?>?status=<?= $key ?>" class="<?= $filters['status'] === $key ? 'is-active' : '' ?>"><?= $label ?></a>
    <?php endforeach; ?>
</nav>

<form class="data-filters" method="get">
    <div class="field">
        <label for="q">Search</label>
        <input class="input" id="q" name="q" value="<?= e($filters['q']) ?>" placeholder="Reference, name, town">
    </div>
    <div class="field">
        <label for="status">Status</label>
        <select class="select" id="status" name="status">
            <option value="">Any status</option>
            <?php foreach (Booking::statuses() as $key => $label): ?>
                <option value="<?= $key ?>" <?= $filters['status'] === $key ? 'selected' : '' ?>><?= $label ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="field">
        <label for="vehicle_type">Vehicle</label>
        <select class="select" id="vehicle_type" name="vehicle_type">
            <option value="">Any vehicle</option>
            <?php foreach (array_keys(Booking::vehicleTypes()) as $vehicle): ?>
                <option value="<?= e($vehicle) ?>" <?= $filters['vehicle_type'] === $vehicle ? 'selected' : '' ?>><?= e($vehicle) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="flex gap-sm">
        <button class="btn btn-primary" type="submit">Filter</button>
        <a class="btn btn-ghost" href="<?= url('/admin/logistics') ?>">Reset</a>
    </div>
</form>

<?php if (!$result['rows']): ?>
    <div class="empty">
        <div class="mark">0</div>
        <h3>No bookings match those filters</h3>
        <p>Logistics requests submitted from the site land here.</p>
        <a class="btn btn-primary" href="<?= url('/admin/logistics') ?>">Clear filters</a>
    </div>
<?php else: ?>
    <div class="card table-flush">
        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr><th>Reference</th><th>Customer</th><th>Route</th><th>Vehicle</th>
                        <th>Pickup</th><th>Status</th><th class="num">Price</th><th class="tight"></th></tr>
                </thead>
                <tbody>
                <?php foreach ($result['rows'] as $booking): ?>
                    <tr>
                        <td>
                            <a class="cell-title mono" href="<?= url('/admin/logistics/' . $booking['id']) ?>"><?= e($booking['reference']) ?></a>
                            <div class="cell-sub"><?= e($booking['service_type']) ?></div>
                        </td>
                        <td>
                            <div class="cell-title"><?= e($booking['customer_name']) ?></div>
                            <div class="cell-sub"><?= e($booking['company'] ?: $booking['phone']) ?></div>
                        </td>
                        <td class="small">
                            <?= e($booking['pickup_city']) ?> &rarr; <?= e($booking['destination_city']) ?>
                            <div class="cell-sub"><?= e($booking['distance_band']) ?></div>
                        </td>
                        <td class="small">
                            <?= e($booking['vehicle_type']) ?>
                            <div class="cell-sub"><?= number_format((int) $booking['weight_kg']) ?>kg</div>
                        </td>
                        <td class="small nowrap">
                            <?= e(date_human($booking['pickup_date'])) ?>
                            <div class="cell-sub"><?= e($booking['pickup_time'] ?: 'Flexible') ?></div>
                        </td>
                        <td><span class="badge badge-<?= status_tone($booking['status']) ?>"><?= e(Booking::statuses()[$booking['status']] ?? $booking['status']) ?></span></td>
                        <td class="num strong">
                            <?= money($booking['quoted_price'] ?? $booking['estimated_price']) ?>
                            <?php if ($booking['quoted_price'] === null): ?><div class="cell-sub">estimate</div><?php endif; ?>
                        </td>
                        <td class="tight"><a class="btn btn-ghost btn-sm" href="<?= url('/admin/logistics/' . $booking['id']) ?>">Open</a></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php partial('pagination', ['page' => $result['page'], 'pages' => $result['pages']]); ?>
<?php endif; ?>

<?php partial('admin_footer'); ?>
