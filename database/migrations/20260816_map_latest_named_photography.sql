-- Exact product matches from the client's latest named photography. Existing
-- back-office uploads remain untouched.

UPDATE products SET image = 'img/photos/prod-basmati-rice.jpg'     WHERE slug = 'basmati-rice-5kg' AND (image IS NULL OR image = '');
UPDATE products SET image = 'img/photos/prod-curry-powder.jpg'     WHERE slug = 'curry-powder-1kg' AND (image IS NULL OR image = '');
UPDATE products SET image = 'img/photos/prod-dried-thyme.jpg'      WHERE slug = 'dried-thyme-1kg' AND (image IS NULL OR image = '');
UPDATE products SET image = 'img/photos/prod-fresh-beef.jpg'       WHERE slug = 'fresh-beef-20kg' AND (image IS NULL OR image = '');
UPDATE products SET image = 'img/photos/prod-frozen-croaker.jpg'   WHERE slug = 'frozen-croaker-fish-20kg' AND (image IS NULL OR image = '');
UPDATE products SET image = 'img/photos/prod-frozen-mixed-veg.jpg' WHERE slug = 'frozen-mixed-vegetables-10kg' AND (image IS NULL OR image = '');
UPDATE products SET image = 'img/photos/prod-frozen-titus.jpg'     WHERE slug = 'frozen-titus-fish-20kg' AND (image IS NULL OR image = '');
UPDATE products SET image = 'img/photos/prod-garden-egg.jpg'       WHERE slug = 'garden-egg-basket' AND (image IS NULL OR image = '');
UPDATE products SET image = 'img/photos/prod-groundnut-oil.jpg'    WHERE slug = 'groundnut-oil-25l' AND (image IS NULL OR image = '');
UPDATE products SET image = 'img/photos/prod-ijebu-garri.jpg'      WHERE slug = 'ijebu-garri-50kg' AND (image IS NULL OR image = '');
UPDATE products SET image = 'img/photos/prod-indomie.jpg'          WHERE slug = 'indomie-noodles-carton' AND (image IS NULL OR image = '');
UPDATE products SET image = 'img/photos/prod-table-salt.jpg'       WHERE slug = 'iodised-table-salt-50kg' AND (image IS NULL OR image = '');
UPDATE products SET image = 'img/photos/prod-irish-potato.jpg'     WHERE slug = 'irish-potato-50kg' AND (image IS NULL OR image = '');
UPDATE products SET image = 'img/photos/prod-margarine.jpg'        WHERE slug = 'margarine-5kg' AND (image IS NULL OR image = '');
UPDATE products SET image = 'img/photos/prod-red-onions.jpg'       WHERE slug = 'red-onions-100kg' AND (image IS NULL OR image = '');
UPDATE products SET image = 'img/photos/prod-suya-spice.jpg'       WHERE slug = 'suya-spice-2kg' AND (image IS NULL OR image = '');
UPDATE products SET image = 'img/photos/prod-white-garri.jpg'      WHERE slug = 'white-garri-50kg' AND (image IS NULL OR image = '');

-- Newly identified fresh market lines use the storefront's price-on-request
-- flow until a current price is entered in the back office.
INSERT IGNORE INTO products
    (category_id, name, slug, sku, summary, description, origin, unit, retail_price,
     wholesale_price, wholesale_min_qty, sale_price, min_order, stock_qty, image, is_featured, is_active)
VALUES
((SELECT id FROM categories WHERE slug = 'proteins'), 'Assorted Meat', 'assorted-meat', 'KFL-PR-012', 'Clean assorted meat for soups and stews.', 'A selection of properly cleaned beef offal supplied by weight for home and commercial kitchens.', 'Delta State', 'kg', 0, NULL, 10, NULL, 1, 0, 'img/photos/prod-assorted-meat.jpg', 0, 1),
((SELECT id FROM categories WHERE slug = 'vegetables'), 'Bell Peppers', 'bell-peppers', 'KFL-VG-011', 'Fresh mixed-colour bell peppers.', 'Firm red, yellow and green bell peppers supplied by weight for homes, restaurants and events.', 'Nigeria', 'kg', 0, NULL, 10, NULL, 1, 0, 'img/photos/prod-bell-peppers.jpg', 0, 1),
((SELECT id FROM categories WHERE slug = 'vegetables'), 'Fresh Cucumbers', 'fresh-cucumbers', 'KFL-VG-012', 'Firm fresh cucumbers supplied by weight.', 'Fresh cucumbers selected for firmness and delivered in household or catering quantities.', 'Nigeria', 'kg', 0, NULL, 10, NULL, 1, 0, 'img/photos/prod-cucumber.jpg', 0, 1),
((SELECT id FROM categories WHERE slug = 'vegetables'), 'Fresh Cabbage', 'fresh-cabbage', 'KFL-VG-013', 'Firm fresh cabbage heads.', 'Fresh cabbage selected for tight heads and clean outer leaves.', 'Nigeria', 'head', 0, NULL, 10, NULL, 1, 0, 'img/photos/prod-cabbage.jpg', 0, 1),
((SELECT id FROM categories WHERE slug = 'vegetables'), 'Fresh Okra', 'fresh-okra', 'KFL-VG-014', 'Tender fresh okra for soups and stews.', 'Fresh okra selected for tenderness and supplied by weight.', 'Nigeria', 'kg', 0, NULL, 10, NULL, 1, 0, 'img/photos/prod-okra.jpg', 0, 1),
((SELECT id FROM categories WHERE slug = 'vegetables'), 'Spring Onions', 'spring-onions', 'KFL-VG-015', 'Fresh spring onions in tied bundles.', 'Crisp spring onions supplied in fresh market bundles.', 'Nigeria', 'bundle', 0, NULL, 10, NULL, 1, 0, 'img/photos/prod-spring-onions.jpg', 0, 1),
((SELECT id FROM categories WHERE slug = 'proteins'), 'Periwinkle', 'periwinkle', 'KFL-PR-013', 'Clean periwinkle for traditional soups.', 'Periwinkle supplied by weight for home, restaurant and catering orders.', 'South South Nigeria', 'kg', 0, NULL, 10, NULL, 1, 0, 'img/photos/prod-periwinkle.jpg', 0, 1);
