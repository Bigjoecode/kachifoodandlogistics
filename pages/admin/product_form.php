<?php
/** @var array $params */
$isEdit  = isset($params['id']);
$product = $isEdit ? Product::find((int) $params['id']) : null;

if ($isEdit && !$product) {
    abort_404();
}

$categories = Category::all(false);
$errors     = [];

/** Store an uploaded image and return its filename, or null. */
function store_product_image(array $file, ?string &$error): ?string
{
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return null;
    }
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $error = 'The image failed to upload. Try a smaller file.';
        return null;
    }
    if ($file['size'] > 3 * 1024 * 1024) {
        $error = 'Images must be under 3MB.';
        return null;
    }

    $mime      = @getimagesize($file['tmp_name'])['mime'] ?? '';
    $allowed   = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', 'image/gif' => 'gif'];
    if (!isset($allowed[$mime])) {
        $error = 'Use a JPG, PNG, WebP or GIF image.';
        return null;
    }

    if (!is_dir(UPLOAD_PATH) && !mkdir(UPLOAD_PATH, 0775, true) && !is_dir(UPLOAD_PATH)) {
        $error = 'The upload folder could not be created.';
        return null;
    }

    $filename = bin2hex(random_bytes(8)) . '.' . $allowed[$mime];
    if (!move_uploaded_file($file['tmp_name'], UPLOAD_PATH . '/' . $filename)) {
        $error = 'The image could not be saved.';
        return null;
    }
    return $filename;
}

if (is_post()) {
    $data = [
        'name'        => (string) input('name'),
        'category_id' => input_int('category_id'),
        'sku'         => (string) input('sku'),
        'summary'     => (string) input('summary'),
        'description' => (string) input('description'),
        'origin'      => (string) input('origin'),
        'unit'              => (string) input('unit'),
        'retail_price'      => input_float('retail_price'),
        'wholesale_price'   => input('wholesale_price') === '' ? null : input_float('wholesale_price'),
        'wholesale_min_qty' => input_int('wholesale_min_qty', 10),
        'sale_price'        => input('sale_price') === '' ? null : input_float('sale_price'),
        'min_order'   => input_int('min_order', 1),
        'stock_qty'   => input_int('stock_qty', 0),
        'is_featured' => input('is_featured') ? 1 : 0,
        'is_active'   => input('is_active') ? 1 : 0,
        'image'       => $product['image'] ?? null,
    ];

    if ($data['name'] === '')          $errors['name'] = 'Give the product a name.';
    if ($data['unit'] === '')          $errors['unit'] = 'What is it sold by? Bag, carton, crate...';
    if ($data['retail_price'] <= 0)    $errors['retail_price'] = 'Set a retail price above zero.';
    if ($data['sale_price'] !== null && $data['sale_price'] >= $data['retail_price']) {
        $errors['sale_price'] = 'The sale price must be lower than the retail price.';
    }
    if ($data['wholesale_price'] !== null && $data['wholesale_price'] >= $data['retail_price']) {
        $errors['wholesale_price'] = 'The wholesale price must be lower than the retail price.';
    }

    $uploadError = null;
    $uploaded    = store_product_image($_FILES['image'] ?? [], $uploadError);
    if ($uploadError) {
        $errors['image'] = $uploadError;
    } elseif ($uploaded) {
        $data['image'] = $uploaded;
    }
    if (input('remove_image')) {
        $data['image'] = null;
    }

    if (!$errors) {
        $slug = slugify(input('slug') ?: $data['name']);

        if ($isEdit) {
            $data['slug'] = Product::uniqueSlug($slug, (int) $product['id']);
            Product::update((int) $product['id'], $data);
            flash('success', $data['name'] . ' updated.');
            redirect('/admin/products/' . $product['id'] . '/edit');
        }

        $data['slug'] = Product::uniqueSlug($slug);
        $newId = Product::create($data);
        flash('success', $data['name'] . ' added to the catalogue.');
        redirect('/admin/products/' . $newId . '/edit');
    }

    flash_old($data);
    $product = array_merge($product ?? [], $data);
}

