<?php
/** @var array $params */
$product = Product::find((int) ($params['id'] ?? 0));

if (!$product) {
    flash('error', 'That product no longer exists.');
    redirect('/admin/products');
}

// Keep the row if it appears on historical orders — hide it instead, so the
// order line keeps its product link and reporting stays intact.
$onOrders = (int) Db::value('SELECT COUNT(*) FROM order_items WHERE product_id = ?', [$product['id']]);

if ($onOrders > 0) {
    Product::update((int) $product['id'], array_merge($product, ['is_active' => 0, 'is_featured' => 0]));
    flash('warn', $product['name'] . ' appears on ' . $onOrders . ' order line(s), so it was hidden rather than deleted.');
    redirect('/admin/products');
}

if (!empty($product['image']) && is_file(UPLOAD_PATH . '/' . $product['image'])) {
    @unlink(UPLOAD_PATH . '/' . $product['image']);
}

Product::delete((int) $product['id']);
flash('success', $product['name'] . ' deleted.');
redirect('/admin/products');
