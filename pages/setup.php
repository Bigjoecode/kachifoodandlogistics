<?php
/**
 * First-run administrator setup.
 *
 * The production database is bootstrapped automatically from schema.sql and
 * seed.sql, neither of which creates a user — accounts are hashed by PHP, so
 * they cannot be seeded from SQL. install.php is deliberately never deployed.
 * That leaves a fresh production install with no way in, which this page fixes.
 *
 * It is only reachable while the users table holds no admin or staff account.
 * The moment one exists this route 404s, so it cannot be used to add a second
 * administrator later.
 */
if (User::staffExists()) {
    abort_404();
}

$errors = [];

if (is_post()) {
    $data = [
        'name'     => (string) input('name'),
        'email'    => (string) input('email'),
        'phone'    => (string) input('phone'),
        'password' => (string) input('password'),
    ];
    $confirm = (string) input('password_confirm');

    if ($data['name'] === '')                               $errors['name'] = 'Enter your full name.';
    if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) $errors['email'] = 'Enter a valid email address.';
    elseif (User::emailTaken($data['email']))               $errors['email'] = 'That email already has an account.';
    if (strlen($data['password']) < 10)                     $errors['password'] = 'Use at least 10 characters for an administrator.';
    elseif ($data['password'] !== $confirm)                 $errors['password_confirm'] = 'The two passwords do not match.';

    if (!$errors) {
        try {
            $userId = User::createFirstAdmin($data);
            auth_login(User::find($userId));
            flash('success', 'Administrator account created. Welcome to the back office.');
            redirect('/admin');
        } catch (RuntimeException $e) {
            // Another request won the race and created the first admin.
            $errors['_'] = 'An administrator already exists. Sign in instead.';
        }
    }

    flash_old($data);
}

$bad = fn(string $field) => isset($errors[$field]) ? ' has-error' : '';
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>First-run setup | <?= e(APP_NAME) ?></title>
    <meta name="robots" content="noindex, nofollow">
    <link rel="stylesheet" href="<?= asset('css/app.css') ?>">
    <link rel="icon" type="image/png" href="<?= asset('img/favicon.png') ?>">
    <style>
        body { display: grid; place-items: center; background: var(--brand-900); }
        .setup { width: min(480px, calc(100vw - 2.5rem)); padding: 2rem 0; }
    </style>
</head>
<body>
<main class="setup">
    <div class="center mb-3">
        <span class="brand-mark" style="margin:0 auto 1rem">KF</span>
        <h1 style="color:#fff;font-size:1.5rem;margin-bottom:.25rem">Create the first administrator</h1>
        <p style="color:var(--brand-200)" class="small">
            This site has no accounts yet. This page stops working once one exists.
        </p>
    </div>

    <div class="card card-pad">
        <?php if (isset($errors['_'])): ?>
            <div class="alert alert-error"><?= e($errors['_']) ?></div>
        <?php elseif ($errors): ?>
            <div class="alert alert-error" role="alert">
                Please fix the <?= count($errors) ?> highlighted field<?= count($errors) === 1 ? '' : 's' ?> below.
            </div>
        <?php endif; ?>

        <form method="post">
            <?= csrf_field() ?>

            <div class="field">
                <label for="name">Full name</label>
                <input class="input<?= $bad('name') ?>" id="name" name="name"
                       value="<?= e(old('name')) ?>" autocomplete="name" required autofocus>
                <?php if (isset($errors['name'])): ?><p class="error-text"><?= e($errors['name']) ?></p><?php endif; ?>
            </div>

            <div class="field">
                <label for="email">Work email</label>
                <input class="input<?= $bad('email') ?>" type="email" id="email" name="email"
                       value="<?= e(old('email')) ?>" autocomplete="email" required>
                <?php if (isset($errors['email'])): ?><p class="error-text"><?= e($errors['email']) ?></p><?php endif; ?>
            </div>

            <div class="field">
                <label for="phone">Phone</label>
                <input class="input" type="tel" id="phone" name="phone"
                       value="<?= e(old('phone')) ?>" autocomplete="tel">
            </div>

            <div class="field">
                <label for="password">Password</label>
                <input class="input<?= $bad('password') ?>" type="password" id="password" name="password"
                       autocomplete="new-password" required>
                <?php if (isset($errors['password'])): ?>
                    <p class="error-text"><?= e($errors['password']) ?></p>
                <?php else: ?>
                    <p class="hint">At least 10 characters. Use something you do not reuse elsewhere.</p>
                <?php endif; ?>
            </div>

            <div class="field">
                <label for="password_confirm">Confirm password</label>
                <input class="input<?= $bad('password_confirm') ?>" type="password" id="password_confirm"
                       name="password_confirm" autocomplete="new-password" required>
                <?php if (isset($errors['password_confirm'])): ?>
                    <p class="error-text"><?= e($errors['password_confirm']) ?></p>
                <?php endif; ?>
            </div>

            <button class="btn btn-primary btn-block" type="submit">Create administrator</button>
        </form>
    </div>

    <p class="center small mt-3" style="color:var(--brand-200)">
        Already set up? <a href="<?= url('/admin/login') ?>" style="color:#fff">Sign in</a>.
    </p>
</main>
</body>
</html>
