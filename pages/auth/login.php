<?php
if (auth_check()) {
    redirect(is_staff() ? '/admin' : '/account');
}

$error = null;

if (is_post()) {
    $email    = (string) input('email');
    $password = (string) input('password');
    $user     = User::verify($email, $password);

    if ($user) {
        auth_login($user);
        flash('success', 'Welcome back, ' . explode(' ', $user['name'])[0] . '.');
        redirect(intended(in_array($user['role'], ['admin', 'staff'], true) ? '/admin' : '/account'));
    }

    $error = 'That email and password combination did not work.';
    flash_old(['email' => $email]);
}

partial('header', ['title' => page_title('Sign in')]);
?>

<section class="section-sm pb-20">
    <div class="shell">
        <div class="mx-auto max-w-md">

            <div class="mb-8 text-center">
                <span class="mx-auto grid size-14 place-items-center rounded-2xl bg-navy-700 text-white shadow-soft">
                    <?= icon('user', 'size-7') ?>
                </span>
                <h1 class="mt-5 font-display text-3xl font-extrabold text-navy-700">Sign in</h1>
                <p class="mt-2 text-ink-500">
                    Track orders, reorder in one click and see your full invoice history.
                </p>
            </div>

            <div class="card card-pad">
                <?php if ($error): ?>
                    <div class="alert alert-error" role="alert">
                        <?= icon('alert', 'size-5 shrink-0') ?><span><?= e($error) ?></span>
                    </div>
                <?php endif; ?>

                <form method="post">
                    <?= csrf_field() ?>
                    <div class="field">
                        <label class="label" for="email">Email</label>
                        <input class="input<?= $error ? ' input-error' : '' ?>" type="email" id="email" name="email"
                               value="<?= e(old('email')) ?>" autocomplete="email" required autofocus>
                    </div>
                    <div class="field">
                        <label class="label" for="password">Password</label>
                        <input class="input<?= $error ? ' input-error' : '' ?>" type="password" id="password" name="password"
                               autocomplete="current-password" required>
                    </div>
                    <button class="btn btn-primary btn-block btn-lg gap-2" type="submit">
                        <?= icon('arrow-right', 'size-5') ?>Sign in
                    </button>
                </form>

                <div class="divider"></div>

                <p class="text-center text-sm text-ink-500">
                    No account yet? <a class="link-quiet" href="<?= url('/register') ?>">Create one</a> &mdash; it takes a minute.
                </p>
            </div>

            <p class="mt-6 text-center text-sm text-ink-400">
                Staff should use the <a class="link-quiet" href="<?= url('/admin/login') ?>">back office sign in</a>.
            </p>
        </div>
    </div>
</section>

<?php partial('footer'); ?>
