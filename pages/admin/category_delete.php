<?php
/** @var array $params */
$category = Category::find((int) ($params['id'] ?? 0));

if (!$category) {
    flash('error', 'That category no longer exists.');
    redirect('/admin/categories');
}

// Products keep their rows; the foreign key nulls their category_id.
Category::delete((int) $category['id']);
flash('success', $category['name'] . ' deleted. Its products are now uncategorised.');
redirect('/admin/categories');
