<?php
/** POST handlers for /cart/add, /cart/update, /cart/remove and /cart/clear. */

switch (current_path()) {
    case '/cart/add':
        $productId = input_int('product_id');
        $product   = $productId ? Product::find($productId) : null;

        if (!$product || !(int) $product['is_active']) {
            flash('error', 'That product is no longer available.');
            back();
        }

        $quantity = max(1, input_int('quantity', 1));
        $minOrder = max(1, (int) $product['min_order']);
        if ($quantity < $minOrder) {
            $quantity = $minOrder;
            flash('info', 'Minimum order for ' . $product['name'] . ' is ' . $minOrder . ' ' . $product['unit'] . '.');
        }

        cart_add($productId, $quantity);
        flash('success', $product['name'] . ' added to your cart.');
        back();
        // no break — back() exits

    case '/cart/update':
        $productId = input_int('product_id');
        $quantity  = input_int('quantity', 0);

        if ($productId) {
            cart_set($productId, max(0, $quantity));
            flash('success', $quantity > 0 ? 'Cart updated.' : 'Item removed from your cart.');
        }
        redirect('/cart');

    case '/cart/remove':
        cart_remove(input_int('product_id'));
        flash('success', 'Item removed from your cart.');
        redirect('/cart');

    case '/cart/clear':
        cart_clear();
        flash('success', 'Your cart is now empty.');
        redirect('/cart');
}

redirect('/cart');
