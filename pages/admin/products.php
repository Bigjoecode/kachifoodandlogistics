<?php
$categories = Category::all(false);
$result = Product::paginate([
    'q'                => (string) input('q', ''),
    'category_id'      => input_int('category_id', 0),
    'stock'            => (string) input('stock', ''),
    'sort'             => (string) input('sort', ''),
    'page'             => input_int('page', 1),
    'per_page'         => 20,
    'include_inactive' => true,
]);

partial('admin_header', [
    'title'    => 'Products',
    'subtitle' => number_format($result['total']) . ' product' . ($result['total'] === 1 ? '' : 's') . ' in the catalogue',
    'actions'  => '<a class="btn btn-primary btn-sm" href="' . url('/admin/products/new') . '">Add product</a>',
]);
?>

<form class="data-filters" method="get">
    <div class="field">
        <label for="q">Search</label>
        <input class="input" id="q" name="q" value="<?= e(input('q', '')) ?>" placeholder="Name, SKU, origin">
    </div>
    <div class="field">
        <label for="category_id">Category</label>
        <select class="select" id="category_id" name="category_id">
            <option value="">All categories</option>
            <?php foreach ($categories as $category): ?>
                <option value="<?= (int) $category['id'] ?>" <?= input_int('category_id') === (int) $category['id'] ? 'selected' : '' ?>>
                    <?= e($category['name']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="field">
        <label for="stock">Stock</label>
        <select class="select" id="stock" name="stock">
            <option value="">Any</option>
            <option value="in"  <?= input('stock') === 'in' ? 'selected' : '' ?>>In stock</option>
            <option value="out" <?= input('stock') === 'out' ? 'selected' : '' ?>>Out of stock</option>
        </select>
    </div>
    <div class="field">
        <label for="sort">Sort</label>
        <select class="select" id="sort" name="sort">
            <option value="">Newest first</option>
            <option value="name"       <?= input('sort') === 'name' ? 'selected' : '' ?>>Name A-Z</option>
            <option value="price_desc" <?= input('sort') === 'price_desc' ? 'selected' : '' ?>>Price high to low</option>
            <option value="stock"      <?= input('sort') === 'stock' ? 'selected' : '' ?>>Lowest stock</option>
        </select>
    </div>
    <div class="flex gap-sm">
        <button class="btn btn-primary" type="submit">Filter</button>
        <a class="btn btn-ghost" href="<?= url('/admin/products') ?>">Reset</a>
    </div>
</form>

<?php if (!$result['rows']): ?>
    <div class="empty">
        <div class="mark">0</div>
        <h3>No products match</h3>
        <p>Adjust the filters, or add a new product to the catalogue.</p>
        <a class="btn btn-primary" href="<?= url('/admin/products/new') ?>">Add product</a>
    </div>
<?php else: ?>
    <div class="card table-flush">
        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr><th>Product</th><th>Category</th><th class="num">Retail</th><th class="num">Wholesale</th>
                        <th class="num">Stock</th><th>State</th><th class="tight"></th></tr>
                </thead>
                <tbody>
                <?php foreach ($result['rows'] as $product): ?>
                    <tr>
                        <td>
                            <a class="cell-title" href="<?= url('/admin/products/' . $product['id'] . '/edit') ?>"><?= e($product['name']) ?></a>
                            <div class="cell-sub">
                                <?= e($product['sku'] ?: 'no SKU') ?> &middot; per <?= e($product['unit']) ?>
                                <?php if ($product['origin']): ?> &middot; <?= e($product['origin']) ?><?php endif; ?>
                            </div>
                        </td>
                        <td class="small"><?= e($product['category_name'] ?: 'Uncategorised') ?></td>
                        <td class="num">
                            <span class="strong"><?= money(Product::retailPrice($product)) ?></span>
                            <?php if (Product::isOnSale($product)): ?>
                                <div class="cell-sub"><s><?= money($product['retail_price']) ?></s></div>
                            <?php endif; ?>
                        </td>
                        <td class="num">
                            <?php if (Product::hasWholesale($product)): ?>
                                <span class="strong"><?= money($product['wholesale_price']) ?></span>
                                <div class="cell-sub">from <?= (int) $product['wholesale_min_qty'] ?></div>
                            <?php else: ?>
                                <span class="muted">&mdash;</span>
                            <?php endif; ?>
                        </td>
                        <td class="num">
                            <span class="badge badge-<?= (int) $product['stock_qty'] === 0 ? 'danger' : ((int) $product['stock_qty'] < 60 ? 'warn' : 'muted') ?>">
                                <?= number_format((int) $product['stock_qty']) ?>
                            </span>
                        </td>
                        <td>
                            <?php if ((int) $product['is_active']): ?>
                                <span class="badge badge-success badge-dot">Live</span>
                            <?php else: ?>
                                <span class="badge badge-muted badge-dot">Hidden</span>
                            <?php endif; ?>
                            <?php if ((int) $product['is_featured']): ?><span class="badge badge-accent">Featured</span><?php endif; ?>
                        </td>
                        <td class="tight">
                            <div class="flex gap-sm">
                                <a class="btn btn-ghost btn-sm" href="<?= url('/admin/products/' . $product['id'] . '/edit') ?>">Edit</a>
                                <form method="post" action="<?= url('/admin/products/' . $product['id'] . '/delete') ?>"
                                      data-confirm="Delete <?= e($product['name']) ?>? This cannot be undone.">
                                    <?= csrf_field() ?>
                                    <button class="btn btn-ghost btn-sm" type="submit" style="color:var(--danger-500)">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php partial('pagination', ['page' => $result['page'], 'pages' => $result['pages']]); ?>
<?php endif; ?>

<?php partial('admin_footer'); ?>
