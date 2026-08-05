<?php
/**
 * Standalone logistics booking. Customers can hire a vehicle without ordering
 * any food. The estimator runs client-side for instant feedback and is
 * recalculated server-side on submit, which is the figure we actually store.
 */
$user     = auth_user();
$errors   = [];
$vehicles = Booking::vehicleTypes();

if (is_post()) {
    $data = [
        'customer_name'       => (string) input('customer_name'),
        'email'               => (string) input('email'),
        'phone'               => (string) input('phone'),
        'company'             => (string) input('company'),
        'service_type'        => (string) input('service_type'),
        'vehicle_type'        => (string) input('vehicle_type'),
        'pickup_address'      => (string) input('pickup_address'),
        'pickup_city'         => (string) input('pickup_city'),
        'destination_address' => (string) input('destination_address'),
        'destination_city'    => (string) input('destination_city'),
        'pickup_date'         => (string) input('pickup_date'),
        'pickup_time'         => (string) input('pickup_time'),
        'distance_band'       => (string) input('distance_band'),
        'weight_kg'           => input_int('weight_kg', 0),
        'urgency'             => (string) input('urgency'),
        'needs_labour'        => input('needs_labour') ? 1 : 0,
        'description'         => (string) input('description'),
        'instructions'        => (string) input('instructions'),
    ];

    if ($data['customer_name'] === '')                              $errors['customer_name'] = 'We need a name for the booking.';
    if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL))         $errors['email'] = 'Enter a valid email address.';
    if (strlen(preg_replace('/\D+/', '', $data['phone'])) < 10)     $errors['phone'] = 'Enter a reachable phone number.';
    if (!in_array($data['service_type'], Booking::serviceTypes(), true))          $errors['service_type'] = 'Choose a service.';
    if (!array_key_exists($data['vehicle_type'], $vehicles))                      $errors['vehicle_type'] = 'Choose a vehicle.';
    if (!array_key_exists($data['distance_band'], Booking::distanceBands()))      $errors['distance_band'] = 'Choose a distance band.';
    if (!array_key_exists($data['urgency'], Booking::urgencyLevels()))            $errors['urgency'] = 'Choose how urgent this is.';
    if ($data['pickup_address'] === '')                             $errors['pickup_address'] = 'Where are we collecting from?';
    if ($data['pickup_city'] === '')                                $errors['pickup_city'] = 'Which town?';
    if ($data['destination_address'] === '')                        $errors['destination_address'] = 'Where is it going?';
    if ($data['destination_city'] === '')                           $errors['destination_city'] = 'Which town?';
    if ($data['pickup_date'] !== '' && strtotime($data['pickup_date']) < strtotime('today')) {
        $errors['pickup_date'] = 'Pick today or a later date.';
    }

    if (!$errors) {
        try {
            $booking = Booking::create($data + ['user_id' => auth_id()]);
            $_SESSION['_recent_bookings'][] = $booking['reference'];
            flash('success', 'Booking ' . $booking['reference'] . ' received.');
            redirect('/logistics/' . $booking['reference']);
        } catch (Throwable $e) {
            $errors['_'] = APP_DEBUG ? $e->getMessage() : 'We could not submit that booking. Please try again.';
        }
    }

    flash_old($data);
}

$value = function (string $field, ?string $userField = null, string $fallback = '') use ($user) {
    $prefill = ($userField && $user && !empty($user[$userField])) ? (string) $user[$userField] : $fallback;
    return (string) old($field, $prefill);
};
$bad = fn(string $field) => isset($errors[$field]) ? ' input-error' : '';

/** Pricing tables handed to the client-side estimator. */
$pricingJson = json_encode([
    'vehicles' => array_map(fn($v) => ['base' => $v[0], 'capacity' => $v[2]], $vehicles),
    'distance' => Booking::distanceBands(),
    'urgency'  => Booking::urgencyLevels(),
    'labour'   => Booking::LABOUR_FEE,
], JSON_THROW_ON_ERROR);

$vehicleIcons = ['Motorcycle' => 'route', 'Mini Van' => 'truck', 'Cargo Van' => 'truck',
                 'Pickup Truck' => 'truck', 'Mini Truck' => 'truck', 'Large Truck' => 'truck', 'Flatbed' => 'warehouse'];

partial('header', [
    'title'       => page_title('Book logistics, truck hire and van hire in Asaba'),
    'description' => 'Hire a motorcycle, van, truck or flatbed anywhere in Delta State. Get an instant price estimate, book online and track your delivery from pickup to drop-off.',
    'ogImage'     => 'photos/fleet-truck.jpg',
]);
?>

