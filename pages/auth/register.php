<?php
if (auth_check()) {
    redirect('/account');
}

$errors = [];

if (is_post()) {
    $data = [
        'name'     => (string) input('name'),
        'email'    => (string) input('email'),
        'phone'    => (string) input('phone'),
        'company'  => (string) input('company'),
        'address'  => (string) input('address'),
        'city'     => (string) input('city'),
        'state'    => (string) input('state'),
        'password' => (string) input('password'),
    ];
    $confirm = (string) input('password_confirm');

    if ($data['name'] === '')                                   $errors['name'] = 'Enter your full name.';
    if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL))     $errors['email'] = 'Enter a valid email address.';
    elseif (User::emailTaken($data['email']))                   $errors['email'] = 'That email already has an account. Try signing in.';
    if (strlen(preg_replace('/\D+/', '', $data['phone'])) < 10) $errors['phone'] = 'Enter a reachable phone number.';
    if (strlen($data['password']) < 8)                          $errors['password'] = 'Use at least 8 characters.';
    elseif ($data['password'] !== $confirm)                     $errors['password_confirm'] = 'The two passwords do not match.';

    if (!$errors) {
        $userId = User::create($data + ['role' => 'customer']);
        auth_login(User::find($userId));
        flash('success', 'Account created. Welcome to KACHI.');
        redirect(intended('/account'));
    }

    flash_old($data);
}

$bad = fn(string $field) => isset($errors[$field]) ? ' input-error' : '';

partial('header', ['title' => page_title('Create an account')]);
?>

<section class="section-sm pb-20">
    <div class="shell">
        <div class="mx-auto grid max-w-5xl gap-8 lg:grid-cols-[minmax(0,1fr)_18rem] lg:items-start">

            <div class="card card-pad">
                <span class="badge badge-navy">Trade account</span>
                <h1 class="mt-4 font-display text-3xl font-extrabold text-navy-700">Create your account</h1>
                <p class="mt-2 text-ink-500">Faster checkout, saved delivery details and every invoice in one place.</p>

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
                            <input class="input<?= $bad('name') ?>" id="name" name="name" value="<?= e(old('name')) ?>"
                                   autocomplete="name" required>
                            <?php if (isset($errors['name'])): ?>
                                <p class="error-text"><?= icon('alert', 'size-4 shrink-0 mt-px') ?><?= e($errors['name']) ?></p>
                            <?php endif; ?>
                        </div>
                        <div class="field">
                            <label class="label" for="company">Company (optional)</label>
                            <input class="input" id="company" name="company" value="<?= e(old('company')) ?>" autocomplete="organization">
                        </div>
                        <div class="field">
                            <label class="label" for="email">Email <span class="req">*</span></label>
                            <input class="input<?= $bad('email') ?>" type="email" id="email" name="email"
                                   value="<?= e(old('email')) ?>" autocomplete="email" required>
                            <?php if (isset($errors['email'])): ?>
                                <p class="error-text"><?= icon('alert', 'size-4 shrink-0 mt-px') ?><?= e($errors['email']) ?></p>
                            <?php endif; ?>
                        </div>
                        <div class="field">
                            <label class="label" for="phone">Phone <span class="req">*</span></label>
                            <input class="input<?= $bad('phone') ?>" type="tel" id="phone" name="phone"
                                   value="<?= e(old('phone')) ?>" autocomplete="tel" required>
                            <?php if (isset($errors['phone'])): ?>
                                <p class="error-text"><?= icon('alert', 'size-4 shrink-0 mt-px') ?><?= e($errors['phone']) ?></p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="field">
                        <label class="label" for="address">Delivery address</label>
                        <input class="input" id="address" name="address" value="<?= e(old('address')) ?>" autocomplete="street-address">
                    </div>

                    <div class="grid gap-x-5 sm:grid-cols-2">
                        <div class="field">
                            <label class="label" for="city">City / area</label>
                            <input class="input" id="city" name="city" list="register-areas"
                                   value="<?= e(old('city')) ?>" autocomplete="address-level2">
                            <datalist id="register-areas">
                                <?php foreach (service_areas() as $area): ?><option value="<?= e($area) ?>"><?php endforeach; ?>
                            </datalist>
                        </div>
                        <div class="field">
                            <label class="label" for="state">State</label>
                            <select class="select" id="state" name="state">
                                <option value="">Select a state</option>
                                <?php foreach (nigerian_states() as $state): ?>
                                    <option value="<?= e($state) ?>" <?= old('state', 'Delta') === $state ? 'selected' : '' ?>><?= e($state) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="grid gap-x-5 sm:grid-cols-2">
                        <div class="field">
                            <label class="label" for="password">Password <span class="req">*</span></label>
                            <input class="input<?= $bad('password') ?>" type="password" id="password" name="password"
                                   autocomplete="new-password" required>
                            <?php if (isset($errors['password'])): ?>
                                <p class="error-text"><?= icon('alert', 'size-4 shrink-0 mt-px') ?><?= e($errors['password']) ?></p>
                            <?php else: ?>
                                <p class="hint">At least 8 characters.</p>
                            <?php endif; ?>
                        </div>
                        <div class="field">
                            <label class="label" for="password_confirm">Confirm password <span class="req">*</span></label>
                            <input class="input<?= $bad('password_confirm') ?>" type="password" id="password_confirm"
                                   name="password_confirm" autocomplete="new-password" required>
                            <?php if (isset($errors['password_confirm'])): ?>
                                <p class="error-text"><?= icon('alert', 'size-4 shrink-0 mt-px') ?><?= e($errors['password_confirm']) ?></p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <button class="btn btn-primary btn-block btn-lg mt-2 gap-2" type="submit">
                        <?= icon('check', 'size-5') ?>Create account
                    </button>
                </form>

                <div class="divider"></div>
                <p class="text-center text-sm text-ink-500">
                    Already registered? <a class="link-quiet" href="<?= url('/login') ?>">Sign in instead</a>.
                </p>
            </div>

            <aside class="card card-pad lg:sticky lg:top-28">
                <h2 class="text-base">Why register</h2>
                <ul class="tick-list mt-4 text-sm">
                    <li>Checkout pre-filled with your delivery details</li>
                    <li>Every order and logistics booking in one place</li>
                    <li>Live status and full delivery timelines</li>
                    <li>Apply for volume pricing and credit terms</li>
                </ul>
            </aside>
        </div>
    </div>
</section>

<?php partial('footer'); ?>
