<?php
/** @var int $page @var int $pages */
if (($pages ?? 1) < 2) {
    return;
}
$window = 1;
$from   = max(1, $page - $window);
$to     = min($pages, $page + $window);

$base   = 'inline-flex min-h-11 min-w-11 items-center justify-center gap-1 rounded-xl border px-3 text-sm font-semibold transition-colors';
$idle   = $base . ' border-ink-200 bg-white text-ink-700 hover:border-navy-300 hover:text-navy-700';
$active = $base . ' border-navy-700 bg-navy-700 text-white';
$off    = $base . ' border-ink-200 bg-ink-50 text-ink-300 pointer-events-none';
?>
<nav class="mt-10 flex flex-wrap items-center justify-center gap-2" aria-label="Pagination">
    <a class="<?= $page <= 1 ? $off : $idle ?>" href="<?= e(query_with(['page' => $page - 1])) ?>"
       <?= $page <= 1 ? 'aria-disabled="true" tabindex="-1"' : 'rel="prev"' ?>>
        <?= icon('arrow-left', 'size-4') ?><span class="hidden sm:inline">Prev</span>
    </a>

    <?php if ($from > 1): ?>
        <a class="<?= $idle ?>" href="<?= e(query_with(['page' => 1])) ?>">1</a>
        <?php if ($from > 2): ?><span class="px-1 text-ink-300">&hellip;</span><?php endif; ?>
    <?php endif; ?>

    <?php for ($i = $from; $i <= $to; $i++): ?>
        <?php if ($i === $page): ?>
            <span class="<?= $active ?>" aria-current="page"><?= $i ?></span>
        <?php else: ?>
            <a class="<?= $idle ?>" href="<?= e(query_with(['page' => $i])) ?>"><?= $i ?></a>
        <?php endif; ?>
    <?php endfor; ?>

    <?php if ($to < $pages): ?>
        <?php if ($to < $pages - 1): ?><span class="px-1 text-ink-300">&hellip;</span><?php endif; ?>
        <a class="<?= $idle ?>" href="<?= e(query_with(['page' => $pages])) ?>"><?= $pages ?></a>
    <?php endif; ?>

    <a class="<?= $page >= $pages ? $off : $idle ?>" href="<?= e(query_with(['page' => $page + 1])) ?>"
       <?= $page >= $pages ? 'aria-disabled="true" tabindex="-1"' : 'rel="next"' ?>>
        <span class="hidden sm:inline">Next</span><?= icon('arrow-right', 'size-4') ?>
    </a>
</nav>
