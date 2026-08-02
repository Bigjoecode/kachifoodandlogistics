<?php
$fields = [
    'site_name'          => ['Site name', 'text'],
    'contact_email'      => ['Contact email', 'email'],
    'contact_phone'      => ['Contact phone', 'text'],
    'contact_phone_alt'  => ['Second contact phone', 'text'],
    'whatsapp'           => ['WhatsApp number (digits only, with country code)', 'text'],
    'address'            => ['Head office address', 'text'],
    'opening_hours'      => ['Opening hours', 'text'],
    'delivery_fee'       => ['Flat delivery fee (' . CURRENCY . ')', 'number'],
    'free_delivery_from' => ['Free delivery from (' . CURRENCY . ')', 'number'],
    'bank_name'          => ['Bank name', 'text'],
    'bank_account_name'  => ['Account name', 'text'],
    'bank_account_no'    => ['Account number', 'text'],
    'facebook'           => ['Facebook page URL', 'url'],
    'instagram'          => ['Instagram profile URL', 'url'],
    'tiktok'             => ['TikTok profile URL', 'url'],
];

$errors = [];

if (is_post()) {
    $values = [];
    foreach ($fields as $key => [$label, $type]) {
        $values[$key] = (string) input($key, '');
    }

    if ($values['site_name'] === '')                              $errors['site_name'] = 'The site needs a name.';
    if (!filter_var($values['contact_email'], FILTER_VALIDATE_EMAIL)) $errors['contact_email'] = 'Enter a valid email address.';
    if (!is_numeric($values['delivery_fee']) || $values['delivery_fee'] < 0)  $errors['delivery_fee'] = 'Enter a number.';
    if (!is_numeric($values['free_delivery_from']) || $values['free_delivery_from'] < 0) $errors['free_delivery_from'] = 'Enter a number.';

    if (!$errors) {
        Setting::setMany($values);
        flash('success', 'Settings saved.');
        redirect('/admin/settings');
    }

    flash_old($values);
}

partial('admin_header', [
    'title'    => 'Settings',
    'subtitle' => 'Contact details, delivery pricing and payment information',
]);
?>

<form method="post" class="split">
    <?= csrf_field() ?>

    <div>
        <div class="card card-pad mb-3">
            <h3>Business details</h3>
            <?php foreach (['site_name', 'contact_email', 'contact_phone', 'contact_phone_alt', 'whatsapp', 'address', 'opening_hours'] as $key): ?>
                <?php [$label, $type] = $fields[$key]; ?>
                <div class="field">
                    <label for="<?= $key ?>"><?= e($label) ?></label>
                    <input class="input <?= isset($errors[$key]) ? 'has-error' : '' ?>" type="<?= $type ?>"
                           id="<?= $key ?>" name="<?= $key ?>" value="<?= e(old($key, Setting::get($key, ''))) ?>">
                    <?php if (isset($errors[$key])): ?><p class="error-text"><?= e($errors[$key]) ?></p><?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="card card-pad mb-3">
            <h3>Social profiles</h3>
            <p class="small muted">Shown in the footer and published as <code>sameAs</code> in the site schema.
               Leave one blank to hide its icon.</p>
            <?php foreach (['facebook', 'instagram', 'tiktok'] as $key): ?>
                <div class="field">
                    <label for="<?= $key ?>"><?= e($fields[$key][0]) ?></label>
                    <input class="input <?= isset($errors[$key]) ? 'has-error' : '' ?>" type="url"
                           id="<?= $key ?>" name="<?= $key ?>" value="<?= e(old($key, Setting::get($key, ''))) ?>"
                           placeholder="https://">
                    <?php if (isset($errors[$key])): ?><p class="error-text"><?= e($errors[$key]) ?></p><?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="card card-pad">
            <h3>Payment details</h3>
            <p class="small muted">Shown on the order confirmation for bank transfer payments.</p>
            <div class="field-row-3">
                <?php foreach (['bank_name', 'bank_account_name', 'bank_account_no'] as $key): ?>
                    <div class="field">
                        <label for="<?= $key ?>"><?= e($fields[$key][0]) ?></label>
                        <input class="input" id="<?= $key ?>" name="<?= $key ?>" value="<?= e(old($key, Setting::get($key, ''))) ?>">
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <aside>
        <div class="card card-pad mb-3">
            <h3>Delivery pricing</h3>
            <?php foreach (['delivery_fee', 'free_delivery_from'] as $key): ?>
                <div class="field">
                    <label for="<?= $key ?>"><?= e($fields[$key][0]) ?></label>
                    <input class="input <?= isset($errors[$key]) ? 'has-error' : '' ?>" type="number" step="1" min="0"
                           id="<?= $key ?>" name="<?= $key ?>" value="<?= e(old($key, Setting::get($key, '0'))) ?>">
                    <?php if (isset($errors[$key])): ?><p class="error-text"><?= e($errors[$key]) ?></p><?php endif; ?>
                </div>
            <?php endforeach; ?>
            <p class="hint">Applies to new carts immediately. Existing orders keep the fee they were placed with.</p>
        </div>

        <div class="card card-pad mb-3">
            <button class="btn btn-primary btn-block" type="submit">Save settings</button>
        </div>

        <div class="card card-pad">
            <h4>Environment</h4>
            <dl class="spec-list" style="margin:0">
                <div><dt>PHP</dt><dd class="mono"><?= e(PHP_VERSION) ?></dd></div>
                <div><dt>Database</dt><dd class="mono"><?= e(DB_NAME) ?></dd></div>
                <div><dt>Mode</dt><dd><?= APP_DEBUG ? 'Debug' : 'Production' ?></dd></div>
                <div><dt>Uploads</dt><dd><?= is_writable(dirname(UPLOAD_PATH)) ? 'Writable' : 'Not writable' ?></dd></div>
            </dl>
            <?php if (is_file(ROOT_PATH . '/install.php')): ?>
                <div class="alert alert-warn small mt-3 mb-0">
                    <strong>install.php is still present.</strong> Delete it before this site goes live.
                </div>
            <?php endif; ?>
        </div>
    </aside>
</form>

<?php partial('admin_footer'); ?>
