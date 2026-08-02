<?php
$errors = [];

if (is_post()) {
    $data = [
        'name'    => (string) input('name'),
        'email'   => (string) input('email'),
        'phone'   => (string) input('phone'),
        'subject' => (string) input('subject'),
        'message' => (string) input('message'),
    ];

    if ($data['name'] === '')                               $errors['name'] = 'Tell us who you are.';
    if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) $errors['email'] = 'Enter a valid email address.';
    if (mb_strlen($data['message']) < 10)                   $errors['message'] = 'A little more detail helps us route this to the right desk.';

    if (!$errors) {
        Message::create($data);
        flash('success', 'Thanks. Your message is with our team and we will reply shortly.');
        redirect('/contact');
    }

    flash_old($data);
}

$bad      = fn(string $field) => isset($errors[$field]) ? ' input-error' : '';
$whatsapp = Setting::get('whatsapp');

partial('header', [
    'title'       => page_title('Contact us'),
    'description' => 'Reach the KACHI sales, ordering and dispatch desks in Asaba, Delta State.',
]);
?>

<section class="relative isolate overflow-hidden bg-navy-800">
    <div class="absolute inset-0 bg-grid opacity-30"></div>
    <div class="absolute -right-24 -bottom-24 size-72 rounded-full bg-orange-500/20 blur-3xl"></div>

    <div class="shell relative py-12 sm:py-16">
        <nav class="mb-5 flex items-center gap-1.5 text-xs text-navy-200" aria-label="Breadcrumb">
            <a class="inline-flex min-h-6 items-center transition-colors hover:text-white" href="<?= url('/') ?>">Home</a>
            <?= icon('chevron-right', 'size-3') ?>
            <span class="text-white">Contact</span>
        </nav>
        <div class="max-w-2xl">
            <p class="eyebrow eyebrow-light"><?= icon('message', 'size-3.5') ?>We are listening</p>
            <h1 class="h-section mt-4 text-white">Talk to us</h1>
            <p class="mt-4 leading-relaxed text-navy-100">
                Sales, deliveries, credit accounts or a problem with an order.
                Anything that needs a person, send it here.
            </p>
        </div>
    </div>
</section>

<!-- Contact cards -->
<section class="section-sm">
    <div class="shell">
        <div class="grid gap-4 sm:grid-cols-3">
            <a href="tel:<?= e(preg_replace('/[^0-9+]/', '', Setting::get('contact_phone', APP_PHONE))) ?>"
               class="card card-hover flex items-start gap-4 p-6">
                <span class="grid size-11 shrink-0 place-items-center rounded-xl bg-orange-50 text-orange-600">
                    <?= icon('phone-call', 'size-5') ?>
                </span>
                <span>
                    <span class="block text-xs font-bold uppercase tracking-wider text-ink-400">Call us</span>
                    <span class="mt-1 block font-display font-bold text-navy-700"><?= e(Setting::get('contact_phone', APP_PHONE)) ?></span>
                    <span class="mt-0.5 block text-xs text-ink-400"><?= e(Setting::get('opening_hours', 'Mon - Sat')) ?></span>
                </span>
            </a>

            <a href="mailto:<?= e(Setting::get('contact_email', APP_EMAIL)) ?>"
               class="card card-hover flex items-start gap-4 p-6">
                <span class="grid size-11 shrink-0 place-items-center rounded-xl bg-navy-50 text-navy-600">
                    <?= icon('mail', 'size-5') ?>
                </span>
                <span class="min-w-0">
                    <span class="block text-xs font-bold uppercase tracking-wider text-ink-400">Email us</span>
                    <span class="mt-1 block break-words font-display font-bold text-navy-700"><?= e(Setting::get('contact_email', APP_EMAIL)) ?></span>
                    <span class="mt-0.5 block text-xs text-ink-400">Replies within one business day</span>
                </span>
            </a>

            <?php if ($whatsapp): ?>
                <a href="https://wa.me/<?= e($whatsapp) ?>" rel="noopener" class="card card-hover flex items-start gap-4 p-6">
                    <span class="grid size-11 shrink-0 place-items-center rounded-xl bg-[#25D366]/15 text-[#128C4A]">
                        <?= icon('message', 'size-5') ?>
                    </span>
                    <span>
                        <span class="block text-xs font-bold uppercase tracking-wider text-ink-400">WhatsApp</span>
                        <span class="mt-1 block font-display font-bold text-navy-700">Chat with sales</span>
                        <span class="mt-0.5 block text-xs text-ink-400">Fastest for quick questions</span>
                    </span>
                </a>
            <?php endif; ?>
        </div>
    </div>
</section>

