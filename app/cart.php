<?php
/**
 * Session cart. Stores product_id => quantity and re-reads prices from the
 * database on every render, so a price change never leaks a stale total.
 */

function cart_raw(): array
{
    return $_SESSION['cart'] ?? [];
}

function cart_save(array $cart): void
{
    $_SESSION['cart'] = array_filter($cart, fn($qty) => $qty > 0);
}

function cart_add(int $productId, int $qty = 1): void
{
    $cart = cart_raw();
    $cart[$productId] = ($cart[$productId] ?? 0) + max(1, $qty);
    cart_save($cart);
}

function cart_set(int $productId, int $qty): void
{
    $cart = cart_raw();
    if ($qty <= 0) {
        unset($cart[$productId]);
    } else {
        $cart[$productId] = $qty;
    }
    cart_save($cart);
}

function cart_remove(int $productId): void
{
    $cart = cart_raw();
    unset($cart[$productId]);
    cart_save($cart);
}

function cart_clear(): void
{
    unset($_SESSION['cart']);
}

function cart_count(): int
{
    return array_sum(cart_raw());
}

/**
 * Cart lines joined against live product rows. Products that were deleted or
 * deactivated since they were added are dropped from the session silently.
 */
function cart_lines(): array
{
    $cart = cart_raw();
    if (!$cart) {
        return [];
    }

    $products = Product::findMany(array_keys($cart));
    $lines    = [];
    $pruned   = false;

    foreach ($cart as $productId => $qty) {
        if (!isset($products[$productId])) {
            $pruned = true;
            continue;
        }
        $product = $products[$productId];
        $qty     = (int) $qty;
        $price   = Product::effectivePrice($product, $qty);

        $lines[] = [
            'product'     => $product,
            'quantity'    => $qty,
            'unit_price'  => $price,
            'line_total'  => $price * $qty,
            'is_wholesale' => Product::qualifiesForWholesale($product, $qty),
            // How many more units would unlock the wholesale rate on this line.
            'to_wholesale' => Product::hasWholesale($product) && !Product::qualifiesForWholesale($product, $qty)
                ? max(0, (int) $product['wholesale_min_qty'] - $qty)
                : 0,
        ];
    }

    if ($pruned) {
        cart_save(array_intersect_key($cart, $products));
    }
    return $lines;
}

function cart_subtotal(?array $lines = null): float
{
    $lines ??= cart_lines();
    return array_sum(array_column($lines, 'line_total'));
}

/** Free above the threshold, flat fee otherwise, nothing on an empty cart. */
function cart_delivery_fee(float $subtotal): float
{
    if ($subtotal <= 0) {
        return 0.0;
    }
    $threshold = (float) Setting::get('free_delivery_from', (string) FREE_DELIVERY_FROM);
    return $subtotal >= $threshold ? 0.0 : (float) Setting::get('delivery_fee', (string) DELIVERY_FEE);
}

function cart_totals(): array
{
    $lines    = cart_lines();
    $subtotal = cart_subtotal($lines);
    $fee      = cart_delivery_fee($subtotal);

    return [
        'lines'        => $lines,
        'subtotal'     => $subtotal,
        'delivery_fee' => $fee,
        'total'        => $subtotal + $fee,
        'count'        => cart_count(),
    ];
}