/** Current field value: failed submission first, then the stored row. */
$val = fn(string $key, $default = '') => old($key, $product[$key] ?? $default);

partial('admin_header', [
    'title'    => $isEdit ? 'Edit product' : 'New product',
    'subtitle' => $isEdit ? $product['name'] : 'Add a line to the catalogue',
    'actions'  => '<a class="btn btn-ghost btn-sm" href="' . url('/admin/products') . '">Back to products</a>'
        . ($isEdit && !empty($product['slug'])
            ? '<a class="btn btn-ghost btn-sm" href="' . url('/products/' . $product['slug']) . '" target="_blank" rel="noopener">View on site</a>'
            : ''),
]);
?>

<form method="post" enctype="multipart/form-data" class="split">
    <?= csrf_field() ?>

    <div>
        <div class="card card-pad mb-3">
            <h3>Basics</h3>
            <div class="field">
                <label for="name">Product name <span class="required">*</span></label>
                <input class="input <?= isset($errors['name']) ? 'has-error' : '' ?>" id="name" name="name" value="<?= e($val('name')) ?>" required>
                <?php if (isset($errors['name'])): ?><p class="error-text"><?= e($errors['name']) ?></p><?php endif; ?>
            </div>

            <div class="field-row">
                <div class="field">
                    <label for="slug">URL slug</label>
                    <input class="input" id="slug" name="slug" value="<?= e($val('slug')) ?>" placeholder="Left blank, we generate one">
                </div>
                <div class="field">
                    <label for="sku">SKU</label>
                    <input class="input" id="sku" name="sku" value="<?= e($val('sku')) ?>" placeholder="KFL-GR-001">
                </div>
            </div>

            <div class="field">
                <label for="summary">Short summary</label>
                <input class="input" id="summary" name="summary" value="<?= e($val('summary')) ?>" maxlength="400"
                       placeholder="One line shown on catalogue cards">
            </div>

            <div class="field mb-0">
                <label for="description">Full description</label>
                <textarea class="textarea" id="description" name="description" rows="7"><?= e($val('description')) ?></textarea>
            </div>
        </div>

        <div class="card card-pad">
            <h3>Pricing and stock</h3>
            <div class="field-row-3">
                <div class="field">
                    <label for="retail_price">Retail price (<?= CURRENCY ?>) <span class="required">*</span></label>
                    <input class="input <?= isset($errors['retail_price']) ? 'has-error' : '' ?>" type="number" step="0.01" min="0"
                           id="retail_price" name="retail_price" value="<?= e($val('retail_price', '0')) ?>" required>
                    <?php if (isset($errors['retail_price'])): ?><p class="error-text"><?= e($errors['retail_price']) ?></p><?php endif; ?>
                </div>
                <div class="field">
                    <label for="sale_price">Sale price (<?= CURRENCY ?>)</label>
                    <input class="input <?= isset($errors['sale_price']) ? 'has-error' : '' ?>" type="number" step="0.01" min="0"
                           id="sale_price" name="sale_price" value="<?= e($val('sale_price')) ?>">
                    <?php if (isset($errors['sale_price'])): ?><p class="error-text"><?= e($errors['sale_price']) ?></p>
                    <?php else: ?><p class="hint">Optional promo price below retail.</p><?php endif; ?>
                </div>
                <div class="field">
                    <label for="unit">Sold by <span class="required">*</span></label>
                    <input class="input <?= isset($errors['unit']) ? 'has-error' : '' ?>" id="unit" name="unit"
                           value="<?= e($val('unit', 'bag')) ?>" placeholder="50kg bag" required>
                    <?php if (isset($errors['unit'])): ?><p class="error-text"><?= e($errors['unit']) ?></p><?php endif; ?>
                </div>
            </div>

            <div class="field-row">
                <div class="field">
                    <label for="wholesale_price">Wholesale price (<?= CURRENCY ?>)</label>
                    <input class="input <?= isset($errors['wholesale_price']) ? 'has-error' : '' ?>" type="number" step="0.01" min="0"
                           id="wholesale_price" name="wholesale_price" value="<?= e($val('wholesale_price')) ?>">
                    <?php if (isset($errors['wholesale_price'])): ?><p class="error-text"><?= e($errors['wholesale_price']) ?></p>
                    <?php else: ?><p class="hint">Leave blank if this line has no bulk rate.</p><?php endif; ?>
                </div>
                <div class="field">
                    <label for="wholesale_min_qty">Wholesale applies from</label>
                    <input class="input" type="number" min="1" id="wholesale_min_qty" name="wholesale_min_qty"
                           value="<?= e($val('wholesale_min_qty', '10')) ?>">
                    <p class="hint">Quantity at which the cart switches to the wholesale rate.</p>
                </div>
            </div>

            <div class="field-row-3 mb-0">
                <div class="field">
                    <label for="stock_qty">Stock quantity</label>
                    <input class="input" type="number" min="0" id="stock_qty" name="stock_qty" value="<?= e($val('stock_qty', '0')) ?>">
                </div>
                <div class="field">
                    <label for="min_order">Minimum order</label>
                    <input class="input" type="number" min="1" id="min_order" name="min_order" value="<?= e($val('min_order', '1')) ?>">
                </div>
                <div class="field">
                    <label for="origin">Origin</label>
                    <input class="input" id="origin" name="origin" value="<?= e($val('origin')) ?>" placeholder="Kebbi State">
                </div>
            </div>
        </div>
    </div>

    <aside>
        <div class="card card-pad mb-3">
            <h4>Visibility</h4>
            <label class="check mb-2">
                <input type="checkbox" name="is_active" value="1" <?= $val('is_active', 1) ? 'checked' : '' ?>>
                <span><span class="strong">Live on the storefront</span><br><span class="small muted">Uncheck to hide without deleting.</span></span>
            </label>
            <label class="check">
                <input type="checkbox" name="is_featured" value="1" <?= $val('is_featured', 0) ? 'checked' : '' ?>>
                <span><span class="strong">Feature on the home page</span><br><span class="small muted">Shown in the featured lines row.</span></span>
            </label>

            <div class="field mt-3 mb-0">
                <label for="category_id">Category</label>
                <select class="select" id="category_id" name="category_id">
                    <option value="">Uncategorised</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?= (int) $category['id'] ?>" <?= (int) $val('category_id') === (int) $category['id'] ? 'selected' : '' ?>>
                            <?= e($category['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="card card-pad mb-3">
            <h4>Image</h4>
            <?php if (!empty($product['image'])): ?>
                <div class="thumb mb-2" style="border-radius:var(--radius)">
                    <img src="<?= e(UPLOAD_URL . '/' . $product['image']) ?>" alt="">
                </div>
                <label class="check mb-2">
                    <input type="checkbox" name="remove_image" value="1">
                    <span class="small">Remove this image</span>
                </label>
            <?php else: ?>
                <p class="small muted">No image yet. Cards fall back to a generated tile with the product initials.</p>
            <?php endif; ?>

            <div class="field mb-0">
                <label for="image">Upload a new image</label>
                <input class="input <?= isset($errors['image']) ? 'has-error' : '' ?>" type="file" id="image" name="image" accept="image/*">
                <?php if (isset($errors['image'])): ?><p class="error-text"><?= e($errors['image']) ?></p>
                <?php else: ?><p class="hint">JPG, PNG, WebP or GIF. Max 3MB.</p><?php endif; ?>
            </div>
        </div>

        <div class="card card-pad">
            <button class="btn btn-primary btn-block" type="submit"><?= $isEdit ? 'Save changes' : 'Create product' ?></button>
            <a class="btn btn-ghost btn-block btn-sm mt-1" href="<?= url('/admin/products') ?>">Cancel</a>
        </div>
    </aside>
</form>

<?php partial('admin_footer'); ?>
