<?php
$errors  = [];
$editing = null;

if (input_int('edit')) {
    $editing = Category::find(input_int('edit'));
}

if (is_post()) {
    $id   = input_int('id');
    $data = [
        'name'        => (string) input('name'),
        'description' => (string) input('description'),
        'icon'        => mb_substr((string) input('icon'), 0, 4),
        'sort_order'  => input_int('sort_order', 0),
        'is_active'   => input('is_active') ? 1 : 0,
    ];

    if ($data['name'] === '') {
        $errors['name'] = 'Give the category a name.';
    }

    if (!$errors) {
        $slug = slugify(input('slug') ?: $data['name']);

        if ($id) {
            $data['slug'] = Category::uniqueSlug($slug, $id);
            Category::update($id, $data);
            flash('success', $data['name'] . ' updated.');
        } else {
            $data['slug'] = Category::uniqueSlug($slug);
            Category::create($data);
            flash('success', $data['name'] . ' created.');
        }
        redirect('/admin/categories');
    }

    flash_old($data);
    $editing = $id ? array_merge(Category::find($id) ?? [], $data) : $data;
}

$categories = Category::withCounts(false);
$val = fn(string $key, $default = '') => old($key, $editing[$key] ?? $default);

partial('admin_header', [
    'title'    => 'Categories',
    'subtitle' => count($categories) . ' categories organise the catalogue',
]);
?>

<div class="split">
    <div class="card table-flush">
        <div class="table-wrap">
            <table class="table">
                <thead><tr><th>Category</th><th class="num">Products</th><th class="num">Order</th><th>State</th><th class="tight"></th></tr></thead>
                <tbody>
                <?php if (!$categories): ?>
                    <tr><td colspan="5" class="center muted">No categories yet. Create the first one on the right.</td></tr>
                <?php endif; ?>
                <?php foreach ($categories as $category): ?>
                    <tr>
                        <td>
                            <div class="flex-center">
                                <span class="cat-icon" style="width:34px;height:34px;font-size:.78rem"><?= e($category['icon'] ?: initials($category['name'])) ?></span>
                                <span>
                                    <span class="cell-title"><?= e($category['name']) ?></span>
                                    <div class="cell-sub mono"><?= e($category['slug']) ?></div>
                                </span>
                            </div>
                        </td>
                        <td class="num"><?= (int) $category['product_count'] ?></td>
                        <td class="num"><?= (int) $category['sort_order'] ?></td>
                        <td>
                            <span class="badge badge-<?= (int) $category['is_active'] ? 'success' : 'muted' ?> badge-dot">
                                <?= (int) $category['is_active'] ? 'Live' : 'Hidden' ?>
                            </span>
                        </td>
                        <td class="tight">
                            <div class="flex gap-sm">
                                <a class="btn btn-ghost btn-sm" href="<?= url('/admin/categories') ?>?edit=<?= (int) $category['id'] ?>">Edit</a>
                                <form method="post" action="<?= url('/admin/categories/' . $category['id'] . '/delete') ?>"
                                      data-confirm="Delete <?= e($category['name']) ?>? Its products become uncategorised.">
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

    <div class="card card-pad">
        <h3><?= $editing && !empty($editing['id']) ? 'Edit category' : 'New category' ?></h3>

        <form method="post">
            <?= csrf_field() ?>
            <?php if ($editing && !empty($editing['id'])): ?>
                <input type="hidden" name="id" value="<?= (int) $editing['id'] ?>">
            <?php endif; ?>

            <div class="field">
                <label for="name">Name <span class="required">*</span></label>
                <input class="input <?= isset($errors['name']) ? 'has-error' : '' ?>" id="name" name="name" value="<?= e($val('name')) ?>" required>
                <?php if (isset($errors['name'])): ?><p class="error-text"><?= e($errors['name']) ?></p><?php endif; ?>
            </div>

            <div class="field-row">
                <div class="field">
                    <label for="slug">Slug</label>
                    <input class="input" id="slug" name="slug" value="<?= e($val('slug')) ?>" placeholder="auto">
                </div>
                <div class="field">
                    <label for="icon">Tile initials</label>
                    <input class="input" id="icon" name="icon" maxlength="4" value="<?= e($val('icon')) ?>" placeholder="GS">
                </div>
            </div>

            <div class="field">
                <label for="description">Description</label>
                <textarea class="textarea" id="description" name="description" rows="3" maxlength="400"><?= e($val('description')) ?></textarea>
            </div>

            <div class="field">
                <label for="sort_order">Sort order</label>
                <input class="input" type="number" id="sort_order" name="sort_order" value="<?= e($val('sort_order', '0')) ?>">
                <p class="hint">Lower numbers appear first in menus.</p>
            </div>

            <label class="check mb-3">
                <input type="checkbox" name="is_active" value="1" <?= $val('is_active', 1) ? 'checked' : '' ?>>
                <span>Show this category on the storefront</span>
            </label>

            <button class="btn btn-primary btn-block" type="submit">
                <?= $editing && !empty($editing['id']) ? 'Save category' : 'Create category' ?>
            </button>
            <?php if ($editing && !empty($editing['id'])): ?>
                <a class="btn btn-ghost btn-block btn-sm mt-1" href="<?= url('/admin/categories') ?>">Cancel edit</a>
            <?php endif; ?>
        </form>
    </div>
</div>

<?php partial('admin_footer'); ?>
