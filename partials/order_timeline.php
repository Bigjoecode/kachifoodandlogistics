<?php
/**
 * Milestone timeline. Real recorded events take priority; the remaining
 * milestones are shown greyed out so the customer sees what is still to come.
 * @var array $order @var array $events
 */
$milestones = tracking_milestones();
$recorded   = [];
foreach ($events as $event) {
    if (!isset($recorded[$event['status']])) {
        $recorded[$event['status']] = $event;
    }
}
$currentIndex = array_search($order['status'], $milestones, true);
$cancelled    = $order['status'] === 'cancelled';
?>

<?php if (!$cancelled): ?>
    <div class="track-strip" aria-hidden="true">
        <?php foreach ($milestones as $i => $milestone): ?>
            <?php
            $class = 'seg';
            if ($currentIndex !== false && $i < $currentIndex)  $class .= ' done';
            if ($currentIndex !== false && $i === $currentIndex) $class .= ' current';
            ?>
            <span class="<?= $class ?>"></span>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<ul class="timeline">
    <?php foreach ($events as $event): ?>
        <li class="done">
            <span class="dot"></span>
            <div class="t"><?= e(status_label($event['status'])) ?></div>
            <?php if ($event['note']): ?><div class="m"><?= e($event['note']) ?></div><?php endif; ?>
            <div class="d">
                <?= e(date_human($event['created_at'], true)) ?>
                <?php if ($event['location']): ?> &middot; <?= e($event['location']) ?><?php endif; ?>
            </div>
        </li>
    <?php endforeach; ?>

    <?php if (!$cancelled): ?>
        <?php foreach ($milestones as $i => $milestone): ?>
            <?php if (isset($recorded[$milestone]) || ($currentIndex !== false && $i <= $currentIndex)) continue; ?>
            <li class="pending">
                <span class="dot"></span>
                <div class="t"><?= e(status_label($milestone)) ?></div>
                <div class="d">Pending</div>
            </li>
        <?php endforeach; ?>
    <?php endif; ?>
</ul>
