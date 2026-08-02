<?php
$totals = cart_totals();

if (!$totals['lines']) {
    flash('warn', 'Your cart is empty, so there is nothing to check out.');
    redirect('/cart');
}

$user   = auth_user();
$errors = [];

if (is_post()) {
    $data = [
        'customer_name'     => (string) input('customer_name'),
        'email'             => (string) input('email'),
        'phone'             => (string) input('phone'),
        'company'           => (string) input('company'),
        'delivery_address'  => (string) input('delivery_address'),
        'city'              => (string) input('city'),
        'state'             => (string) input('state'),
        'delivery_date'     => (string) input('delivery_date'),
        'delivery_window'   => (string) input('delivery_window'),
        'logistics_service' => (string) input('logistics_service'),
        'payment_method'    => (string) input('payment_method', 'transfer'),
        'notes'             => (string) input('notes'),
    ];

    if ($data['customer_name'] === '')                       $errors['customer_name'] = 'Tell us who to address the delivery to.';
    if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL))  $errors['email'] = 'Enter a valid email address.';
    if (strlen(preg_replace('/\D+/', '', $data['phone'])) < 10) $errors['phone'] = 'Enter a reachable phone number.';
    if ($data['delivery_address'] === '')                    $errors['delivery_address'] = 'We need a street address to deliver to.';
    if ($data['city'] === '')                                $errors['city'] = 'Which city?';
    if (!in_array($data['state'], nigerian_states(), true))  $errors['state'] = 'Choose a state.';
    if ($data['delivery_date'] !== '' && strtotime($data['delivery_date']) < strtotime('today')) {
        $errors['delivery_date'] = 'Pick today or a later date.';
    }
    if (!in_array($data['payment_method'], ['transfer', 'cash', 'terms'], true)) {
        $data['payment_method'] = 'transfer';
    }

    if (!$errors) {
        $lines = [];
        foreach ($totals['lines'] as $line) {
            $lines[] = [
                'product_id'   => (int) $line['product']['id'],
                'product_name' => $line['product']['name'],
                'unit'         => $line['product']['unit'],
                'unit_price'   => $line['unit_price'],
                'quantity'     => $line['quantity'],
            ];
        }

        try {
            $order = Order::create($data + [
                'user_id'      => auth_id(),
                'type'         => 'order',
                'delivery_fee' => $totals['delivery_fee'],
            ], $lines);

            cart_clear();
            $_SESSION['_recent_orders'][] = $order['reference'];
            flash('success', 'Order ' . $order['reference'] . ' received. We will confirm shortly.');
            redirect('/order/' . $order['reference']);
        } catch (Throwable $e) {
            $errors['_'] = APP_DEBUG ? $e->getMessage() : 'We could not place that order. Please try again.';
        }
    }

    flash_old($data);
}

/**
 * Field value: a failed submission wins, then the signed-in profile, then a fallback.
 * $field is the form name, $userField the matching column on the user row.
 */
$value = function (string $field, ?string $userField = null, string $fallback = '') use ($user) {
    $prefill = ($userField && $user && !empty($user[$userField])) ? (string) $user[$userField] : $fallback;
    return (string) old($field, $prefill);
};

/** Shorthand for the error-state class on an input. */
$bad = fn(string $field) => isset($errors[$field]) ? ' input-error' : '';

partial('header', ['title' => page_title('Checkout')]);
?>

