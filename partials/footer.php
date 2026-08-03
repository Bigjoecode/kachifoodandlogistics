</main>

<footer class="mt-auto bg-navy-900 text-navy-100">
    <div class="shell py-14 lg:py-16">
        <div class="grid gap-10 lg:grid-cols-12 lg:gap-8">

            <div class="lg:col-span-4">
                <img src="<?= asset('img/logo-white.png') ?>" alt="<?= e(APP_NAME) ?>"
                     class="h-11 w-auto" width="1136" height="392" loading="lazy">
                <p class="mt-5 max-w-sm text-sm leading-relaxed text-navy-200">
                    Wholesale and retail foodstuff supply for hotels, restaurants, schools, churches,
                    supermarkets and homes &mdash; backed by our own fleet for truck hire, van hire and
                    scheduled delivery across <?= e(APP_STATE) ?>.
                </p>
                <p class="mt-4 flex items-center gap-2 text-sm text-navy-200">
                    <?= icon('clock', 'size-4 shrink-0 text-orange-400') ?>
                    <?= e(Setting::get('opening_hours', 'Mon - Sat')) ?>
                </p>
            </div>

            <div class="lg:col-span-2">
                <h2 class="mb-4 text-xs font-bold uppercase tracking-[0.14em] text-white">Shop</h2>
                <ul class="-my-1 text-sm">
                    <li><a class="flex min-h-11 items-center text-navy-200 transition-colors hover:text-orange-400" href="<?= url('/products') ?>">Full catalogue</a></li>
                    <?php foreach (array_slice(Category::all(), 0, 5) as $footerCat): ?>
                        <li>
                            <a class="flex min-h-11 items-center text-navy-200 transition-colors hover:text-orange-400"
                               href="<?= url('/category/' . $footerCat['slug']) ?>"><?= e($footerCat['name']) ?></a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <div class="lg:col-span-2">
                <h2 class="mb-4 text-xs font-bold uppercase tracking-[0.14em] text-white">Company</h2>
                <ul class="-my-1 text-sm">
                    <?php foreach ([
                        '/logistics' => 'Book logistics',
                        '/services'  => 'Our services',
                        '/quote'     => 'Request a quote',
                        '/track'     => 'Track an order',
                        '/about'     => 'About us',
                        '/faqs'      => 'FAQs',
                        '/contact'   => 'Contact',
                    ] as $href => $label): ?>
                        <li><a class="flex min-h-11 items-center text-navy-200 transition-colors hover:text-orange-400" href="<?= url($href) ?>"><?= $label ?></a></li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <div class="lg:col-span-4">
                <h2 class="mb-4 text-xs font-bold uppercase tracking-[0.14em] text-white">Get in touch</h2>
                <ul class="space-y-3 text-sm">
                    <li class="flex items-start gap-3">
                        <?= icon('map-pin', 'size-4 shrink-0 mt-0.5 text-orange-400') ?>
                        <span class="text-navy-200"><?= e(Setting::get('address', APP_ADDRESS)) ?></span>
                    </li>
                    <li class="flex items-start gap-3">
                        <?= icon('phone-call', 'size-4 shrink-0 mt-0.5 text-orange-400') ?>
                        <span class="flex min-h-11 flex-col justify-center gap-1">
                            <?php $footerPhone = Setting::get('contact_phone', APP_PHONE); ?>
                            <a class="font-semibold text-white transition-colors hover:text-orange-400"
                               href="tel:<?= e(preg_replace('/[^0-9+]/', '', $footerPhone)) ?>"><?= e($footerPhone) ?></a>
                            <?php if ($footerPhoneAlt = Setting::get('contact_phone_alt', APP_PHONE_ALT)): ?>
                                <a class="font-semibold text-white transition-colors hover:text-orange-400"
                                   href="tel:<?= e(preg_replace('/[^0-9+]/', '', $footerPhoneAlt)) ?>"><?= e($footerPhoneAlt) ?></a>
                            <?php endif; ?>
                        </span>
                    </li>
                    <li class="flex items-start gap-3">
                        <?= icon('mail', 'size-4 shrink-0 mt-0.5 text-orange-400') ?>
                        <a class="flex min-h-11 items-center break-all text-navy-200 transition-colors hover:text-orange-400"
                           href="mailto:<?= e(Setting::get('contact_email', APP_EMAIL)) ?>">
                            <?= e(Setting::get('contact_email', APP_EMAIL)) ?>
                        </a>
                    </li>
                </ul>

                <?php if ($whatsapp = Setting::get('whatsapp')): ?>
                    <a class="btn btn-accent mt-6 gap-2" href="https://wa.me/<?= e($whatsapp) ?>" rel="noopener">
                        <?= icon('message', 'size-5') ?>Chat on WhatsApp
                    </a>
                <?php endif; ?>

                <?php if ($socials = social_links()): ?>
                    <div class="mt-6">
                        <h3 class="mb-3 text-xs font-bold uppercase tracking-[0.14em] text-white">Follow us</h3>
                        <ul class="flex flex-wrap gap-2">
                            <?php foreach ($socials as $social): ?>
                                <li>
                                    <a href="<?= e($social['url']) ?>" target="_blank" rel="noopener noreferrer"
                                       class="grid size-11 place-items-center rounded-xl border border-white/15 text-navy-100
                                              transition-colors duration-200 hover:border-orange-400 hover:bg-white/10 hover:text-orange-400">
                                        <?= icon($social['icon'], 'size-5', $social['label']) ?>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="mt-12 border-t border-white/10 pt-6">
            <p class="mb-4 text-xs font-bold uppercase tracking-[0.14em] text-navy-300">Delivering to</p>
            <div class="flex flex-wrap gap-2">
                <?php foreach (service_areas() as $area): ?>
                    <span class="rounded-full border border-white/15 px-3 py-1 text-xs font-semibold text-navy-100"><?= e($area) ?></span>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="mt-8 flex flex-col gap-3 border-t border-white/10 pt-6 text-xs text-navy-300 sm:flex-row sm:items-center sm:justify-between">
            <p>&copy; <?= date('Y') ?> <?= e(Setting::get('site_name', APP_NAME)) ?>. All rights reserved.</p>
            <p class="flex items-center gap-2">
                <?= icon('shield', 'size-3.5 text-orange-400') ?>
                <?= e(Setting::get('cac_number', APP_CAC_NUMBER)) ?> &middot; <?= e(APP_CITY) ?>, <?= e(APP_STATE) ?>
            </p>
        </div>
    </div>
</footer>

<?php if ($whatsappFloat = Setting::get('whatsapp')): ?>
    <a href="https://wa.me/<?= e($whatsappFloat) ?>" rel="noopener"
       class="fixed bottom-5 right-5 z-40 flex min-h-12 items-center gap-2 rounded-full bg-[#25D366] px-5 py-3
              text-sm font-bold text-[#04310f] shadow-deep transition-transform duration-200 hover:-translate-y-0.5 no-print">
        <?= icon('message', 'size-5') ?>
        <span class="hidden sm:inline">WhatsApp us</span>
    </a>
<?php endif; ?>

<script src="<?= asset('js/app.js') ?>" defer></script>
</body>
</html>
