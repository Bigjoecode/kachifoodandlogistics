<?php
/** @var array $product */
$retail   = Product::retailPrice($product);
$hasPrice = Product::hasPrice($product);
$onSale   = Product::isOnSale($product);
$hasBulk  = Product::hasWholesale($product);
$inStock  = !$hasPrice || (int) $product['stock_qty'] > 0;
$minOrder = max(1, (int) $product['min_order']);
$bulkQty  = max(1, (int) $product['wholesale_min_qty']);
$href     = url('/products/' . $product['slug']);
?>
<article class="card card-hover group flex flex-col overflow-hidden" data-reveal>

    <a href="<?= $href ?>" class="relative block aspect-[4/3] overflow-hidden bg-navy-50" tabindex="-1" aria-hidden="true">
        <?php if (!empty($product['image'])): ?>
            <picture>
                <?php if ($webp = product_image_webp($product['image'])): ?>
                    <source srcset="<?= e($webp) ?>" type="image/webp">
                <?php endif; ?>
                <img src="<?= e(product_image_url($product['image'])) ?>" alt=""
                     class="size-full object-cover transition-transform duration-500 ease-out group-hover:scale-105"
                     loading="lazy" width="400" height="300">
            </picture>
        <?php else: ?>
            <!-- No photo uploaded: a branded category tile rather than a broken frame. -->
            <span class="absolute inset-0 bg-gradient-to-br from-navy-100 via-navy-50 to-orange-100"></span>
            <span class="absolute inset-0 grid place-items-center">
                <span class="grid size-20 place-items-center rounded-2xl bg-white/70 text-navy-600 shadow-soft
                             transition-transform duration-500 ease-out group-hover:scale-110">
                    <?= icon(category_icon($product['category_slug'] ?? ''), 'size-10') ?>
                </span>
            </span>
        <?php endif; ?>

        <span class="absolute left-3 top-3 flex flex-col items-start gap-1.5">
            <?php if ($onSale): ?><span class="badge badge-orange shadow-soft">Sale</span><?php endif; ?>
            <?php if ($hasBulk): ?><span class="badge badge-navy shadow-soft">Wholesale</span><?php endif; ?>
        </span>

        <?php if (!$inStock): ?>
            <span class="absolute right-3 top-3 badge badge-danger shadow-soft">Out of stock</span>
        <?php endif; ?>
    </a>

    <div class="flex flex-1 flex-col gap-2 p-5">
        <?php if (!empty($product['category_name'])): ?>
            <p class="text-xs font-bold uppercase tracking-[0.12em] text-ink-400"><?= e($product['category_name']) ?></p>
        <?php endif; ?>

        <h3 class="text-base leading-snug">
            <a href="<?= $href ?>" class="inline-block py-0.5 transition-colors hover:text-orange-600 focus-visible:text-orange-600">
                <?= e($product['name']) ?>
            </a>
        </h3>

        <p class="text-sm leading-relaxed text-ink-500"><?= e(excerpt($product['summary'], 74)) ?></p>

        <div class="mt-auto pt-3">
            <?php if ($hasPrice): ?>
                <div class="flex flex-wrap items-baseline gap-x-2 gap-y-0.5">
                    <span class="price font-display text-xl font-extrabold text-navy-700"><?= money($retail) ?></span>
                    <?php if ($onSale): ?>
                        <span class="text-sm text-ink-400 line-through"><?= money($product['retail_price']) ?></span>
                    <?php endif; ?>
                    <span class="text-xs text-ink-400">/ <?= e($product['unit']) ?></span>
                </div>
            <?php else: ?>
                <p class="font-display text-xl font-extrabold text-navy-700">Price on request</p>
                <p class="mt-1 text-xs text-ink-400">Current market price &middot; <?= e($product['unit']) ?></p>
            <?php endif; ?>

            <?php if ($hasBulk): ?>
                <p class="mt-1 flex items-center gap-1.5 text-xs font-semibold text-navy-600">
                    <?= icon('tag', 'size-3.5 text-orange-500') ?>
                    <?= money($product['wholesale_price']) ?> each from <?= $bulkQty ?>
                </p>
            <?php endif; ?>

            <?php if ($hasPrice): ?>
                <form method="post" action="<?= url('/cart/add') ?>" class="mt-4">
                    <?= csrf_field() ?>
                    <input type="hidden" name="product_id" value="<?= (int) $product['id'] ?>">
                    <input type="hidden" name="quantity" value="<?= $minOrder ?>">
                    <button class="btn <?= $inStock ? 'btn-outline' : 'btn-ghost' ?> btn-block gap-2"
                            type="submit" <?= $inStock ? '' : 'disabled' ?>>
                        <?php if ($inStock): ?>
                            <?= icon('cart', 'size-4') ?>Add to cart
                        <?php else: ?>
                            Unavailable
                        <?php endif; ?>
                    </button>
                </form>
            <?php else: ?>
                <a class="btn btn-outline btn-block mt-4 gap-2" href="<?= $href ?>">
                    <?= icon('message', 'size-4') ?>Request price
                </a>
            <?php endif; ?>
        </div>
    </div>
</article>