<section class="section-sm pb-20">
    <div class="shell">

        <nav class="mb-6 flex items-center gap-1.5 text-xs text-ink-400" aria-label="Breadcrumb">
            <a class="inline-flex min-h-6 items-center transition-colors hover:text-navy-700" href="<?= url('/') ?>">Home</a>
            <?= icon('chevron-right', 'size-3') ?>
            <a class="inline-flex min-h-6 items-center transition-colors hover:text-navy-700" href="<?= url('/cart') ?>">Cart</a>
            <?= icon('chevron-right', 'size-3') ?>
            <span class="font-semibold text-navy-700">Checkout</span>
        </nav>

        <div class="mb-8">
            <h1 class="h-section">Delivery details</h1>
            <p class="mt-2 max-w-2xl text-ink-500">
                Tell us where this is going and when you want it. Payment is arranged after we confirm stock.
            </p>
        </div>

        <?php if (isset($errors['_'])): ?>
            <div class="alert alert-error"><?= icon('alert', 'size-5 shrink-0') ?><span><?= e($errors['_']) ?></span></div>
        <?php endif; ?>

        <?php if ($errors && !isset($errors['_'])): ?>
            <div class="alert alert-error" role="alert">
                <?= icon('alert', 'size-5 shrink-0') ?>
                <span>Please fix the <?= count($errors) ?> highlighted field<?= count($errors) === 1 ? '' : 's' ?> below.</span>
            </div>
        <?php endif; ?>

        <?php if (!auth_check()): ?>
            <div class="alert alert-info">
                <?= icon('info', 'size-5 shrink-0') ?>
                <span>
                    Ordering as a guest. <a class="link-quiet" href="<?= url('/login') ?>">Sign in</a> or
                    <a class="link-quiet" href="<?= url('/register') ?>">create an account</a>
                    to keep your order history and reorder in one click.
                </span>
            </div>
        <?php endif; ?>

        <form method="post" class="grid gap-8 lg:grid-cols-[minmax(0,1fr)_22rem] lg:items-start">
            <?= csrf_field() ?>

            <div class="space-y-6">

                <!-- Contact -->
                <fieldset class="card card-pad">
                    <legend class="flex items-center gap-2.5 px-1">
                        <span class="grid size-8 place-items-center rounded-lg bg-navy-700 font-display text-sm font-bold text-white">1</span>
                        <span class="font-display text-lg font-bold text-navy-700">Contact</span>
                    </legend>

                    <div class="mt-5 grid gap-x-5 sm:grid-cols-2">
                        <div class="field">
                            <label class="label" for="customer_name">Full name <span class="req">*</span></label>
                            <input class="input<?= $bad('customer_name') ?>" id="customer_name" name="customer_name"
                                   value="<?= e($value('customer_name', 'name')) ?>" autocomplete="name" required>
                            <?php if (isset($errors['customer_name'])): ?>
                                <p class="error-text"><?= icon('alert', 'size-4 shrink-0 mt-px') ?><?= e($errors['customer_name']) ?></p>
                            <?php endif; ?>
                        </div>
                        <div class="field">
                            <label class="label" for="company">Company (optional)</label>
                            <input class="input" id="company" name="company" value="<?= e($value('company', 'company')) ?>" autocomplete="organization">
                        </div>
                        <div class="field">
                            <label class="label" for="email">Email <span class="req">*</span></label>
                            <input class="input<?= $bad('email') ?>" type="email" id="email" name="email"
                                   value="<?= e($value('email', 'email')) ?>" autocomplete="email" required>
                            <?php if (isset($errors['email'])): ?>
                                <p class="error-text"><?= icon('alert', 'size-4 shrink-0 mt-px') ?><?= e($errors['email']) ?></p>
                            <?php endif; ?>
                        </div>
                        <div class="field">
                            <label class="label" for="phone">Phone <span class="req">*</span></label>
                            <input class="input<?= $bad('phone') ?>" type="tel" id="phone" name="phone"
                                   value="<?= e($value('phone', 'phone')) ?>" placeholder="0803 123 4567" autocomplete="tel" required>
                            <?php if (isset($errors['phone'])): ?>
                                <p class="error-text"><?= icon('alert', 'size-4 shrink-0 mt-px') ?><?= e($errors['phone']) ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                </fieldset>

                <!-- Delivery -->
                <fieldset class="card card-pad">
                    <legend class="flex items-center gap-2.5 px-1">
                        <span class="grid size-8 place-items-center rounded-lg bg-navy-700 font-display text-sm font-bold text-white">2</span>
                        <span class="font-display text-lg font-bold text-navy-700">Delivery</span>
                    </legend>

                    <div class="mt-5">
                        <div class="field">
                            <label class="label" for="delivery_address">Street address <span class="req">*</span></label>
                            <input class="input<?= $bad('delivery_address') ?>" id="delivery_address" name="delivery_address"
                                   value="<?= e($value('delivery_address', 'address')) ?>" autocomplete="street-address" required>
                            <?php if (isset($errors['delivery_address'])): ?>
                                <p class="error-text"><?= icon('alert', 'size-4 shrink-0 mt-px') ?><?= e($errors['delivery_address']) ?></p>
                            <?php endif; ?>
                        </div>

                        <div class="grid gap-x-5 sm:grid-cols-2">
                            <div class="field">
                                <label class="label" for="city">City / area <span class="req">*</span></label>
                                <input class="input<?= $bad('city') ?>" id="city" name="city" list="delivery-areas"
                                       value="<?= e($value('city', 'city', APP_CITY)) ?>" autocomplete="address-level2" required>
                                <datalist id="delivery-areas">
                                    <?php foreach (service_areas() as $area): ?><option value="<?= e($area) ?>"><?php endforeach; ?>
                                </datalist>
                                <?php if (isset($errors['city'])): ?>
                                    <p class="error-text"><?= icon('alert', 'size-4 shrink-0 mt-px') ?><?= e($errors['city']) ?></p>
                                <?php endif; ?>
                            </div>
                            <div class="field">
                                <label class="label" for="state">State <span class="req">*</span></label>
                                <select class="select<?= $bad('state') ?>" id="state" name="state" required>
                                    <option value="">Select a state</option>
                                    <?php foreach (nigerian_states() as $state): ?>
                                        <option value="<?= e($state) ?>" <?= $value('state', 'state', 'Delta') === $state ? 'selected' : '' ?>><?= e($state) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <?php if (isset($errors['state'])): ?>
                                    <p class="error-text"><?= icon('alert', 'size-4 shrink-0 mt-px') ?><?= e($errors['state']) ?></p>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="grid gap-x-5 sm:grid-cols-3">
                            <div class="field">
                                <label class="label" for="delivery_date">Preferred date</label>
                                <input class="input<?= $bad('delivery_date') ?>" type="date" id="delivery_date" name="delivery_date"
                                       value="<?= e(old('delivery_date', date('Y-m-d', strtotime('+2 days')))) ?>" data-min-today>
                                <?php if (isset($errors['delivery_date'])): ?>
                                    <p class="error-text"><?= icon('alert', 'size-4 shrink-0 mt-px') ?><?= e($errors['delivery_date']) ?></p>
                                <?php endif; ?>
                            </div>
                            <div class="field">
                                <label class="label" for="delivery_window">Time window</label>
                                <select class="select" id="delivery_window" name="delivery_window">
                                    <?php foreach (delivery_windows() as $window): ?>
                                        <option value="<?= e($window) ?>" <?= old('delivery_window') === $window ? 'selected' : '' ?>><?= e($window) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="field">
                                <label class="label" for="logistics_service">Service</label>
                                <select class="select" id="logistics_service" name="logistics_service">
                                    <?php foreach (logistics_services() as $service): ?>
                                        <option value="<?= e($service) ?>" <?= old('logistics_service') === $service ? 'selected' : '' ?>><?= e($service) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="field mb-0">
                            <label class="label" for="notes">Delivery notes</label>
                            <textarea class="textarea" id="notes" name="notes" rows="3"
                                      placeholder="Gate code, contact on site, offloading instructions..."><?= e(old('notes')) ?></textarea>
                        </div>
                    </div>
                </fieldset>

                <!-- Payment -->
                <fieldset class="card card-pad">
                    <legend class="flex items-center gap-2.5 px-1">
                        <span class="grid size-8 place-items-center rounded-lg bg-navy-700 font-display text-sm font-bold text-white">3</span>
                        <span class="font-display text-lg font-bold text-navy-700">Payment</span>
                    </legend>

                    <p class="mt-4 text-sm text-ink-500">
                        Nothing is charged online. We confirm stock and send an invoice with payment details.
                    </p>

                    <div class="mt-5 grid gap-3">
                        <?php foreach ([
                            'transfer' => ['banknote', 'Bank transfer', 'Pay into our account before dispatch. Details are on the invoice.'],
                            'cash'     => ['receipt', 'Cash on delivery', 'Available for ' . APP_CITY . ' orders under ' . money(500000) . '.'],
                            'terms'    => ['clock', 'Credit terms', 'For approved accounts on 14 or 30 day terms.'],
                        ] as $method => [$ico, $label, $blurb]): ?>
                            <label class="check rounded-xl border border-ink-200 p-4 transition-colors hover:border-navy-300 has-[:checked]:border-navy-500 has-[:checked]:bg-navy-50">
                                <input type="radio" name="payment_method" value="<?= $method ?>"
                                       <?= old('payment_method', 'transfer') === $method ? 'checked' : '' ?>>
                                <span class="flex-1">
                                    <span class="flex items-center gap-2 font-semibold text-navy-700">
                                        <?= icon($ico, 'size-4 text-orange-500') ?><?= $label ?>
                                    </span>
                                    <span class="mt-0.5 block text-xs text-ink-500"><?= $blurb ?></span>
                                </span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </fieldset>
            </div>

            <!-- Summary -->
            <aside class="card card-pad lg:sticky lg:top-28">
                <h2 class="text-lg">Your order</h2>

                <ul class="mt-5 space-y-3 text-sm">
                    <?php foreach ($totals['lines'] as $line): ?>
                        <li class="flex justify-between gap-3">
                            <span class="min-w-0 text-ink-500">
                                <span class="block truncate"><?= e($line['product']['name']) ?></span>
                                <span class="text-xs text-ink-400">&times; <?= (int) $line['quantity'] ?> <?= e($line['product']['unit']) ?></span>
                            </span>
                            <span class="price shrink-0 font-semibold text-navy-700"><?= money($line['line_total']) ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>

                <div class="divider"></div>

                <dl class="space-y-3 text-sm">
                    <div class="flex justify-between gap-4">
                        <dt class="text-ink-500">Subtotal</dt>
                        <dd class="price font-semibold text-navy-700"><?= money($totals['subtotal']) ?></dd>
                    </div>
                    <div class="flex justify-between gap-4">
                        <dt class="text-ink-500">Delivery</dt>
                        <dd class="font-semibold <?= $totals['delivery_fee'] > 0 ? 'text-navy-700' : 'text-emerald-600' ?>">
                            <?= $totals['delivery_fee'] > 0 ? money($totals['delivery_fee']) : 'Free' ?>
                        </dd>
                    </div>
                </dl>

                <div class="mt-4 flex items-baseline justify-between gap-4 border-t border-ink-200 pt-4">
                    <span class="font-display text-base font-bold text-navy-700">Total</span>
                    <span class="price font-display text-2xl font-extrabold text-navy-700"><?= money($totals['total']) ?></span>
                </div>

                <button class="btn btn-primary btn-block btn-lg mt-6 gap-2" type="submit">
                    <?= icon('check', 'size-5') ?>Place order
                </button>
                <a class="btn btn-ghost btn-block btn-sm mt-2" href="<?= url('/cart') ?>">Back to cart</a>

                <p class="mt-4 flex items-start gap-2 text-xs leading-relaxed text-ink-400">
                    <?= icon('shield', 'size-4 shrink-0 mt-px') ?>
                    Deliveries outside <?= e(APP_STATE) ?> are re-quoted before dispatch. You will be told before anything ships.
                </p>
            </aside>
        </form>
    </div>
</section>

<?php partial('footer'); ?>