<!-- Hero -->
<section class="relative isolate overflow-hidden bg-navy-900">
    <picture>
        <source srcset="<?= asset('img/photos/fleet-truck.webp') ?>" type="image/webp">
        <img src="<?= asset('img/photos/fleet-truck.jpg') ?>" alt=""
             class="absolute inset-0 size-full object-cover opacity-25" width="1600" height="900">
    </picture>
    <div class="absolute inset-0 bg-gradient-to-r from-navy-950 via-navy-900/95 to-navy-800/70"></div>

    <div class="shell relative py-14 lg:py-20">
        <nav class="mb-5 flex items-center gap-1.5 text-xs text-navy-200" aria-label="Breadcrumb">
            <a class="inline-flex min-h-6 items-center transition-colors hover:text-white" href="<?= url('/') ?>">Home</a>
            <?= icon('chevron-right', 'size-3') ?>
            <span class="text-white">Book logistics</span>
        </nav>

        <div class="max-w-3xl">
            <p class="eyebrow eyebrow-light"><?= icon('truck', 'size-3.5') ?>Truck hire &middot; Van hire &middot; Haulage</p>
            <h1 class="h-section mt-4 text-white sm:text-5xl">Know the price before you book</h1>
            <p class="mt-5 text-lg leading-relaxed text-navy-100">
                Relocations, market runs and interstate haulage across <?= e(APP_STATE) ?> and beyond.
                You do not need to order food to use it &mdash; the estimate updates as you fill the form.
            </p>
        </div>

        <div class="mt-10 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
            <?php foreach (array_slice($vehicles, 0, 4, true) as $name => [$base, $blurb, $capacity]): ?>
                <div class="rounded-2xl border border-white/15 bg-white/5 p-5 backdrop-blur">
                    <span class="text-orange-400"><?= icon($vehicleIcons[$name] ?? 'truck', 'size-6') ?></span>
                    <p class="mt-3 font-display font-bold text-white"><?= e($name) ?></p>
                    <p class="mt-1 text-xs leading-snug text-navy-200"><?= e($blurb) ?></p>
                    <p class="mt-3 font-display text-lg font-extrabold text-orange-400">
                        <?= money($base) ?> <span class="text-xs font-semibold text-navy-200">from</span>
                    </p>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="section-sm pb-20">
    <div class="shell">
        <?php if (isset($errors['_'])): ?>
            <div class="alert alert-error"><?= icon('alert', 'size-5 shrink-0') ?><span><?= e($errors['_']) ?></span></div>
        <?php endif; ?>

        <?php if ($errors && !isset($errors['_'])): ?>
            <div class="alert alert-error" role="alert">
                <?= icon('alert', 'size-5 shrink-0') ?>
                <span>Please fix the <?= count($errors) ?> highlighted field<?= count($errors) === 1 ? '' : 's' ?> below.</span>
            </div>
        <?php endif; ?>

        <form method="post" id="booking-form" data-pricing='<?= e($pricingJson) ?>'
              class="grid gap-8 lg:grid-cols-[minmax(0,1fr)_23rem] lg:items-start">
            <?= csrf_field() ?>

            <div class="space-y-6">

                <!-- Job -->
                <fieldset class="card card-pad">
                    <legend class="flex items-center gap-2.5 px-1">
                        <span class="grid size-8 place-items-center rounded-lg bg-navy-700 font-display text-sm font-bold text-white">1</span>
                        <span class="font-display text-lg font-bold text-navy-700">The job</span>
                    </legend>

                    <div class="mt-5 grid gap-x-5 sm:grid-cols-2">
                        <div class="field">
                            <label class="label" for="service_type">Service <span class="req">*</span></label>
                            <select class="select<?= $bad('service_type') ?>" id="service_type" name="service_type" required>
                                <?php foreach (Booking::serviceTypes() as $service): ?>
                                    <option value="<?= e($service) ?>" <?= $value('service_type', null, 'Truck Hire') === $service ? 'selected' : '' ?>><?= e($service) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <?php if (isset($errors['service_type'])): ?>
                                <p class="error-text"><?= icon('alert', 'size-4 shrink-0 mt-px') ?><?= e($errors['service_type']) ?></p>
                            <?php endif; ?>
                        </div>
                        <div class="field">
                            <label class="label" for="vehicle_type">Vehicle <span class="req">*</span></label>
                            <select class="select<?= $bad('vehicle_type') ?>" id="vehicle_type" name="vehicle_type" required>
                                <?php foreach ($vehicles as $name => [$base, $blurb, $capacity]): ?>
                                    <option value="<?= e($name) ?>" <?= $value('vehicle_type', null, 'Mini Truck') === $name ? 'selected' : '' ?>>
                                        <?= e($name) ?> &mdash; up to <?= number_format($capacity) ?>kg
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <?php if (isset($errors['vehicle_type'])): ?>
                                <p class="error-text"><?= icon('alert', 'size-4 shrink-0 mt-px') ?><?= e($errors['vehicle_type']) ?></p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="field mb-0">
                        <label class="label" for="description">What are we moving?</label>
                        <textarea class="textarea" id="description" name="description" rows="3"
                                  placeholder="20 bags of rice and 8 kegs of palm oil, palletised"><?= e(old('description')) ?></textarea>
                    </div>
                </fieldset>

                <!-- Route -->
                <fieldset class="card card-pad">
                    <legend class="flex items-center gap-2.5 px-1">
                        <span class="grid size-8 place-items-center rounded-lg bg-navy-700 font-display text-sm font-bold text-white">2</span>
                        <span class="font-display text-lg font-bold text-navy-700">Route</span>
                    </legend>

                    <datalist id="service-areas">
                        <?php foreach (service_areas() as $area): ?><option value="<?= e($area) ?>"><?php endforeach; ?>
                    </datalist>

                    <div class="mt-5 grid gap-5 sm:grid-cols-2">
                        <div class="rounded-xl border border-ink-200 bg-ink-50 p-4">
                            <p class="mb-3 flex items-center gap-2 font-display text-sm font-bold text-navy-700">
                                <?= icon('map-pin', 'size-4 text-orange-500') ?>Pickup
                            </p>
                            <div class="field">
                                <label class="label" for="pickup_address">Address <span class="req">*</span></label>
                                <input class="input<?= $bad('pickup_address') ?>" id="pickup_address" name="pickup_address"
                                       value="<?= e($value('pickup_address', 'address')) ?>" required>
                                <?php if (isset($errors['pickup_address'])): ?>
                                    <p class="error-text"><?= icon('alert', 'size-4 shrink-0 mt-px') ?><?= e($errors['pickup_address']) ?></p>
                                <?php endif; ?>
                            </div>
                            <div class="field mb-0">
                                <label class="label" for="pickup_city">Town <span class="req">*</span></label>
                                <input class="input<?= $bad('pickup_city') ?>" id="pickup_city" name="pickup_city" list="service-areas"
                                       value="<?= e($value('pickup_city', 'city', APP_CITY)) ?>" required>
                                <?php if (isset($errors['pickup_city'])): ?>
                                    <p class="error-text"><?= icon('alert', 'size-4 shrink-0 mt-px') ?><?= e($errors['pickup_city']) ?></p>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="rounded-xl border border-ink-200 bg-ink-50 p-4">
                            <p class="mb-3 flex items-center gap-2 font-display text-sm font-bold text-navy-700">
                                <?= icon('route', 'size-4 text-orange-500') ?>Destination
                            </p>
                            <div class="field">
                                <label class="label" for="destination_address">Address <span class="req">*</span></label>
                                <input class="input<?= $bad('destination_address') ?>" id="destination_address" name="destination_address"
                                       value="<?= e(old('destination_address')) ?>" required>
                                <?php if (isset($errors['destination_address'])): ?>
                                    <p class="error-text"><?= icon('alert', 'size-4 shrink-0 mt-px') ?><?= e($errors['destination_address']) ?></p>
                                <?php endif; ?>
                            </div>
                            <div class="field mb-0">
                                <label class="label" for="destination_city">Town <span class="req">*</span></label>
                                <input class="input<?= $bad('destination_city') ?>" id="destination_city" name="destination_city" list="service-areas"
                                       value="<?= e(old('destination_city')) ?>" required>
                                <?php if (isset($errors['destination_city'])): ?>
                                    <p class="error-text"><?= icon('alert', 'size-4 shrink-0 mt-px') ?><?= e($errors['destination_city']) ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <div class="field mb-0 mt-5">
                        <label class="label" for="distance_band">Distance <span class="req">*</span></label>
                        <select class="select<?= $bad('distance_band') ?>" id="distance_band" name="distance_band" required>
                            <?php foreach (Booking::distanceBands() as $band => $multiplier): ?>
                                <option value="<?= e($band) ?>" <?= $value('distance_band', null, 'Within Asaba') === $band ? 'selected' : '' ?>><?= e($band) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <p class="hint">Pick the band that matches your route. Dispatch confirms it against the real distance.</p>
                    </div>
                </fieldset>

                <!-- Schedule -->
                <fieldset class="card card-pad">
                    <legend class="flex items-center gap-2.5 px-1">
                        <span class="grid size-8 place-items-center rounded-lg bg-navy-700 font-display text-sm font-bold text-white">3</span>
                        <span class="font-display text-lg font-bold text-navy-700">When and how heavy</span>
                    </legend>

                    <div class="mt-5 grid gap-x-5 sm:grid-cols-3">
                        <div class="field">
                            <label class="label" for="pickup_date">Pickup date</label>
                            <input class="input<?= $bad('pickup_date') ?>" type="date" id="pickup_date" name="pickup_date"
                                   value="<?= e(old('pickup_date', date('Y-m-d', strtotime('+1 day')))) ?>" data-min-today>
                            <?php if (isset($errors['pickup_date'])): ?>
                                <p class="error-text"><?= icon('alert', 'size-4 shrink-0 mt-px') ?><?= e($errors['pickup_date']) ?></p>
                            <?php endif; ?>
                        </div>
                        <div class="field">
                            <label class="label" for="pickup_time">Pickup window</label>
                            <select class="select" id="pickup_time" name="pickup_time">
                                <?php foreach (Booking::pickupTimes() as $window): ?>
                                    <option value="<?= e($window) ?>" <?= old('pickup_time') === $window ? 'selected' : '' ?>><?= e($window) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="field">
                            <label class="label" for="weight_kg">Approx. weight (kg)</label>
                            <input class="input" type="number" min="0" step="10" id="weight_kg" name="weight_kg"
                                   value="<?= e(old('weight_kg', '500')) ?>" inputmode="numeric">
                        </div>
                    </div>

                    <div class="field">
                        <label class="label" for="urgency">Urgency <span class="req">*</span></label>
                        <select class="select<?= $bad('urgency') ?>" id="urgency" name="urgency" required>
                            <?php foreach (Booking::urgencyLevels() as $level => $multiplier): ?>
                                <option value="<?= e($level) ?>" <?= $value('urgency', null, 'Standard (24 - 72 hours)') === $level ? 'selected' : '' ?>><?= e($level) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <label class="check rounded-xl border border-ink-200 p-4 transition-colors hover:border-navy-300 has-[:checked]:border-navy-500 has-[:checked]:bg-navy-50">
                        <input type="checkbox" id="needs_labour" name="needs_labour" value="1" <?= old('needs_labour') ? 'checked' : '' ?>>
                        <span class="flex-1">
                            <span class="flex items-center gap-2 font-semibold text-navy-700">
                                <?= icon('users', 'size-4 text-orange-500') ?>I need loading and offloading hands
                            </span>
                            <span class="mt-0.5 block text-xs text-ink-500">
                                Adds <?= money(Booking::LABOUR_FEE) ?> for a two-man team.
                            </span>
                        </span>
                    </label>

                    <div class="field mb-0 mt-5">
                        <label class="label" for="instructions">Special instructions</label>
                        <textarea class="textarea" id="instructions" name="instructions" rows="3"
                                  placeholder="Gate closes at 6pm, ask for the storekeeper on arrival"><?= e(old('instructions')) ?></textarea>
                    </div>
                </fieldset>

                <!-- Contact -->
                <fieldset class="card card-pad">
                    <legend class="flex items-center gap-2.5 px-1">
                        <span class="grid size-8 place-items-center rounded-lg bg-navy-700 font-display text-sm font-bold text-white">4</span>
                        <span class="font-display text-lg font-bold text-navy-700">Who do we call?</span>
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
            </div>

            <!-- Estimator -->
            <aside class="lg:sticky lg:top-28">
                <div class="card overflow-hidden">
                    <div class="bg-navy-700 px-6 py-5 text-white">
                        <p class="flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-orange-400">
                            <?= icon('banknote', 'size-4') ?>Live estimate
                        </p>
                        <p class="price mt-2 font-display text-4xl font-extrabold" data-est="total">&mdash;</p>
                        <p class="mt-1 text-xs text-navy-200">Updates as you fill the form</p>
                    </div>

                    <div class="p-6">
                        <dl class="space-y-2.5 text-sm">
                            <?php foreach ([
                                'base'     => 'Base fare',
                                'distance' => 'Distance',
                                'weight'   => 'Weight surcharge',
                                'urgency'  => 'Urgency',
                                'labour'   => 'Loading crew',
                            ] as $key => $label): ?>
                                <div class="flex justify-between gap-4">
                                    <dt class="text-ink-500"><?= $label ?></dt>
                                    <dd class="price font-semibold text-navy-700" data-est="<?= $key ?>">&mdash;</dd>
                                </div>
                            <?php endforeach; ?>
                        </dl>

                        <p class="mt-5 flex items-start gap-2 rounded-xl bg-ink-50 p-3 text-xs leading-relaxed text-ink-500">
                            <?= icon('info', 'size-4 shrink-0 mt-px text-navy-500') ?>
                            Indicative only. Dispatch confirms the final price against the actual route and load
                            before any vehicle moves, and you approve it first.
                        </p>

                        <button class="btn btn-accent btn-block btn-lg mt-5 gap-2" type="submit">
                            <?= icon('truck', 'size-5') ?>Request this booking
                        </button>
                        <noscript>
                            <p class="mt-3 text-xs text-ink-400">
                                Estimates need JavaScript. Submit anyway and we will price it for you.
                            </p>
                        </noscript>
                    </div>
                </div>

                <div class="card card-pad mt-4">
                    <h2 class="text-base">What happens next</h2>
                    <ul class="tick-list mt-4 text-sm">
                        <li>Dispatch confirms vehicle availability for your date.</li>
                        <li>You get a firm price, not the estimate.</li>
                        <li>A driver and vehicle registration are assigned.</li>
                        <li>Track it from pickup to drop-off on your reference.</li>
                    </ul>
                </div>
            </aside>
        </form>
    </div>
