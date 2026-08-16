<?php
$categories = Category::withCounts();
$activeSlug = (string) input('category', '');
$activeCat  = $activeSlug ? Category::findBySlug($activeSlug) : null;

if ($activeSlug && !$activeCat) {
    abort_404();
}

$result = Product::paginate([
    'category' => $activeSlug,
    'q'        => input('q', ''),
    'sort'     => input('sort', ''),
    'min'      => input('min', ''),
    'max'      => input('max', ''),
    'page'     => input_int('page', 1),
]);

$heading   = $activeCat ? $activeCat['name'] : 'Full catalogue';
$intro     = $activeCat['description']
    ?? 'Everything we supply, from fresh market produce and 50kg staples to reefer-only frozen lines. Fixed prices are shown, while changing market lines can be requested in one tap.';
$totalLive = (int) Db::value('SELECT COUNT(*) FROM products WHERE is_active = 1');
$hasFilters = input('q') || input('min') || input('max');

partial('header', ['title' => page_title($heading), 'description' => $intro]);
?>

<!-- Page header -->
<?php $categoryPhoto = $activeCat ? category_photo($activeCat['slug']) : null; ?>
<section class="relative isolate overflow-hidden bg-navy-800">
    <?php if ($categoryPhoto): ?>
        <img src="<?= asset($categoryPhoto) ?>" alt=""
             class="absolute inset-0 size-full object-cover opacity-25" width="900" height="600">
        <div class="absolute inset-0 bg-gradient-to-r from-navy-900 via-navy-900/90 to-navy-800/60"></div>
    <?php endif; ?>
    <div class="absolute inset-0 bg-grid opacity-30"></div>
    <div class="absolute -left-24 -top-24 size-72 rounded-full bg-orange-500/20 blur-3xl"></div>

    <div class="shell relative py-10 sm:py-14">
        <nav class="mb-4 flex flex-wrap items-center gap-1.5 text-xs text-navy-200" aria-label="Breadcrumb">
            <a class="inline-flex min-h-6 items-center transition-colors hover:text-white" href="<?= url('/') ?>">Home</a>
            <?= icon('chevron-right', 'size-3') ?>
            <?php if ($activeCat): ?>
                <a class="inline-flex min-h-6 items-center transition-colors hover:text-white" href="<?= url('/products') ?>">Catalogue</a>
                <?= icon('chevron-right', 'size-3') ?>
                <span class="text-white"><?= e($activeCat['name']) ?></span>
            <?php else: ?>
                <span class="text-white">Catalogue</span>
            <?php endif; ?>
        </nav>

        <div class="flex flex-wrap items-end justify-between gap-6">
            <div class="max-w-2xl">
                <div class="flex items-center gap-4">
                    <?php if ($activeCat): ?>
                        <span class="grid size-14 shrink-0 place-items-center rounded-2xl bg-orange-500 text-white">
                            <?= icon(category_icon($activeCat['slug']), 'size-7') ?>
                        </span>
                    <?php endif; ?>
                    <h1 class="h-section text-white"><?= e($heading) ?></h1>
                </div>
                <p class="mt-4 leading-relaxed text-navy-100"><?= e($intro) ?></p>
            </div>

            <p class="rounded-full bg-white/10 px-4 py-2 text-sm font-semibold text-white backdrop-blur">
                <?= number_format($result['total']) ?> product<?= $result['total'] === 1 ? '' : 's' ?>
            </p>
        </div>
    </div>
</section>

