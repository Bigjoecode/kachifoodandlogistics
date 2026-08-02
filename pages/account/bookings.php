<?php
$user     = auth_user();
$bookings = Booking::forUser((int) $user['id']);

$open  = 0;
$spend = 0.0;
foreach ($bookings as $booking) {
    if (!in_array($booking['status'], ['completed', 'cancelled'], true)) {
        $open++;
    }
    if ($booking['status'] !== 'cancelled') {
        $spend += (float) ($booking['quoted_price'] ?? $booking['estimated_price']);
    }
}

$tone = fn(string $status) => status_tone($status) === 'brand' ? 'navy' : status_tone($status);

partial('header', ['title' => page_title('My logistics bookings')]);
partial('account_head', ['heading' => 'My bookings', 'sub' => 'Every truck, van and delivery job booked on this account.']);
?>

<section class="section-sm pb-20">
    <div class="shell">
        <?php partial('account_nav'); ?>

        <div class="mb-8 grid gap-4 sm:grid-cols-3">
            <?php foreach ([
                ['truck', 'Total bookings', number_format(count($bookings)), 'Across all time', false],
                ['clock', 'Open jobs', number_format($open), 'Not yet completed', false],
                ['banknote', 'Logistics spend', money_short($spend), 'Quoted or estimated', true],
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

        <?php if (!$bookings): ?>
            <div class="card flex flex-col items-center px-6 py-16 text-center">
                <span class="grid size-16 place-items-center rounded-2xl bg-ink-100 text-ink-400">
                    <?= icon('truck', 'size-8') ?>
                </span>
                <h2 class="mt-5 text-xl">No bookings yet</h2>
                <p class="mt-2 max-w-sm text-ink-500">
                    Hire a van, truck or flatbed and it will show up here with a live status.
                </p>
                <a class="btn btn-primary mt-6 gap-2" href="<?= url('/logistics') ?>">
                    <?= icon('truck', 'size-5') ?>Book logistics
                </a>
            </div>
        <?php else: ?>
            <div class="card overflow-hidden">
                <div class="table-wrap">
                    <table class="table">
                        <thead>
                            <tr><th>Reference</th><th>Booked</th><th>Route</th><th>Vehicle</th>
                                <th>Status</th><th class="num">Price</th><th></th></tr>
                        </thead>
                        <tbody>
                        <?php foreach ($bookings as $booking): ?>
                            <tr>
                                <td>
                                    <a class="cell-title font-mono transition-colors hover:text-orange-600"
                                       href="<?= url('/logistics/' . $booking['reference']) ?>"><?= e($booking['reference']) ?></a>
                                    <span class="cell-sub block"><?= e($booking['service_type']) ?></span>
                                </td>
                                <td class="whitespace-nowrap">
                                    <span class="text-sm text-ink-600"><?= e(date_human($booking['created_at'])) ?></span>
                                    <span class="cell-sub block"><?= e(time_ago($booking['created_at'])) ?></span>
                                </td>
                                <td>
                                    <span class="flex items-center gap-1.5 text-sm text-ink-600">
                                        <?= e($booking['pickup_city']) ?>
                                        <?= icon('arrow-right', 'size-3 text-ink-300') ?>
                                        <?= e($booking['destination_city']) ?>
                                    </span>
                                </td>
                                <td class="text-sm text-ink-600"><?= e($booking['vehicle_type']) ?></td>
                                <td>
                                    <span class="badge badge-<?= $tone($booking['status']) ?>">
                                        <?= e(Booking::statuses()[$booking['status']] ?? $booking['status']) ?>
                                    </span>
                                </td>
                                <td class="num font-semibold text-navy-700">
                                    <?= money($booking['quoted_price'] ?? $booking['estimated_price']) ?>
                                </td>
                                <td class="text-right">
                                    <a class="btn btn-ghost btn-sm" href="<?= url('/logistics/' . $booking['reference']) ?>">View</a>
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
