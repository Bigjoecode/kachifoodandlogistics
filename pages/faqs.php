<?php
/**
 * FAQ page. The same array drives the visible accordion and the FAQPage
 * structured data, so the two can never drift apart.
 */
$groups = [
    'Ordering foodstuff' => [
        ['Where can I buy foodstuff in bulk in Asaba?',
         'KACHI supplies wholesale and retail foodstuff from our warehouse on Nnebisi Road, Asaba. You can order online from the catalogue, send a bulk list through the quote form, or message us on WhatsApp and we will price it for you.'],
        ['What is the difference between your retail and wholesale prices?',
         'Every product carries both. The retail price applies to small quantities, and the wholesale price kicks in automatically once your line quantity reaches the threshold shown on the product page. You do not need to ask or negotiate for it.'],
        ['Is there a minimum order?',
         'Most lines have a minimum of one unit, and the unit is stated on each product, for example a 50kg bag or a carton of 20. Delivery is free once your order passes the free-delivery threshold shown in your cart.'],
        ['Can I order without creating an account?',
         'Yes. Checkout works as a guest. Creating an account simply saves your delivery details, keeps your order history in one place and lets you reorder in a click.'],
        ['Do you supply hotels, schools and churches?',
         'That is most of our business. Hotels, restaurants, schools, churches, hospitals, camps and event vendors order from us on standing schedules. Approved trade accounts can also order on 14 or 30 day credit terms.'],
    ],

    'Delivery and coverage' => [
        ['Which towns do you deliver to?',
         'We run scheduled routes across Delta State, including Asaba, Warri, Sapele, Ughelli, Abraka, Agbor, Oghara and Effurun. Interstate delivery is available on request and quoted by route.'],
        ['How fast is delivery?',
         'Within Asaba we turn most orders around in 24 hours, and same-day is available if you order before 10am. Elsewhere in Delta State it is typically 24 to 48 hours, and interstate is 48 to 96 hours depending on distance.'],
        ['How much does delivery cost?',
         'A flat fee applies within Asaba, and it is waived once your order value passes the free-delivery threshold shown in your cart. Deliveries outside Delta State are quoted per route before dispatch, and we tell you the cost before anything ships.'],
        ['Can I choose the delivery day and time?',
         'Yes. At checkout you pick a delivery date and a time window, and you choose the service level, including refrigerated delivery for frozen and chilled goods.'],
        ['How do I track my order?',
         'Every order and booking gets a reference. Enter it on the tracking page along with the email or phone number on the order, and you will see a timestamped timeline from confirmation to delivery.'],
    ],

    'Logistics, truck hire and van hire' => [
        ['Can I hire a truck without ordering food?',
         'Yes. The logistics booking form is completely independent of the catalogue. Book a motorcycle, mini van, cargo van, pickup, mini truck, large truck or flatbed on its own.'],
        ['How much does truck hire cost in Asaba?',
         'It depends on the vehicle, distance, weight and how urgently you need it. The booking form gives you an instant estimate before you submit, and our dispatch desk confirms the firm price against the actual route and load before any vehicle moves.'],
        ['Do you provide loading and offloading hands?',
         'Yes. Tick the loading crew option on the booking form and we send a two-man team with the vehicle for a flat additional fee.'],
        ['Do you handle office and house relocation?',
         'We do. Choose Office Relocation as the service type, describe what is being moved, and we will assign the right vehicle class and crew size.'],
        ['Will I know who is driving?',
         'Once dispatch assigns the job, the driver name and vehicle registration appear on your booking and on the tracking timeline.'],
    ],

    'Payment, quality and accounts' => [
        ['How do I pay?',
         'Bank transfer, cash on delivery for smaller Asaba orders, or credit terms for approved trade accounts. Nothing is charged online, so we confirm stock first and then invoice you with the payment details.'],
        ['How do I know the quality is good?',
         'Every intake lot is graded, weighed and moisture tested before we accept it, grains and groundnuts are aflatoxin screened, and stock rotates first-in first-out so nothing sits and ages. Temperature logs for frozen deliveries are available on request.'],
        ['What if something arrives wrong or damaged?',
         'Tell us on the day of delivery. We collect and replace it, or credit your account. Because every line is traceable back to its intake lot, we can also tell you where the problem started.'],
        ['How do I open a trade account?',
         'Register on the site, then contact our sales desk with your business details. Approved accounts get volume pricing, a named contact and credit terms.'],
    ],
];

/** Flatten the same content into FAQPage structured data. */
$faqEntities = [];
foreach ($groups as $questions) {
    foreach ($questions as [$question, $answer]) {
        $faqEntities[] = [
            '@type'          => 'Question',
            'name'           => $question,
            'acceptedAnswer' => ['@type' => 'Answer', 'text' => $answer],
        ];
    }
}

