-- Products identified by the client from the additional photography. A zero
-- retail price means "price on request" in the storefront because these fresh
-- market lines change price frequently.

INSERT IGNORE INTO products
    (category_id, name, slug, sku, summary, description, origin, unit, retail_price,
     wholesale_price, wholesale_min_qty, sale_price, min_order, stock_qty, image, is_featured, is_active)
VALUES
((SELECT id FROM categories WHERE slug = 'spices-seasonings'), 'Blended Pepper', 'blended-pepper', 'KFL-SP-007',
 'Fresh pepper blended and ready for cooking.', 'Prepared from fresh peppers in small batches. Request the current price for your preferred quantity.', 'Delta State', '1kg pack', 0, NULL, 10, NULL, 1, 0, 'img/photos/prod-blended-pepper.jpg', 0, 1),
((SELECT id FROM categories WHERE slug = 'vegetables'), 'Fresh Coconuts', 'fresh-coconuts', 'KFL-VG-006',
 'Mature fresh coconuts supplied in counted lots.', 'Whole mature coconuts selected for sound shells and good water content.', 'Delta State', 'lot of 10', 0, NULL, 5, NULL, 1, 0, 'img/photos/prod-coconut.jpg', 0, 1),
((SELECT id FROM categories WHERE slug = 'vegetables'), 'Fresh Curry Leaves', 'fresh-curry-leaves', 'KFL-VG-007',
 'Aromatic curry leaves harvested fresh.', 'Fresh curry leaves bundled for home, restaurant and catering orders.', 'Delta State', 'bundle', 0, NULL, 10, NULL, 1, 0, 'img/photos/prod-curry-leaf.jpg', 0, 1),
((SELECT id FROM categories WHERE slug = 'grains-dry-foods'), 'Custard Powder', 'custard-powder', 'KFL-GR-014',
 'Smooth custard powder for breakfast and catering.', 'Packaged custard powder available by tub or in larger quantities on request.', 'Nigeria', 'tub', 0, NULL, 6, NULL, 1, 0, 'img/photos/prod-custard.jpg', 0, 1),
((SELECT id FROM categories WHERE slug = 'spices-seasonings'), 'Fresh Ginger', 'fresh-ginger', 'KFL-SP-008',
 'Firm, aromatic fresh ginger roots.', 'Fresh ginger selected for size and freshness, supplied by weight.', 'Nigeria', 'kg', 0, NULL, 10, NULL, 1, 0, 'img/photos/prod-fresh-ginger.jpg', 0, 1),
((SELECT id FROM categories WHERE slug = 'vegetables'), 'Fresh Leafy Vegetables', 'fresh-leafy-vegetables', 'KFL-VG-008',
 'Fresh market vegetables for soups and stews.', 'Leafy vegetables sourced fresh and bundled for same-day or scheduled delivery.', 'Delta State', 'bundle', 0, NULL, 10, NULL, 1, 0, 'img/photos/prod-fresh-vegetables.jpg', 0, 1),
((SELECT id FROM categories WHERE slug = 'spices-seasonings'), 'Fresh Garlic', 'fresh-garlic', 'KFL-SP-009',
 'Clean, firm garlic bulbs supplied by weight.', 'Fresh garlic bulbs for homes, restaurants and institutional kitchens.', 'Nigeria', 'kg', 0, NULL, 10, NULL, 1, 0, 'img/photos/prod-garlic.jpg', 0, 1),
((SELECT id FROM categories WHERE slug = 'spices-seasonings'), 'Ogbono', 'ogbono', 'KFL-SP-010',
 'Clean ogbono for thick, flavourful soup.', 'Ogbono supplied whole or prepared in quantities for home and commercial kitchens.', 'Nigeria', 'kg', 0, NULL, 10, NULL, 1, 0, 'img/photos/prod-ogbono.jpg', 0, 1),
((SELECT id FROM categories WHERE slug = 'vegetables'), 'Fresh Oha Leaves', 'fresh-oha-leaves', 'KFL-VG-009',
 'Fresh oha leaves bundled for soup preparation.', 'Carefully selected fresh oha leaves available in household and catering quantities.', 'South East Nigeria', 'bundle', 0, NULL, 10, NULL, 1, 0, 'img/photos/prod-oha-leaf.jpg', 0, 1),
((SELECT id FROM categories WHERE slug = 'proteins'), 'Pomo', 'pomo', 'KFL-PR-009',
 'Clean pomo ready for further preparation.', 'Quality cow skin supplied by weight for homes, restaurants and food vendors.', 'Nigeria', 'kg', 0, NULL, 10, NULL, 1, 0, 'img/photos/prod-pomo.jpg', 0, 1),
((SELECT id FROM categories WHERE slug = 'vegetables'), 'Fresh Scent Leaves', 'fresh-scent-leaves', 'KFL-VG-010',
 'Fresh aromatic scent leaves for soups and stews.', 'Freshly sourced scent leaves bundled for quick delivery.', 'Delta State', 'bundle', 0, NULL, 10, NULL, 1, 0, 'img/photos/prod-scent-leaf.jpg', 0, 1),
((SELECT id FROM categories WHERE slug = 'proteins'), 'Snails', 'snails', 'KFL-PR-010',
 'Quality snails supplied in counted lots.', 'Snails selected for size and supplied for home, restaurant and event catering.', 'Nigeria', 'lot of 10', 0, NULL, 5, NULL, 1, 0, 'img/photos/prod-snails.jpg', 0, 1),
((SELECT id FROM categories WHERE slug = 'proteins'), 'Titus Fish Tin', 'titus-fish-tin', 'KFL-PR-011',
 'Convenient tinned Titus fish for retail and catering.', 'Tinned Titus fish available by the unit or carton. Request the current carton price.', 'Imported', 'tin', 0, NULL, 24, NULL, 1, 0, 'img/photos/prod-titus-tin.jpg', 0, 1);
