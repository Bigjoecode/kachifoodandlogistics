<?php
$user   = auth_user();
$errors = [];

if (is_post()) {
    $current = (string) input('current_password');
    $new     = (string) input('password');
    $confirm = (string) input('password_confirm');

    if (!password_verify($current, $user['password_hash'])) $errors['current_password'] = 'That is not your current password.';
    if (strlen($new) < 8)                                   $errors['password'] = 'Use at least 8 characters.';
    elseif ($new !== $confirm)                              $errors['password_confirm'] = 'The two passwords do not match.';

    if (!$errors) {
        User::updatePassword((int) $user['id'], $new);
        flash('success', 'Password changed.');
        redirect('/account/password');
    }
}

$bad = fn(string $field) => isset($errors[$field]) ? ' input-error' : '';

partial('header', ['title' => page_title('Change password')]);
partial('account_head', ['heading' => 'My account', 'sub' => 'Signed in as ' . $user['email']]);
?>

<section class="section-sm pb-20">
    <div class="shell">
        <?php partial('account_nav'); ?>

        <div class="grid gap-8 lg:grid-cols-[minmax(0,1fr)_20rem] lg:items-start">
            <div class="card card-pad max-w-xl">
                <h2 class="text-lg">Change your password</h2>

                <?php if ($errors): ?>
                    <div class="alert alert-error mt-5" role="alert">
                        <?= icon('alert', 'size-5 shrink-0') ?>
                        <span>Please fix the <?= count($errors) ?> highlighted field<?= count($errors) === 1 ? '' : 's' ?> below.</span>
                    </div>
                <?php endif; ?>

                <form method="post" class="mt-6">
                    <?= csrf_field() ?>

                    <div class="field">
                        <label class="label" for="current_password">Current password</label>
                        <input class="input<?= $bad('current_password') ?>" type="password" id="current_password"
                               name="current_password" autocomplete="current-password" required>
                        <?php if (isset($errors['current_password'])): ?>
                            <p class="error-text"><?= icon('alert', 'size-4 shrink-0 mt-px') ?><?= e($errors['current_password']) ?></p>
                        <?php endif; ?>
                    </div>

                    <div class="field">
                        <label class="label" for="password">New password</label>
                        <input class="input<?= $bad('password') ?>" type="password" id="password" name="password"
                               autocomplete="new-password" required>
                        <?php if (isset($errors['password'])): ?>
                            <p class="error-text"><?= icon('alert', 'size-4 shrink-0 mt-px') ?><?= e($errors['password']) ?></p>
                        <?php else: ?>
                            <p class="hint">At least 8 characters.</p>
                        <?php endif; ?>
                    </div>

                    <div class="field">
                        <label class="label" for="password_confirm">Confirm new password</label>
                        <input class="input<?= $bad('password_confirm') ?>" type="password" id="password_confirm"
                               name="password_confirm" autocomplete="new-password" required>
                        <?php if (isset($errors['password_confirm'])): ?>
                            <p class="error-text"><?= icon('alert', 'size-4 shrink-0 mt-px') ?><?= e($errors['password_confirm']) ?></p>
                        <?php endif; ?>
                    </div>

                    <button class="btn btn-primary gap-2" type="submit">
                        <?= icon('shield', 'size-5') ?>Update password
                    </button>
                </form>
            </div>

            <aside class="card card-pad lg:sticky lg:top-28">
                <h2 class="text-base">Keeping the account safe</h2>
                <ul class="tick-list mt-4 text-sm">
                    <li>Use a password you do not reuse anywhere else.</li>
                    <li>Sign out on shared or public devices.</li>
                    <li>We will never ask for your password by phone or email.</li>
                </ul>
            </aside>
        </div>
    </div>
</section>

<?php partial('footer'); ?>
