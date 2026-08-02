<?php
$user   = auth_user();
$errors = [];

if (is_post()) {
    $data = [
        'name'    => (string) input('name'),
        'phone'   => (string) input('phone'),
        'company' => (string) input('company'),
        'address' => (string) input('address'),
        'city'    => (string) input('city'),
        'state'   => (string) input('state'),
    ];

    if ($data['name'] === '')                                   $errors['name'] = 'Your name cannot be blank.';
    if (strlen(preg_replace('/\D+/', '', $data['phone'])) < 10) $errors['phone'] = 'Enter a reachable phone number.';

    if (!$errors) {
        User::updateProfile((int) $user['id'], $data);
        flash('success', 'Profile updated.');
        redirect('/account');
    }

    flash_old($data);
    $user = array_merge($user, $data);
}

$bad = fn(string $field) => isset($errors[$field]) ? ' input-error' : '';

partial('header', ['title' => page_title('My profile')]);
partial('account_head', ['heading' => 'My account', 'sub' => 'Signed in as ' . $user['email']]);
?>

<section class="section-sm pb-20">
    <div class="shell">
        <?php partial('account_nav'); ?>

        <div class="grid gap-8 lg:grid-cols-[minmax(0,1fr)_20rem] lg:items-start">

            <div class="card card-pad">
                <h2 class="text-lg">Profile and default delivery details</h2>
                <p class="mt-1.5 text-sm text-ink-500">These pre-fill your checkout, so you only type them once.</p>

                <?php if ($errors): ?>
                    <div class="alert alert-error mt-5" role="alert">
                        <?= icon('alert', 'size-5 shrink-0') ?>
                        <span>Please fix the <?= count($errors) ?> highlighted field<?= count($errors) === 1 ? '' : 's' ?> below.</span>
                    </div>
                <?php endif; ?>

                <form method="post" class="mt-6">
                    <?= csrf_field() ?>

                    <div class="grid gap-x-5 sm:grid-cols-2">
                        <div class="field">
                            <label class="label" for="name">Full name <span class="req">*</span></label>
                            <input class="input<?= $bad('name') ?>" id="name" name="name"
                                   value="<?= e(old('name', $user['name'])) ?>" autocomplete="name" required>
                            <?php if (isset($errors['name'])): ?>
                                <p class="error-text"><?= icon('alert', 'size-4 shrink-0 mt-px') ?><?= e($errors['name']) ?></p>
                            <?php endif; ?>
                        </div>
                        <div class="field">
                            <label class="label" for="company">Company</label>
                            <input class="input" id="company" name="company"
                                   value="<?= e(old('company', $user['company'] ?? '')) ?>" autocomplete="organization">
                        </div>
                        <div class="field">
                            <label class="label" for="email-display">Email</label>
                            <input class="input cursor-not-allowed bg-ink-50 text-ink-400" id="email-display"
                                   value="<?= e($user['email']) ?>" disabled>
                            <p class="hint">Contact us if you need this changed.</p>
                        </div>
                        <div class="field">
                            <label class="label" for="phone">Phone <span class="req">*</span></label>
                            <input class="input<?= $bad('phone') ?>" type="tel" id="phone" name="phone"
                                   value="<?= e(old('phone', $user['phone'] ?? '')) ?>" autocomplete="tel" required>
                            <?php if (isset($errors['phone'])): ?>
                                <p class="error-text"><?= icon('alert', 'size-4 shrink-0 mt-px') ?><?= e($errors['phone']) ?></p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="field">
                        <label class="label" for="address">Delivery address</label>
                        <input class="input" id="address" name="address"
                               value="<?= e(old('address', $user['address'] ?? '')) ?>" autocomplete="street-address">
                    </div>

                    <div class="grid gap-x-5 sm:grid-cols-2">
                        <div class="field">
                            <label class="label" for="city">City / area</label>
                            <input class="input" id="city" name="city" list="account-areas"
                                   value="<?= e(old('city', $user['city'] ?? '')) ?>" autocomplete="address-level2">
                            <datalist id="account-areas">
                                <?php foreach (service_areas() as $area): ?><option value="<?= e($area) ?>"><?php endforeach; ?>
                            </datalist>
                        </div>
                        <div class="field">
                            <label class="label" for="state">State</label>
                            <select class="select" id="state" name="state">
                                <option value="">Select a state</option>
                                <?php foreach (nigerian_states() as $state): ?>
                                    <option value="<?= e($state) ?>" <?= old('state', $user['state'] ?? '') === $state ? 'selected' : '' ?>><?= e($state) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <button class="btn btn-primary gap-2" type="submit">
                        <?= icon('check', 'size-5') ?>Save changes
                    </button>
                </form>
            </div>

            <aside class="space-y-4 lg:sticky lg:top-28">
                <div class="card card-pad">
                    <h2 class="text-base">Account</h2>
                    <dl class="mt-4 divide-y divide-ink-100 border-t border-ink-100 text-sm">
                        <?php foreach ([
                            'Role'         => ucfirst($user['role']),
                            'Member since' => date_human($user['created_at']),
                            'Orders'       => (string) (int) Db::value('SELECT COUNT(*) FROM orders WHERE user_id = ?', [$user['id']]),
                            'Bookings'     => (string) (int) Db::value('SELECT COUNT(*) FROM logistics_bookings WHERE user_id = ?', [$user['id']]),
                        ] as $term => $val): ?>
                            <div class="flex justify-between gap-4 py-3">
                                <dt class="text-ink-500"><?= $term ?></dt>
                                <dd class="font-semibold text-navy-700"><?= e($val) ?></dd>
                            </div>
                        <?php endforeach; ?>
                    </dl>
                </div>

                <div class="card card-pad">
                    <h2 class="text-base">Security</h2>
                    <p class="mt-1.5 text-sm text-ink-500">Change your password regularly, especially on shared devices.</p>
                    <a class="btn btn-ghost btn-block mt-4 gap-2" href="<?= url('/account/password') ?>">
                        <?= icon('shield', 'size-4') ?>Change password
                    </a>
                </div>
            </aside>
        </div>
    </div>
</section>

<?php partial('footer'); ?>
