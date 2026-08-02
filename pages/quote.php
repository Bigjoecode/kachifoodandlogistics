<?php
/**
 * Bulk quote request. Lines are free text because most of these asks are for
 * volumes or items that are not on the public catalogue.
 */
$user     = auth_user();
$errors   = [];
$rowCount = 6;

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
        'notes'             => (string) input('notes'),
    ];

    $names = $_POST['item_name'] ?? [];
    $qtys  = $_POST['item_qty'] ?? [];
    $units = $_POST['item_unit'] ?? [];

    $lines = [];
    foreach ($names as $i => $name) {
        $name = trim((string) $name);
        if ($name === '') {
            continue;
        }
        $lines[] = [
            'product_id'   => null,
            'product_name' => mb_substr($name, 0, 160),
            'unit'         => trim((string) ($units[$i] ?? '')) ?: 'unit',
            'unit_price'   => 0,
            'quantity'     => max(1, (int) ($qtys[$i] ?? 1)),
        ];
    }

    if ($data['customer_name'] === '')                          $errors['customer_name'] = 'We need a name for the quote.';
    if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL))     $errors['email'] = 'Enter a valid email address.';
    if (strlen(preg_replace('/\D+/', '', $data['phone'])) < 10) $errors['phone'] = 'Enter a reachable phone number.';
    if ($data['city'] === '')                                   $errors['city'] = 'Where is this going?';
    if (!in_array($data['state'], nigerian_states(), true))     $errors['state'] = 'Choose a state.';
    if (!$lines)                                                $errors['items'] = 'Add at least one item to quote.';

    if (!$errors) {
        try {
            $order = Order::create($data + [
                'user_id'          => auth_id(),
                'type'             => 'quote',
                'delivery_fee'     => 0,
                'payment_method'   => 'transfer',
                'delivery_address' => $data['delivery_address'] ?: 'To be confirmed',
            ], $lines);

            $_SESSION['_recent_orders'][] = $order['reference'];
            flash('success', 'Quote request ' . $order['reference'] . ' received.');
            redirect('/order/' . $order['reference']);
        } catch (Throwable $e) {
            $errors['_'] = APP_DEBUG ? $e->getMessage() : 'We could not submit that request. Please try again.';
        }
    }

    flash_old($data);
    $rowCount = max($rowCount, count($names));
}

$postedNames = $_POST['item_name'] ?? [];
$postedQtys  = $_POST['item_qty'] ?? [];
$postedUnits = $_POST['item_unit'] ?? [];

$value = function (string $field, ?string $userField = null, string $fallback = '') use ($user) {
    $prefill = ($userField && $user && !empty($user[$userField])) ? (string) $user[$userField] : $fallback;
    return (string) old($field, $prefill);
};
$bad = fn(string $field) => isset($errors[$field]) ? ' input-error' : '';

partial('header', [
    'title'       => page_title('Request a bulk quote'),
    'description' => 'Send us your bulk food list and delivery location. We come back with wholesale pricing, lead times and a delivery plan.',
]);
?>

<section class="relative isolate overflow-hidden bg-navy-800">
    <div class="absolute inset-0 bg-grid opacity-30"></div>
    <div class="absolute -left-24 -top-24 size-72 rounded-full bg-orange-500/20 blur-3xl"></div>

    <div class="shell relative py-12 sm:py-16">
        <nav class="mb-5 flex items-center gap-1.5 text-xs text-navy-200" aria-label="Breadcrumb">
            <a class="inline-flex min-h-6 items-center transition-colors hover:text-white" href="<?= url('/') ?>">Home</a>
            <?= icon('chevron-right', 'size-3') ?>
            <span class="text-white">Request a quote</span>
        </nav>
        <div class="max-w-2xl">
            <p class="eyebrow eyebrow-light"><?= icon('receipt', 'size-3.5') ?>Bulk &amp; corporate</p>
            <h1 class="h-section mt-4 text-white">Request a bulk quote</h1>
            <p class="mt-4 leading-relaxed text-navy-100">
                Ordering by the tonne, supplying an event, or need something we do not list?
                Send the list and we will come back with wholesale pricing, lead times and a delivery plan.
            </p>
        </div>
    </div>
