<?php
$totals    = cart_totals();
$threshold = (float) Setting::get('free_delivery_from', (string) FREE_DELIVERY_FROM);
$shortfall = max(0, $threshold - $totals['subtotal']);
$progress  = $threshold > 0 ? min(100, (int) round($totals['subtotal'] / $threshold * 100)) : 100;

partial('header', ['title' => page_title('Your cart')]);
?>

<section class="section-sm pb-20">
    <div class="shell">

        <nav class="mb-6 flex items-center gap-1.5 text-xs text-ink-400" aria-label="Breadcrumb">
            <a class="inline-flex min-h-6 items-center transition-colors hover:text-navy-700" href="<?= url('/') ?>">Home</a>
            <?= icon('chevron-right', 'size-3') ?>
            <span class="font-semibold text-navy-700">Cart</span>
        </nav>

        <div class="mb-8 flex flex-wrap items-end justify-between gap-4">
            <div>
                <h1 class="h-section">Your cart</h1>
                <p class="mt-2 text-ink-500">
                    <?= $totals['count'] ?> item<?= $totals['count'] === 1 ? '' : 's' ?> ready to schedule for delivery.
                </p>
            </div>
            <?php if ($totals['lines']): ?>
                <a class="btn btn-ghost gap-2" href="<?= url('/products') ?>">
                    <?= icon('arrow-left', 'size-4') ?>Continue shopping
                </a>
            <?php endif; ?>
        </div>

        <?php if (!$totals['lines']): ?>
            <div class="card flex flex-col items-center px-6 py-20 text-center">
                <span class="grid size-20 place-items-center rounded-3xl bg-ink-100 text-ink-400">
                    <?= icon('cart', 'size-10') ?>
                </span>
                <h2 class="mt-6 text-2xl">Your cart is empty</h2>
                <p class="mt-2 max-w-md text-ink-500">
                    Add products from the catalogue, or send us a bulk list and we will price it for you.
                </p>
                <div class="mt-7 flex flex-wrap justify-center gap-3">
                    <a class="btn btn-primary btn-lg gap-2" href="<?= url('/products') ?>">
                        <?= icon('package', 'size-5') ?>Browse the catalogue
                    </a>
                    <a class="btn btn-ghost btn-lg" href="<?= url('/quote') ?>">Request a quote</a>
                </div>
            </div>
        <?php else: ?>
            <div class="grid gap-8 lg:grid-cols-[minmax(0,1fr)_22rem] lg:items-start">

                <!-- Lines -->
                <div class="card overflow-hidden">
                    <div class="flex items-center justify-between gap-4 border-b border-ink-100 px-6 py-4">
                        <h2 class="text-base">Items</h2>
                        <form method="post" action="<?= url('/cart/clear') ?>" data-confirm="Empty your cart?">
                            <?= csrf_field() ?>
                            <button class="inline-flex cursor-pointer items-center gap-1.5 text-sm font-semibold text-ink-400 transition-colors hover:text-red-600" type="submit">
                                <?= icon('trash', 'size-4') ?>Empty cart
                            </button>
                        </form>
                    </div>

                    <ul class="divide-y divide-ink-100">
                        <?php foreach ($totals['lines'] as $line): ?>
                            <?php $product = $line['product']; ?>
                            <li class="flex flex-wrap items-start gap-4 p-5 sm:flex-nowrap">

                                <a href="<?= url('/products/' . $product['slug']) ?>"
                                   class="relative grid size-20 shrink-0 place-items-center overflow-hidden rounded-xl border border-ink-200 bg-navy-50">
                                    <?php if (!empty($product['image'])): ?>
                                        <img src="<?= e(UPLOAD_URL . '/' . $product['image']) ?>" alt=""
                                             class="size-full object-cover" loading="lazy" width="80" height="80">
                                    <?php else: ?>
                                        <span class="text-navy-500"><?= icon(category_icon($product['category_slug'] ?? ''), 'size-8') ?></span>
                                    <?php endif; ?>
                                </a>

                                <div class="min-w-0 flex-1">
                                    <a class="font-display font-bold leading-snug text-navy-700 transition-colors hover:text-orange-600"
                                       href="<?= url('/products/' . $product['slug']) ?>">
                                        <?= e($product['name']) ?>
                                    </a>
                                    <p class="mt-0.5 text-xs text-ink-400">
                                        per <?= e($product['unit']) ?><?= $product['sku'] ? ' &middot; ' . e($product['sku']) : '' ?>
                                    </p>

                                    <p class="mt-2 flex flex-wrap items-center gap-2 text-sm">
                                        <span class="price font-semibold text-navy-700"><?= money($line['unit_price']) ?></span>
                                        <?php if ($line['is_wholesale']): ?>
                                            <span class="badge badge-navy gap-1"><?= icon('tag', 'size-3') ?>Wholesale rate</span>
                                        <?php elseif ($line['to_wholesale'] > 0): ?>
                                            <span class="badge badge-warn">
                                                Add <?= $line['to_wholesale'] ?> more for <?= money($product['wholesale_price']) ?>
                                            </span>
                                        <?php endif; ?>
                                    </p>
                                </div>

                                <div class="flex items-center gap-4 sm:flex-col sm:items-end">
                                    <form method="post" action="<?= url('/cart/update') ?>">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="product_id" value="<?= (int) $product['id'] ?>">
                                        <div class="qty">
                                            <button type="button" data-qty="down" aria-label="Decrease quantity"><?= icon('minus', 'size-4') ?></button>
                                            <input type="number" name="quantity" value="<?= (int) $line['quantity'] ?>"
                                                   min="<?= max(1, (int) $product['min_order']) ?>" step="1" inputmode="numeric"
                                                   data-cart-qty aria-label="Quantity of <?= e($product['name']) ?>">
                                            <button type="button" data-qty="up" aria-label="Increase quantity"><?= icon('plus', 'size-4') ?></button>
                                        </div>
                                        <noscript><button class="btn btn-ghost btn-sm mt-2" type="submit">Update</button></noscript>
                                    </form>

                                    <div class="text-right">
                                        <p class="price font-display text-lg font-extrabold text-navy-700"><?= money($line['line_total']) ?></p>
                                        <form method="post" action="<?= url('/cart/remove') ?>">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="product_id" value="<?= (int) $product['id'] ?>">
                                            <button class="mt-1 cursor-pointer text-xs font-semibold text-ink-400 transition-colors hover:text-red-600"
                                                    type="submit">Remove</button>
                                        </form>
                                    </div>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>

                <!-- Summary -->
                <aside class="card card-pad lg:sticky lg:top-28">
                    <h2 class="text-lg">Order summary</h2>

                    <dl class="mt-5 space-y-3 text-sm">
                        <div class="flex justify-between gap-4">
                            <dt class="text-ink-500">Subtotal</dt>
                            <dd class="price font-semibold text-navy-700"><?= money($totals['subtotal']) ?></dd>
                        </div>
                        <div class="flex justify-between gap-4">
                            <dt class="text-ink-500">Delivery</dt>
                            <dd class="font-semibold <?= $totals['delivery_fee'] > 0 ? 'text-navy-700' : 'text-emerald-600' ?>">
                                <?= $totals['delivery_fee'] > 0 ? money($totals['delivery_fee']) : 'Free' ?>
                            </dd>
                        </div>
                    </dl>

                    <div class="mt-4 flex items-baseline justify-between gap-4 border-t border-ink-200 pt-4">
                        <span class="font-display text-base font-bold text-navy-700">Total</span>
                        <span class="price font-display text-2xl font-extrabold text-navy-700"><?= money($totals['total']) ?></span>
                    </div>

                    <?php if ($shortfall > 0): ?>
                        <div class="mt-5 rounded-xl border border-sky-200 bg-sky-50 p-4">
                            <p class="text-sm text-sky-900">
                                Add <span class="font-bold"><?= money($shortfall) ?></span> more for free delivery.
                            </p>
                            <div class="mt-2.5 h-2 overflow-hidden rounded-full bg-sky-200">
                                <div class="h-full rounded-full bg-sky-600 transition-all duration-500" style="width: <?= $progress ?>%"></div>
                            </div>
                        </div>
                    <?php else: ?>
                        <p class="mt-5 flex items-center gap-2 rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-800">
                            <?= icon('check-circle', 'size-5 shrink-0') ?>Free delivery applied to this order.
                        </p>
                    <?php endif; ?>

                    <a class="btn btn-primary btn-block btn-lg mt-6 gap-2" href="<?= url('/checkout') ?>">
                        Proceed to checkout<?= icon('arrow-right', 'size-4') ?>
                    </a>

                    <p class="mt-4 text-center text-xs leading-relaxed text-ink-400">
                        Prices exclude VAT. Delivery outside <?= e(APP_STATE) ?> is quoted separately.
                    </p>
                </aside>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php partial('footer'); ?>
