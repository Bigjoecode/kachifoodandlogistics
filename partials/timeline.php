<?php
/**
 * Storefront delivery timeline (Tailwind).
 *
 * Recorded events first, then the milestones still to come so the customer can
 * see the whole journey. The back office keeps its own copies in
 * order_timeline.php / booking_timeline.php because it runs on app.css.
 *
 * @var array  $events      Rows from Order::events() / Booking::events()
 * @var string $status      Current status of the order or booking
 * @var array  $milestones  Ordered list of milestone status keys
 * @var array  $labels      status key => human label
 */
$recorded = [];
foreach ($events as $event) {
    $recorded[$event['status']] = true;
}
$currentIndex = array_search($status, $milestones, true);
$cancelled    = $status === 'cancelled';
$label        = fn(string $key) => $labels[$key] ?? ucfirst(str_replace('_', ' ', $key));
?>

<?php if (!$cancelled): ?>
    <div class="mb-7 flex gap-1.5" aria-hidden="true">
        <?php foreach ($milestones as $i => $milestone): ?>
            <?php
            $done    = $currentIndex !== false && $i < $currentIndex;
            $current = $currentIndex !== false && $i === $currentIndex;
            ?>
            <span class="h-1.5 flex-1 rounded-full <?= $done ? 'bg-navy-600' : ($current ? 'bg-orange-500' : 'bg-ink-200') ?>"></span>
        <?php endforeach; ?>
    </div>
    <p class="sr-only">
        Status: <?= e($label($status)) ?>, step <?= ($currentIndex === false ? 1 : $currentIndex + 1) ?> of <?= count($milestones) ?>.
    </p>
<?php endif; ?>

<ol class="relative space-y-6 border-l-2 border-ink-200 pl-7">
    <?php foreach ($events as $event): ?>
        <li class="relative">
            <span class="absolute -left-[2.19rem] top-1 grid size-5 place-items-center rounded-full bg-navy-600 text-white ring-4 ring-white">
                <?= icon('check', 'size-3') ?>
            </span>
            <p class="font-display text-sm font-bold text-navy-700"><?= e($label($event['status'])) ?></p>
            <?php if ($event['note']): ?>
                <p class="mt-0.5 text-sm text-ink-500"><?= e($event['note']) ?></p>
            <?php endif; ?>
            <p class="mt-1 flex flex-wrap items-center gap-x-2 text-xs text-ink-400">
                <time datetime="<?= e($event['created_at']) ?>"><?= e(date_human($event['created_at'], true)) ?></time>
                <?php if ($event['location']): ?>
                    <span aria-hidden="true">&middot;</span>
                    <span class="inline-flex items-center gap-1"><?= icon('map-pin', 'size-3') ?><?= e($event['location']) ?></span>
                <?php endif; ?>
            </p>
        </li>
    <?php endforeach; ?>

    <?php if (!$cancelled): ?>
        <?php foreach ($milestones as $i => $milestone): ?>
            <?php if (isset($recorded[$milestone]) || ($currentIndex !== false && $i <= $currentIndex)) continue; ?>
            <li class="relative">
                <span class="absolute -left-[2.19rem] top-1 size-5 rounded-full border-2 border-ink-200 bg-white ring-4 ring-white"></span>
                <p class="font-display text-sm font-bold text-ink-400"><?= e($label($milestone)) ?></p>
                <p class="mt-0.5 text-xs text-ink-400">Pending</p>
            </li>
        <?php endforeach; ?>
    <?php endif; ?>
</ol>