</section>

<section class="section-sm pb-20">
    <div class="shell">
        <?php if (isset($errors['_'])): ?>
            <div class="alert alert-error"><?= icon('alert', 'size-5 shrink-0') ?><span><?= e($errors['_']) ?></span></div>
        <?php endif; ?>

        <form method="post" class="grid gap-8 lg:grid-cols-[minmax(0,1fr)_22rem] lg:items-start">
            <?= csrf_field() ?>

            <div class="space-y-6">

                <!-- Items -->
                <fieldset class="card card-pad">
                    <legend class="flex items-center gap-2.5 px-1">
                        <span class="grid size-8 place-items-center rounded-lg bg-navy-700 font-display text-sm font-bold text-white">1</span>
                        <span class="font-display text-lg font-bold text-navy-700">What do you need?</span>
                    </legend>

                    <?php if (isset($errors['items'])): ?>
                        <div class="alert alert-error mt-4" role="alert">
                            <?= icon('alert', 'size-5 shrink-0') ?><span><?= e($errors['items']) ?></span>
                        </div>
                    <?php endif; ?>

                    <div class="mt-5 space-y-3">
                        <div class="hidden gap-3 px-1 text-xs font-bold uppercase tracking-wider text-ink-400 sm:grid sm:grid-cols-[minmax(0,1fr)_7rem_8rem]">
                            <span>Item</span><span>Quantity</span><span>Unit</span>
                        </div>

                        <?php for ($i = 0; $i < $rowCount; $i++): ?>
                            <div class="grid gap-3 sm:grid-cols-[minmax(0,1fr)_7rem_8rem]">
                                <input class="input" name="item_name[]" value="<?= e($postedNames[$i] ?? '') ?>"
                                       aria-label="Item <?= $i + 1 ?> name"
                                       placeholder="<?= $i === 0 ? 'e.g. Long grain rice, 50kg bags' : '' ?>">
                                <input class="input" type="number" min="1" name="item_qty[]" inputmode="numeric"
                                       aria-label="Item <?= $i + 1 ?> quantity"
                                       value="<?= e($postedQtys[$i] ?? '') ?>" placeholder="10">
                                <input class="input" name="item_unit[]" value="<?= e($postedUnits[$i] ?? '') ?>"
                                       aria-label="Item <?= $i + 1 ?> unit" placeholder="bag">
                            </div>
                        <?php endfor; ?>
                    </div>

                    <p class="mt-4 flex items-start gap-2 text-xs leading-relaxed text-ink-400">
                        <?= icon('info', 'size-4 shrink-0 mt-px') ?>
                        Leave rows blank if you do not need them. Attachments can follow by email.
                    </p>
                </fieldset>

                <!-- Contact -->
                <fieldset class="card card-pad">
                    <legend class="flex items-center gap-2.5 px-1">
                        <span class="grid size-8 place-items-center rounded-lg bg-navy-700 font-display text-sm font-bold text-white">2</span>
                        <span class="font-display text-lg font-bold text-navy-700">Who are we quoting?</span>
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
                            <label class="label" for="company">Company or organisation</label>
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
                        <div class="field mb-0">
                            <label class="label" for="phone">Phone <span class="req">*</span></label>
                            <input class="input<?= $bad('phone') ?>" type="tel" id="phone" name="phone"
                                   value="<?= e($value('phone', 'phone')) ?>" autocomplete="tel" required>
                            <?php if (isset($errors['phone'])): ?>
                                <p class="error-text"><?= icon('alert', 'size-4 shrink-0 mt-px') ?><?= e($errors['phone']) ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                </fieldset>

                <!-- Where and when -->
                <fieldset class="card card-pad">
                    <legend class="flex items-center gap-2.5 px-1">
                        <span class="grid size-8 place-items-center rounded-lg bg-navy-700 font-display text-sm font-bold text-white">3</span>
                        <span class="font-display text-lg font-bold text-navy-700">Where and when</span>
                    </legend>

                    <div class="mt-5">
                        <div class="field">
                            <label class="label" for="delivery_address">Delivery address</label>
                            <input class="input" id="delivery_address" name="delivery_address"
                                   value="<?= e($value('delivery_address', 'address')) ?>" autocomplete="street-address">
                        </div>

                        <div class="grid gap-x-5 sm:grid-cols-2">
                            <div class="field">
                                <label class="label" for="city">City / area <span class="req">*</span></label>
                                <input class="input<?= $bad('city') ?>" id="city" name="city" list="quote-areas"
                                       value="<?= e($value('city', 'city', APP_CITY)) ?>" required>
                                <datalist id="quote-areas">
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

                        <div class="grid gap-x-5 sm:grid-cols-2">
                            <div class="field">
                                <label class="label" for="delivery_date">Needed by</label>
                                <input class="input" type="date" id="delivery_date" name="delivery_date"
                                       value="<?= e(old('delivery_date')) ?>" data-min-today>
                            </div>
                            <div class="field">
                                <label class="label" for="logistics_service">Service needed</label>
                                <select class="select" id="logistics_service" name="logistics_service">
                                    <?php foreach (logistics_services() as $service): ?>
                                        <option value="<?= e($service) ?>" <?= old('logistics_service') === $service ? 'selected' : '' ?>><?= e($service) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="field mb-0">
                            <label class="label" for="notes">Anything else we should know?</label>
                            <textarea class="textarea" id="notes" name="notes" rows="4"
                                      placeholder="Delivery frequency, packaging requirements, certification, payment terms..."><?= e(old('notes')) ?></textarea>
                        </div>
                    </div>
                </fieldset>
            </div>

            <!-- Aside -->
            <aside class="lg:sticky lg:top-28">
                <div class="card card-pad">
                    <h2 class="text-lg">What happens next</h2>
                    <ol class="mt-5 space-y-4">
                        <?php foreach ([
                            'We confirm receipt the same working day.',
                            'Sourcing checks availability and current mill pricing.',
                            'You get a written quote with lead times and delivery cost.',
                            'Approve it and we convert the quote straight into an order.',
                        ] as $i => $step): ?>
                            <li class="flex gap-3">
                                <span class="grid size-7 shrink-0 place-items-center rounded-lg bg-navy-50 font-display text-xs font-bold text-navy-700">
                                    <?= $i + 1 ?>
                                </span>
                                <span class="text-sm leading-relaxed text-ink-600"><?= $step ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ol>

                    <button class="btn btn-primary btn-block btn-lg mt-6 gap-2" type="submit">
                        <?= icon('receipt', 'size-5') ?>Send quote request
                    </button>
                </div>

                <div class="card card-pad mt-4">
                    <h2 class="text-base">Prefer to talk?</h2>
                    <p class="mt-1.5 text-sm text-ink-500">
                        Our sales desk is open <?= e(Setting::get('opening_hours', 'Mon - Sat')) ?>.
                    </p>
                    <div class="mt-4 space-y-2 text-sm">
                        <a class="flex min-h-11 items-center gap-2.5 font-semibold text-navy-700 transition-colors hover:text-orange-600"
                           href="tel:<?= e(preg_replace('/[^0-9+]/', '', Setting::get('contact_phone', APP_PHONE))) ?>">
                            <?= icon('phone-call', 'size-4 text-orange-500') ?><?= e(Setting::get('contact_phone', APP_PHONE)) ?>
                        </a>
                        <a class="flex min-h-11 items-center gap-2.5 break-all text-ink-600 transition-colors hover:text-orange-600"
                           href="mailto:<?= e(Setting::get('contact_email', APP_EMAIL)) ?>">
                            <?= icon('mail', 'size-4 text-orange-500') ?><?= e(Setting::get('contact_email', APP_EMAIL)) ?>
                        </a>
                    </div>
                </div>
            </aside>
        </form>
    </div>
</section>

<?php partial('footer'); ?>