<section class="section-sm pb-20">
    <div class="shell">
        <div class="grid gap-8 lg:grid-cols-[17rem_minmax(0,1fr)]">

            <!-- Filters -->
            <aside class="lg:sticky lg:top-28 lg:self-start">
                <div class="card card-pad">
                    <form method="get" action="<?= url('/products') ?>" class="space-y-6">
                        <?php if ($activeSlug): ?>
                            <input type="hidden" name="category" value="<?= e($activeSlug) ?>">
                        <?php endif; ?>

                        <div>
                            <label class="label" for="q">Search</label>
                            <div class="relative">
                                <span class="pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 text-ink-400">
                                    <?= icon('search', 'size-4') ?>
                                </span>
                                <input class="input pl-10" type="search" id="q" name="q"
                                       value="<?= e(input('q', '')) ?>" placeholder="Rice, oil, SKU...">
                            </div>
                        </div>

                        <div>
                            <p class="label">Price range (<?= CURRENCY ?>)</p>
                            <div class="grid grid-cols-2 gap-2">
                                <input class="input" type="number" name="min" min="0" step="500"
                                       value="<?= e(input('min', '')) ?>" placeholder="Min" aria-label="Minimum price">
                                <input class="input" type="number" name="max" min="0" step="500"
                                       value="<?= e(input('max', '')) ?>" placeholder="Max" aria-label="Maximum price">
                            </div>
                        </div>

                        <div class="flex gap-2">
                            <button class="btn btn-primary btn-block gap-2" type="submit">
                                <?= icon('filter', 'size-4') ?>Apply
                            </button>
                            <?php if ($hasFilters): ?>
                                <a class="btn btn-ghost px-3" href="<?= url($activeSlug ? '/category/' . $activeSlug : '/products') ?>"
                                   aria-label="Clear filters"><?= icon('close', 'size-4') ?></a>
                            <?php endif; ?>
                        </div>
                    </form>

                    <div class="divider"></div>

                    <p class="label">Categories</p>
                    <ul class="-mx-2 space-y-0.5">
                        <li>
                            <a href="<?= url('/products') ?>"
                               class="flex min-h-11 items-center justify-between gap-2 rounded-lg px-2 text-sm font-medium transition-colors
                                      <?= $activeSlug === '' ? 'bg-navy-50 font-semibold text-navy-700' : 'text-ink-600 hover:bg-ink-100' ?>">
                                All products
                                <span class="text-xs text-ink-400"><?= $totalLive ?></span>
                            </a>
                        </li>
                        <?php foreach ($categories as $category): ?>
                            <li>
                                <a href="<?= url('/category/' . $category['slug']) ?>"
                                   class="flex min-h-11 items-center justify-between gap-2 rounded-lg px-2 text-sm font-medium transition-colors
                                          <?= $activeSlug === $category['slug'] ? 'bg-navy-50 font-semibold text-navy-700' : 'text-ink-600 hover:bg-ink-100' ?>">
                                    <span class="flex items-center gap-2">
                                        <?= icon(category_icon($category['slug']), 'size-4 text-ink-400') ?>
                                        <?= e($category['name']) ?>
                                    </span>
                                    <span class="text-xs text-ink-400"><?= (int) $category['product_count'] ?></span>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>

                <div class="card mt-4 overflow-hidden">
                    <div class="bg-navy-700 p-5 text-white">
                        <?= icon('receipt', 'size-6 text-orange-400') ?>
                        <h2 class="mt-3 text-base text-white">Need something else?</h2>
                        <p class="mt-1.5 text-sm text-navy-100">
                            We source outside the catalogue all the time. Send your list and we will price it.
                        </p>
                        <a class="btn btn-accent btn-block mt-4" href="<?= url('/quote') ?>">Request a quote</a>
                    </div>
                </div>
            </aside>

            <!-- Results -->
            <div>
                <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
                    <p class="text-sm text-ink-500">
                        Showing <span class="font-semibold text-navy-700"><?= count($result['rows']) ?></span>
                        of <?= number_format($result['total']) ?>
                        <?php if (input('q')): ?>
                            for &ldquo;<span class="font-semibold text-navy-700"><?= e(input('q')) ?></span>&rdquo;
                        <?php endif; ?>
                    </p>

                    <form method="get" class="flex items-center gap-2">
                        <?php foreach (['category', 'q', 'min', 'max'] as $carry): ?>
                            <?php if (input($carry)): ?>
                                <input type="hidden" name="<?= $carry ?>" value="<?= e(input($carry)) ?>">
                            <?php endif; ?>
                        <?php endforeach; ?>
                        <label class="text-sm text-ink-500" for="sort">Sort</label>
                        <select class="select w-auto min-w-44" id="sort" name="sort" data-autosubmit>
                            <?php foreach ([
                                ''           => 'Featured first',
                                'name'       => 'Name A-Z',
                                'price_asc'  => 'Price: low to high',
                                'price_desc' => 'Price: high to low',
                                'oldest'     => 'Oldest first',
                            ] as $value => $label): ?>
                                <option value="<?= $value ?>" <?= input('sort', '') === $value ? 'selected' : '' ?>><?= $label ?></option>
                            <?php endforeach; ?>
                        </select>
                        <noscript><button class="btn btn-ghost btn-sm" type="submit">Go</button></noscript>
                    </form>
                </div>

                <?php if ($result['rows']): ?>
                    <div class="grid gap-6 sm:grid-cols-2 xl:grid-cols-3">
                        <?php foreach ($result['rows'] as $product): ?>
                            <?php partial('product_card', ['product' => $product]); ?>
                        <?php endforeach; ?>
                    </div>
                    <?php partial('pagination', ['page' => $result['page'], 'pages' => $result['pages']]); ?>
                <?php else: ?>
                    <div class="card flex flex-col items-center px-6 py-16 text-center">
                        <span class="grid size-16 place-items-center rounded-2xl bg-ink-100 text-ink-400">
                            <?= icon('search', 'size-8') ?>
                        </span>
                        <h2 class="mt-5 text-xl">Nothing matched that search</h2>
                        <p class="mt-2 max-w-sm text-ink-500">
                            Try a broader term, widen the price range, or ask us to source it for you.
                        </p>
                        <div class="mt-6 flex flex-wrap justify-center gap-3">
                            <a class="btn btn-primary" href="<?= url('/products') ?>">Clear filters</a>
                            <a class="btn btn-ghost" href="<?= url('/quote') ?>">Request a quote</a>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<?php partial('footer'); ?>