</section>

<!-- Fleet -->
<section class="section bg-white">
    <div class="shell">
        <div class="mx-auto mb-12 max-w-2xl text-center">
            <p class="eyebrow"><?= icon('truck', 'size-3.5') ?>Fleet</p>
            <h2 class="h-section mt-3">Every vehicle we run, and what it is for</h2>
        </div>

        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
            <?php foreach ($vehicles as $name => [$base, $blurb, $capacity]): ?>
                <div class="card card-hover p-6" data-reveal>
                    <span class="grid size-12 place-items-center rounded-xl bg-navy-50 text-navy-600">
                        <?= icon($vehicleIcons[$name] ?? 'truck', 'size-6') ?>
                    </span>
                    <h3 class="mt-4 text-base"><?= e($name) ?></h3>
                    <p class="mt-1.5 text-sm leading-relaxed text-ink-500"><?= e($blurb) ?></p>
                    <div class="mt-4 flex items-baseline justify-between border-t border-ink-100 pt-3">
                        <span class="text-xs text-ink-400"><?= number_format($capacity) ?>kg payload</span>
                        <span class="price font-display font-extrabold text-navy-700"><?= money($base) ?></span>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<script>
/* Live price estimator — mirrors Booking::estimate() in PHP. The server
   recalculates on submit, so this is convenience, never the source of truth. */
