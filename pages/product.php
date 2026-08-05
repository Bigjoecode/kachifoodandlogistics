<?php
/** @var array $params */
$product = Product::findBySlug($params['slug'] ?? '');
if (!$product) {
    abort_404();
}

$retail   = Product::retailPrice($product);
$onSale   = Product::isOnSale($product);
$hasBulk  = Product::hasWholesale($product);
$bulkQty  = max(1, (int) $product['wholesale_min_qty']);
$inStock  = (int) $product['stock_qty'] > 0;
$minOrder = max(1, (int) $product['min_order']);
$related  = Product::related($product, 4);

$whatsapp = Setting::get('whatsapp');
$waText   = rawurlencode('Hello KACHI, I would like to order: ' . $product['name'] . ' (' . $product['sku'] . ').');

/** Product + breadcrumb structured data for this page. */
$schema = json_encode([
    [
        '@context'    => 'https://schema.org',
        '@type'       => 'Product',
        'name'        => $product['name'],
        'description' => $product['summary'] ?: excerpt($product['description'], 300),
        'sku'         => $product['sku'],
        'category'    => $product['category_name'],
        'brand'       => ['@type' => 'Brand', 'name' => 'KACHI'],
        'offers'      => [
            '@type'         => 'Offer',
            'url'           => rtrim(APP_DOMAIN, '/') . '/products/' . $product['slug'],
            'priceCurrency' => 'NGN',
            'price'         => number_format($retail, 2, '.', ''),
            'availability'  => $inStock ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock',
            'seller'        => ['@type' => 'Organization', 'name' => Setting::get('site_name', APP_NAME)],
        ],
    ],
    [
        '@context'        => 'https://schema.org',
        '@type'           => 'BreadcrumbList',
        'itemListElement' => array_values(array_filter([
            ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => APP_DOMAIN],
            ['@type' => 'ListItem', 'position' => 2, 'name' => 'Catalogue', 'item' => rtrim(APP_DOMAIN, '/') . '/products'],
            $product['category_slug']
                ? ['@type' => 'ListItem', 'position' => 3, 'name' => $product['category_name'],
                   'item' => rtrim(APP_DOMAIN, '/') . '/category/' . $product['category_slug']]
                : null,
        ])),
    ],
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

partial('header', [
    'title'       => page_title($product['name']),
    'description' => $product['summary'] ?: excerpt($product['description'], 155),
    'schema'      => $schema,
]);
?>

<section class="section-sm">
    <div class="shell">
        <nav class="mb-8 flex flex-wrap items-center gap-1.5 text-xs text-ink-400" aria-label="Breadcrumb">
            <a class="inline-flex min-h-6 items-center transition-colors hover:text-navy-700" href="<?= url('/') ?>">Home</a>
            <?= icon('chevron-right', 'size-3') ?>
            <a class="inline-flex min-h-6 items-center transition-colors hover:text-navy-700" href="<?= url('/products') ?>">Catalogue</a>
            <?php if ($product['category_slug']): ?>
                <?= icon('chevron-right', 'size-3') ?>
                <a class="inline-flex min-h-6 items-center transition-colors hover:text-navy-700" href="<?= url('/category/' . $product['category_slug']) ?>">
                    <?= e($product['category_name']) ?>
                </a>
            <?php endif; ?>
            <?= icon('chevron-right', 'size-3') ?>
            <span class="font-semibold text-navy-700"><?= e($product['name']) ?></span>
        </nav>

        <div class="grid gap-10 lg:grid-cols-2 lg:gap-14">

            <!-- Visual -->
            <div class="lg:sticky lg:top-28 lg:self-start">
                <div class="relative aspect-square overflow-hidden rounded-3xl border border-ink-200 bg-navy-50 shadow-soft">
                    <?php if (!empty($product['image'])): ?>
                        <img src="<?= e(product_image_url($product['image'])) ?>" alt="<?= e($product['name']) ?>"
                             class="size-full object-cover" width="800" height="800">
                    <?php else: ?>
                        <span class="absolute inset-0 bg-gradient-to-br from-navy-100 via-navy-50 to-orange-100"></span>
                        <span class="absolute inset-0 grid place-items-center">
                            <span class="grid size-40 place-items-center rounded-3xl bg-white/70 text-navy-600 shadow-soft">
                                <?= icon(category_icon($product['category_slug'] ?? ''), 'size-20') ?>
                            </span>
                        </span>
                    <?php endif; ?>

                    <?php if ($onSale): ?>
                        <span class="absolute left-5 top-5 badge badge-orange shadow-lift">On offer</span>
                    <?php endif; ?>
                </div>

                <div class="mt-4 grid grid-cols-3 gap-3">
                    <?php foreach ([
                        ['shield', 'Quality checked'],
                        ['truck', 'Own fleet delivery'],
                        ['route', 'Trackable order'],
                    ] as [$ico, $label]): ?>
                        <div class="rounded-xl border border-ink-200 bg-white p-3 text-center">
                            <span class="mx-auto grid size-9 place-items-center rounded-lg bg-navy-50 text-navy-600">
                                <?= icon($ico, 'size-4') ?>
                            </span>
                            <p class="mt-2 text-xs font-semibold leading-tight text-ink-600"><?= $label ?></p>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Detail -->
            <div>
                <?php if ($product['category_name']): ?>
                    <a class="badge badge-navy gap-1.5" href="<?= url('/category/' . $product['category_slug']) ?>">
                        <?= icon(category_icon($product['category_slug']), 'size-3.5') ?>
                        <?= e($product['category_name']) ?>
                    </a>
                <?php endif; ?>

                <h1 class="mt-4 font-display text-3xl font-extrabold tracking-tight text-navy-700 sm:text-4xl">
                    <?= e($product['name']) ?>
                </h1>
                <p class="mt-3 text-lg leading-relaxed text-ink-500"><?= e($product['summary']) ?></p>

                <!-- Pricing -->
                <div class="mt-7 rounded-2xl border border-ink-200 bg-white p-6 shadow-soft">
                    <div class="flex flex-wrap items-end justify-between gap-4">
                        <div>
                            <p class="text-xs font-bold uppercase tracking-wider text-ink-400">Retail price</p>
                            <p class="mt-1 flex items-baseline gap-2">
                                <span class="price font-display text-4xl font-extrabold text-navy-700"><?= money($retail) ?></span>
                                <?php if ($onSale): ?>
                                    <span class="text-base text-ink-400 line-through"><?= money($product['retail_price']) ?></span>
                                <?php endif; ?>
                            </p>
                            <p class="mt-1 text-sm text-ink-400">per <?= e($product['unit']) ?></p>
                        </div>

                        <?php if ($hasBulk): ?>
                            <div class="rounded-xl bg-navy-700 px-5 py-4 text-white">
                                <p class="flex items-center gap-1.5 text-xs font-bold uppercase tracking-wider text-orange-400">
                                    <?= icon('tag', 'size-3.5') ?>Wholesale
                                </p>
                                <p class="price mt-1 font-display text-2xl font-extrabold"><?= money($product['wholesale_price']) ?></p>
                                <p class="mt-0.5 text-xs text-navy-200">
                                    from <?= $bulkQty ?> <?= e($product['unit']) ?>
                                    &middot; save <?= money(Product::wholesaleSaving($product)) ?> each
                                </p>
                            </div>
                        <?php endif; ?>
                    </div>

                    <?php if ($hasBulk): ?>
                        <p class="mt-4 flex items-start gap-2 rounded-xl bg-navy-50 p-3 text-xs leading-relaxed text-navy-700">
                            <?= icon('info', 'size-4 shrink-0 mt-px') ?>
                            The wholesale rate is applied automatically in your cart once the quantity reaches <?= $bulkQty ?>.
                        </p>
                    <?php endif; ?>
                </div>

                <!-- Stock -->
                <div class="mt-5 flex flex-wrap gap-2">
                    <?php if ($inStock): ?>
                        <span class="badge badge-success badge-dot"><?= number_format((int) $product['stock_qty']) ?> in stock</span>
                    <?php else: ?>
                        <span class="badge badge-danger badge-dot">Out of stock</span>
                    <?php endif; ?>
                    <span class="badge badge-muted">Min order <?= $minOrder ?> <?= e($product['unit']) ?></span>
                    <?php if ($product['sku']): ?>
                        <span class="badge badge-muted font-mono"><?= e($product['sku']) ?></span>
                    <?php endif; ?>
                    <?php if ($product['origin']): ?>
                        <span class="badge badge-muted gap-1.5"><?= icon('map-pin', 'size-3') ?><?= e($product['origin']) ?></span>
                    <?php endif; ?>
                </div>

                <!-- Buy -->
                <form method="post" action="<?= url('/cart/add') ?>" class="mt-7 rounded-2xl border border-ink-200 bg-ink-50 p-5">
                    <?= csrf_field() ?>
                    <input type="hidden" name="product_id" value="<?= (int) $product['id'] ?>">

                    <div class="flex flex-wrap items-end gap-4">
                        <div>
                            <label class="label" for="quantity">Quantity (<?= e($product['unit']) ?>)</label>
                            <div class="qty">
                                <button type="button" data-qty="down" aria-label="Decrease quantity"><?= icon('minus', 'size-4') ?></button>
                                <input type="number" id="quantity" name="quantity" value="<?= $minOrder ?>"
                                       min="<?= $minOrder ?>" step="1" inputmode="numeric">
                                <button type="button" data-qty="up" aria-label="Increase quantity"><?= icon('plus', 'size-4') ?></button>
                            </div>
                        </div>

                        <button class="btn btn-primary btn-lg flex-1 gap-2" type="submit" <?= $inStock ? '' : 'disabled' ?>>
                            <?php if ($inStock): ?>
                                <?= icon('cart', 'size-5') ?>Add to cart
                            <?php else: ?>
                                Currently unavailable
                            <?php endif; ?>
                        </button>
                    </div>

                    <?php if ($whatsapp): ?>
                        <a class="btn btn-ghost btn-block mt-3 gap-2"
                           href="https://wa.me/<?= e($whatsapp) ?>?text=<?= $waText ?>" rel="noopener">
                            <?= icon('message', 'size-5 text-[#25D366]') ?>Order this on WhatsApp
                        </a>
                    <?php endif; ?>

                    <p class="mt-4 text-xs leading-relaxed text-ink-500">
                        Delivery is <?= money(Setting::get('delivery_fee', (string) DELIVERY_FEE)) ?> within <?= e(APP_CITY) ?>,
                        free above <?= money(Setting::get('free_delivery_from', (string) FREE_DELIVERY_FROM)) ?>.
                        Outside <?= e(APP_STATE) ?>, <a class="link-quiet" href="<?= url('/quote') ?>">request a quote</a>.
                    </p>
                </form>

                <!-- Specs -->
                <?php
                $specs = [
                    'Unit'          => e($product['unit']),
                    'Minimum order' => $minOrder . ' ' . e($product['unit']),
                ];
                if ($product['origin']) $specs['Origin'] = e($product['origin']);
                if ($hasBulk)           $specs['Wholesale from'] = $bulkQty . ' ' . e($product['unit']);
                $specs['Lead time']    = '24 - 72 hours';
                $specs['Last updated'] = e(date_human($product['updated_at']));
                ?>
                <dl class="mt-8 divide-y divide-ink-100 border-y border-ink-100 text-sm">
                    <?php foreach ($specs as $term => $value): ?>
                        <div class="flex items-center justify-between gap-4 py-3">
                            <dt class="text-ink-500"><?= $term ?></dt>
                            <dd class="text-right font-semibold text-navy-700"><?= $value ?></dd>
                        </div>
                    <?php endforeach; ?>
                </dl>

                <?php if ($product['description']): ?>
                    <div class="mt-8">
                        <h2 class="text-xl">Product detail</h2>
                        <p class="mt-3 leading-relaxed text-ink-500"><?= nl2br(e($product['description'])) ?></p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<?php if ($related): ?>
    <section class="section bg-white">
        <div class="shell">
            <div class="mb-10">
                <p class="eyebrow"><?= icon('package', 'size-3.5') ?>Also in <?= e($product['category_name'] ?: 'the catalogue') ?></p>
                <h2 class="h-section mt-3">Frequently ordered together</h2>
            </div>
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                <?php foreach ($related as $item): ?>
                    <?php partial('product_card', ['product' => $item]); ?>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
<?php endif; ?>

<?php partial('footer'); ?>
