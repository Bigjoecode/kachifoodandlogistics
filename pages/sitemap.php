<?php
/**
 * XML sitemap built from live catalogue data, so new products and categories
 * appear without anyone remembering to update a static file.
 */
header('Content-Type: application/xml; charset=utf-8');

$base = rtrim(APP_DOMAIN, '/');

/** [path, changefreq, priority, lastmod] */
$urls = [
    ['/',          'weekly',  '1.0', null],
    ['/products',  'daily',   '0.9', null],
    ['/logistics', 'monthly', '0.9', null],
    ['/services',  'monthly', '0.7', null],
    ['/quote',     'monthly', '0.7', null],
    ['/about',     'yearly',  '0.5', null],
    ['/faqs',      'monthly', '0.6', null],
    ['/contact',   'yearly',  '0.5', null],
    ['/track',     'yearly',  '0.4', null],
];

foreach (Category::all() as $category) {
    $urls[] = ['/category/' . $category['slug'], 'weekly', '0.8', null];
}

foreach (Db::all('SELECT slug, updated_at FROM products WHERE is_active = 1 ORDER BY updated_at DESC') as $product) {
    $urls[] = ['/products/' . $product['slug'], 'weekly', '0.7', date('Y-m-d', strtotime($product['updated_at']))];
}

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
<?php foreach ($urls as [$path, $changefreq, $priority, $lastmod]): ?>
    <url>
        <loc><?= e($base . $path) ?></loc>
        <?php if ($lastmod): ?><lastmod><?= e($lastmod) ?></lastmod><?php endif; ?>
        <changefreq><?= $changefreq ?></changefreq>
        <priority><?= $priority ?></priority>
    </url>
<?php endforeach; ?>
</urlset>