(function () {
    var form = document.getElementById('booking-form');
    if (!form) return;

    var pricing = JSON.parse(form.dataset.pricing);
    var naira = new Intl.NumberFormat('en-NG', { style: 'currency', currency: 'NGN', minimumFractionDigits: 0 });

    function recalculate() {
        var vehicle  = pricing.vehicles[form.vehicle_type.value] || { base: 0, capacity: 1 };
        var distance = pricing.distance[form.distance_band.value] || 1;
        var urgency  = pricing.urgency[form.urgency.value] || 1;
        var weight   = parseInt(form.weight_kg.value || '0', 10);

        var overload   = Math.max(0, weight - vehicle.capacity);
        var overloadPc = vehicle.capacity > 0 ? Math.min(0.5, (overload / vehicle.capacity) * 0.35) : 0;

        var distanceCost = vehicle.base * (distance - 1);
        var weightCost   = vehicle.base * overloadPc;
        var subtotal     = vehicle.base + distanceCost + weightCost;
        var urgencyCost  = subtotal * (urgency - 1);
        var labourCost   = form.needs_labour.checked ? pricing.labour : 0;

        var parts = {
            base: vehicle.base, distance: distanceCost, weight: weightCost,
            urgency: urgencyCost, labour: labourCost,
            total: subtotal + urgencyCost + labourCost
        };

        Object.keys(parts).forEach(function (key) {
            var cell = form.querySelector('[data-est="' + key + '"]');
            if (cell) cell.textContent = naira.format(Math.round(parts[key]));
        });
    }

    ['vehicle_type', 'distance_band', 'urgency', 'weight_kg', 'needs_labour'].forEach(function (name) {
        var field = form.elements[name];
        if (!field) return;
        field.addEventListener('change', recalculate);
        field.addEventListener('input', recalculate);
    });

    recalculate();
})();
</script>

<?php partial('footer'); ?>
