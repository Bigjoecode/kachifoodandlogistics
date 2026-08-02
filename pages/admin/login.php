<?php
if (is_staff()) {
    redirect('/admin');
}

$error = null;

if (is_post()) {
    $user = User::verify((string) input('email'), (string) input('password'));

    if ($user && in_array($user['role'], ['admin', 'staff'], true)) {
        auth_login($user);
        flash('success', 'Signed in as ' . $user['name'] . '.');
        redirect(intended('/admin'));
    }

    $error = $user ? 'That account does not have back office access.' : 'Those credentials did not work.';
    flash_old(['email' => (string) input('email')]);
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Back office sign in | <?= e(APP_NAME) ?></title>
    <meta name="robots" content="noindex">
    <link rel="stylesheet" href="<?= asset('css/app.css') ?>">
    <style>
        body { display: grid; place-items: center; background: var(--brand-900); }
        .signin { width: min(420px, calc(100vw - 2.5rem)); padding: 2rem 0; }
    </style>
</head>
<body>
<main class="signin">
    <div class="center mb-3">
        <span class="brand-mark" style="margin:0 auto 1rem">KF</span>
        <h1 style="color:#fff;font-size:1.5rem;margin-bottom:.25rem">Kachi back office</h1>
        <p style="color:var(--brand-200)" class="small">Staff and administrators only.</p>
    </div>

    <div class="card card-pad">
        <?php if ($error): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>

        <form method="post">
            <?= csrf_field() ?>
            <div class="field">
                <label for="email">Work email</label>
                <input class="input" type="email" id="email" name="email" value="<?= e(old('email')) ?>" required autofocus>
            </div>
            <div class="field">
                <label for="password">Password</label>
                <input class="input" type="password" id="password" name="password" required>
            </div>
            <button class="btn btn-primary btn-block" type="submit">Sign in</button>
        </form>
    </div>

    <p class="center small mt-3"><a href="<?= url('/') ?>" style="color:var(--brand-200)">Back to the storefront</a></p>
</main>
<script src="<?= asset('js/app.js') ?>"></script>
</body>
</html>
