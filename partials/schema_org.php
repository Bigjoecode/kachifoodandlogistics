<?php
/**
 * Site-wide structured data: who we are, where we are, and what we cover.
 * Page-specific schema (Product, FAQPage, BreadcrumbList) is passed into the
 * header separately via the $schema variable.
 */
$phone = Setting::get('contact_phone', APP_PHONE);

$localBusiness = [
    '@context'    => 'https://schema.org',
    '@type'       => ['LocalBusiness', 'Organization'],
    'name'        => Setting::get('site_name', APP_NAME),
    'description' => APP_TAGLINE,
    'url'         => APP_DOMAIN,
    'telephone'   => $phone,
    'email'       => Setting::get('contact_email', APP_EMAIL),
    'identifier'  => [
        '@type'      => 'PropertyValue',
        'propertyID' => 'CAC Registration Number',
        'value'      => Setting::get('cac_number', APP_CAC_NUMBER),
    ],
    'address'     => [
        '@type'           => 'PostalAddress',
        'streetAddress'   => Setting::get('address', APP_ADDRESS),
        'addressLocality' => APP_CITY,
        'addressRegion'   => APP_STATE,
        'addressCountry'  => 'NG',
    ],
    'areaServed'  => array_map(
        fn($area) => ['@type' => 'City', 'name' => $area],
        service_areas()
    ),
    'openingHours' => Setting::get('opening_hours', 'Mo-Sa 07:30-19:00'),
    'priceRange'   => 'NGN',
];

// Verified social profiles help Google tie the site to the brand's accounts.
if ($sameAs = array_column(social_links(), 'url')) {
    $localBusiness['sameAs'] = $sameAs;
}

$website = [
    '@context'        => 'https://schema.org',
    '@type'           => 'WebSite',
    'name'            => Setting::get('site_name', APP_NAME),
    'url'             => APP_DOMAIN,
    'potentialAction' => [
        '@type'       => 'SearchAction',
        'target'      => ['@type' => 'EntryPoint', 'urlTemplate' => APP_DOMAIN . '/products?q={search_term_string}'],
        'query-input' => 'required name=search_term_string',
    ],
];
?>
<script type="application/ld+json"><?= json_encode([$localBusiness, $website], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?></script>