$schema = json_encode([
    '@context'   => 'https://schema.org',
    '@type'      => 'FAQPage',
    'mainEntity' => $faqEntities,
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

partial('header', [
    'title'       => page_title('Frequently asked questions'),
    'description' => 'Answers on buying foodstuff wholesale in Asaba, delivery across Delta State, truck and van hire pricing, payment options and trade accounts.',
    'schema'      => $schema,
]);

$groupIcons = [
    'Ordering foodstuff'                  => 'package',
    'Delivery and coverage'               => 'truck',
    'Logistics, truck hire and van hire'  => 'route',
    'Payment, quality and accounts'       => 'banknote',
];
?>

<section class="relative isolate overflow-hidden bg-navy-800">
    <div class="absolute inset-0 bg-grid opacity-30"></div>
    <div class="absolute -right-24 -top-24 size-72 rounded-full bg-orange-500/20 blur-3xl"></div>

    <div class="shell relative py-12 sm:py-16">
        <nav class="mb-5 flex items-center gap-1.5 text-xs text-navy-200" aria-label="Breadcrumb">
            <a class="inline-flex min-h-6 items-center transition-colors hover:text-white" href="<?= url('/') ?>">Home</a>
            <?= icon('chevron-right', 'size-3') ?>
            <span class="text-white">FAQs</span>
        </nav>
        <div class="max-w-2xl">
            <p class="eyebrow eyebrow-light"><?= icon('info', 'size-3.5') ?>Help centre</p>
            <h1 class="h-section mt-4 text-white">Frequently asked questions</h1>
            <p class="mt-4 leading-relaxed text-navy-100">
                Ordering, delivery, truck hire, payment and quality. If your question is not here,
                <a class="font-semibold text-orange-300 underline underline-offset-4 hover:text-orange-200" href="<?= url('/contact') ?>">send it to us</a>
                and we will answer it.
            </p>
        </div>
    </div>
</section>

<section class="section-sm pb-20">
    <div class="shell">
        <div class="grid gap-8 lg:grid-cols-[minmax(0,1fr)_20rem] lg:items-start">

            <div class="space-y-10">
                <?php foreach ($groups as $groupName => $questions): ?>
                    <section>
                        <h2 class="flex items-center gap-3 text-xl">
                            <span class="grid size-10 place-items-center rounded-xl bg-navy-50 text-navy-600">
                                <?= icon($groupIcons[$groupName] ?? 'info', 'size-5') ?>
                            </span>
                            <?= e($groupName) ?>
                        </h2>

                        <div class="mt-5 space-y-3">
                            <?php foreach ($questions as [$question, $answer]): ?>
                                <details class="group card overflow-hidden">
                                    <summary class="flex cursor-pointer list-none items-center justify-between gap-4 p-5 font-semibold text-navy-700 transition-colors hover:bg-ink-50">
                                        <span><?= e($question) ?></span>
                                        <span class="grid size-8 shrink-0 place-items-center rounded-lg bg-ink-100 text-navy-600 transition-transform duration-300 group-open:rotate-180">
                                            <?= icon('chevron-down', 'size-4') ?>
                                        </span>
                                    </summary>
                                    <p class="border-t border-ink-100 p-5 text-sm leading-relaxed text-ink-500"><?= e($answer) ?></p>
                                </details>
                            <?php endforeach; ?>
                        </div>
                    </section>
                <?php endforeach; ?>
            </div>

            <aside class="space-y-4 lg:sticky lg:top-28">
                <div class="card card-pad">
                    <h2 class="text-base">Still stuck?</h2>
                    <p class="mt-1.5 text-sm text-ink-500">
                        Our desk is open <?= e(Setting::get('opening_hours', 'Mon - Sat')) ?>.
                    </p>
                    <div class="mt-4 space-y-2 text-sm">
                        <a class="flex min-h-11 items-center gap-2.5 font-semibold text-navy-700 transition-colors hover:text-orange-600"
                           href="tel:<?= e(preg_replace('/[^0-9+]/', '', Setting::get('contact_phone', APP_PHONE))) ?>">
                            <?= icon('phone-call', 'size-4 text-orange-500') ?><?= e(Setting::get('contact_phone', APP_PHONE)) ?>
                        </a>
                        <a class="flex min-h-11 items-center gap-2.5 break-all text-ink-600 transition-colors hover:text-orange-600"
                           href="mailto:<?= e(Setting::get('contact_email', APP_EMAIL)) ?>">
                            <?= icon('mail', 'size-4 text-orange-500') ?><?= e(Setting::get('contact_email', APP_EMAIL)) ?>
                        </a>
                    </div>
                    <?php if ($whatsapp = Setting::get('whatsapp')): ?>
                        <a class="btn btn-outline btn-block mt-4 gap-2" href="https://wa.me/<?= e($whatsapp) ?>" rel="noopener">
                            <?= icon('message', 'size-4') ?>Ask on WhatsApp
                        </a>
                    <?php endif; ?>
                </div>

                <div class="card card-pad">
                    <h2 class="text-base">Quick links</h2>
                    <ul class="mt-4 space-y-1">
                        <?php foreach ([
                            ['/products',  'package', 'Browse the catalogue'],
                            ['/logistics', 'truck',   'Get a logistics estimate'],
                            ['/quote',     'receipt', 'Request bulk pricing'],
                            ['/track',     'route',   'Track an order'],
                        ] as [$href, $ico, $label]): ?>
                            <li>
                                <a class="flex min-h-11 items-center gap-3 rounded-lg px-2 text-sm font-medium text-ink-600 transition-colors hover:bg-ink-100 hover:text-navy-700"
                                   href="<?= url($href) ?>">
                                    <?= icon($ico, 'size-4 text-orange-500') ?><?= $label ?>
                                    <?= icon('chevron-right', 'ml-auto size-4 text-ink-300') ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </aside>
        </div>
    </div>
</section>

<?php partial('footer'); ?>