<section class="section-sm pb-20">
    <div class="shell">
        <div class="grid gap-8 lg:grid-cols-[minmax(0,1fr)_22rem] lg:items-start">

            <!-- Form -->
            <div class="card card-pad">
                <h2 class="text-lg">Send a message</h2>

                <?php if ($errors): ?>
                    <div class="alert alert-error mt-4" role="alert">
                        <?= icon('alert', 'size-5 shrink-0') ?>
                        <span>Please fix the <?= count($errors) ?> highlighted field<?= count($errors) === 1 ? '' : 's' ?> below.</span>
                    </div>
                <?php endif; ?>

                <form method="post" class="mt-5">
                    <?= csrf_field() ?>

                    <div class="grid gap-x-5 sm:grid-cols-2">
                        <div class="field">
                            <label class="label" for="name">Your name <span class="req">*</span></label>
                            <input class="input<?= $bad('name') ?>" id="name" name="name"
                                   value="<?= e(old('name', auth_user()['name'] ?? '')) ?>" autocomplete="name" required>
                            <?php if (isset($errors['name'])): ?>
                                <p class="error-text"><?= icon('alert', 'size-4 shrink-0 mt-px') ?><?= e($errors['name']) ?></p>
                            <?php endif; ?>
                        </div>
                        <div class="field">
                            <label class="label" for="phone">Phone</label>
                            <input class="input" type="tel" id="phone" name="phone"
                                   value="<?= e(old('phone', auth_user()['phone'] ?? '')) ?>" autocomplete="tel">
                        </div>
                        <div class="field">
                            <label class="label" for="email">Email <span class="req">*</span></label>
                            <input class="input<?= $bad('email') ?>" type="email" id="email" name="email"
                                   value="<?= e(old('email', auth_user()['email'] ?? '')) ?>" autocomplete="email" required>
                            <?php if (isset($errors['email'])): ?>
                                <p class="error-text"><?= icon('alert', 'size-4 shrink-0 mt-px') ?><?= e($errors['email']) ?></p>
                            <?php endif; ?>
                        </div>
                        <div class="field">
                            <label class="label" for="subject">Subject</label>
                            <select class="select" id="subject" name="subject">
                                <?php foreach ([
                                    'New enquiry', 'Existing order', 'Bulk quote', 'Logistics services',
                                    'Credit account', 'Complaint', 'Something else',
                                ] as $subject): ?>
                                    <option value="<?= e($subject) ?>" <?= old('subject') === $subject ? 'selected' : '' ?>><?= e($subject) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="field">
                        <label class="label" for="message">Message <span class="req">*</span></label>
                        <textarea class="textarea<?= $bad('message') ?>" id="message" name="message" rows="6" required><?= e(old('message')) ?></textarea>
                        <?php if (isset($errors['message'])): ?>
                            <p class="error-text"><?= icon('alert', 'size-4 shrink-0 mt-px') ?><?= e($errors['message']) ?></p>
                        <?php else: ?>
                            <p class="hint">Include a reference number if this is about an existing order.</p>
                        <?php endif; ?>
                    </div>

                    <button class="btn btn-primary btn-lg gap-2" type="submit">
                        <?= icon('mail', 'size-5') ?>Send message
                    </button>
                </form>
            </div>

            <!-- Aside -->
            <aside class="space-y-4 lg:sticky lg:top-28">
                <div class="card card-pad">
                    <h2 class="flex items-center gap-2 text-base">
                        <?= icon('map-pin', 'size-5 text-orange-500') ?>Head office
                    </h2>
                    <address class="mt-3 not-italic text-sm leading-relaxed text-ink-500">
                        <?= e(Setting::get('address', APP_ADDRESS)) ?>
                    </address>
                    <dl class="mt-4 divide-y divide-ink-100 border-t border-ink-100 text-sm">
                        <div class="flex justify-between gap-4 py-3">
                            <dt class="text-ink-500">Hours</dt>
                            <dd class="text-right font-semibold text-navy-700"><?= e(Setting::get('opening_hours', 'Mon - Sat')) ?></dd>
                        </div>
                        <div class="flex justify-between gap-4 py-3">
                            <dt class="text-ink-500">Registration</dt>
                            <dd class="text-right font-semibold text-navy-700"><?= e(Setting::get('cac_number', 'RC 1234567')) ?></dd>
                        </div>
                    </dl>
                </div>

                <div class="card card-pad">
                    <h2 class="text-base">Faster routes</h2>
                    <ul class="mt-4 space-y-3 text-sm">
                        <li class="flex gap-3">
                            <?= icon('route', 'size-4 shrink-0 mt-0.5 text-orange-500') ?>
                            <span class="text-ink-600">Chasing a delivery? <a class="link-quiet" href="<?= url('/track') ?>">Track it</a> for a live status.</span>
                        </li>
                        <li class="flex gap-3">
                            <?= icon('receipt', 'size-4 shrink-0 mt-0.5 text-orange-500') ?>
                            <span class="text-ink-600">Need bulk pricing? <a class="link-quiet" href="<?= url('/quote') ?>">Send the list</a> and skip the back and forth.</span>
                        </li>
                        <li class="flex gap-3">
                            <?= icon('user', 'size-4 shrink-0 mt-0.5 text-orange-500') ?>
                            <span class="text-ink-600">Existing account? <a class="link-quiet" href="<?= url('/login') ?>">Sign in</a> to see your full history.</span>
                        </li>
                    </ul>
                </div>
            </aside>
        </div>
    </div>
</section>

<?php partial('footer'); ?>
