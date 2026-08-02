<?php
/** @var string $heading @var string $sub */
$user = auth_user();
?>
<section class="relative isolate overflow-hidden bg-navy-800">
    <div class="absolute inset-0 bg-grid opacity-30"></div>
    <div class="absolute -right-24 -top-24 size-64 rounded-full bg-orange-500/20 blur-3xl"></div>

    <div class="shell relative py-10 sm:py-12">
        <div class="flex flex-wrap items-center gap-4">
            <span class="grid size-14 place-items-center rounded-2xl bg-white/10 font-display text-xl font-extrabold text-white backdrop-blur">
                <?= e(strtoupper(substr($user['name'], 0, 1))) ?>
            </span>
            <div>
                <h1 class="font-display text-2xl font-extrabold text-white sm:text-3xl"><?= e($heading) ?></h1>
                <p class="mt-1 text-sm text-navy-200"><?= e($sub) ?></p>
            </div>

            <form method="post" action="<?= url('/logout') ?>" class="ml-auto">
                <?= csrf_field() ?>
                <button class="btn btn-onglass gap-2" type="submit">
                    <?= icon('logout', 'size-4') ?>Sign out
                </button>
            </form>
        </div>
    </div>
</section>
