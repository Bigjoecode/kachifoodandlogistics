-- Fixed-price bulk food packages supplied by the client.

INSERT INTO categories (name, slug, description, icon, sort_order, is_active)
SELECT 'Bulk Food Packages', 'bulk-food-packages',
       'Ready-made foodstuff bundles for families, celebrations and monthly household restocking.',
       'BP', 1, 1
WHERE NOT EXISTS (SELECT 1 FROM categories WHERE slug = 'bulk-food-packages');

INSERT IGNORE INTO products
    (category_id, name, slug, sku, summary, description, origin, unit, retail_price,
     wholesale_price, wholesale_min_qty, sale_price, min_order, stock_qty, image, is_featured, is_active)
VALUES
((SELECT id FROM categories WHERE slug = 'bulk-food-packages'),
 'Complete Bulk Food Package', 'complete-bulk-food-package', 'KFL-BP-001',
 'Our largest ready-made package, combining staples, proteins, seasonings and household supplies.',
 '50kg Optimum rice\n25L Kings vegetable oil\n10L red palm oil\n4 custards of brown beans\n5kg Semovita\n4kg poundo yam\n1 bunch of plantain\n10 yam tubers\n4 packs of salt\n4 packs of Knorr beef cubes\n4 packs of Knorr chicken cubes\n1 carton of round-tin tomatoes\n2 cartons of Super Pack Indomie\n1 custard of fresh tomatoes\nFresh pepper\n1 custard of onions\n1 custard of crayfish\n10 dried catfish\n5 stockfish heads\n2 packs of Vival detergent\n5 Vival tablet soaps\n2 packs of Peak milk refill powder\n2 packs of Milo refill\n2 cups of custard powder\n10 yam tubers\n6 custards of yellow garri\n1 carton of Golden Penny spaghetti\n1 carton of Golden Penny Twist\n4 cups of dry pepper\n2 crates of eggs\n1 custard of hand-peeled egusi\n1/2 custard of ogbono',
 'Delta State', 'package', 693600, NULL, 10, NULL, 1, 50, 'img/photos/stock-sacks.jpg', 0, 1),

((SELECT id FROM categories WHERE slug = 'bulk-food-packages'),
 'Premium Family Food Package', 'premium-family-food-package', 'KFL-BP-002',
 'A substantial family restock with rice, beans, oils, proteins, noodles and seasonings.',
 '2 bags of 50kg Optimum rice\n5 custards of white beans\n25L Kings vegetable oil\n20L red palm oil\nHalf portion of a full goat\n1 live chicken\n4 packs of Knorr cubes\n1 custard of onions\n2 cartons of Super Pack Indomie\n4 packs of salt\n24 round-tin tomatoes\n10 wraps of odourless fufu\n2kg wheat\n1 carton of Golden Penny spaghetti\n10 Golden Penny Twist packs\n2 packs of Vival detergent\n3 Vival tablet soaps\n1 cup of custard powder\n15 yam tubers\n1/2 bunch of plantain\n2 cups of curry powder\n2 cups of thyme',
 'Delta State', 'package', 451000, NULL, 10, NULL, 1, 50, 'img/photos/stock-basins.jpg', 0, 1),

((SELECT id FROM categories WHERE slug = 'bulk-food-packages'),
 'Party & Home Food Package', 'party-home-food-package', 'KFL-BP-003',
 'A balanced party and household bundle with rice, pasta, soup ingredients, drinks and protein.',
 '1 carton of Party Jollof\n1 carton of Derica\n5L Kings vegetable oil\n4L red palm oil\n1 carton of Golden Penny spaghetti\n2 custards of beans\n1 carton of Super Pack Indomie\n50kg WAW rice\n4kg poundo yam\n2 custards of yellow garri\n1 custard of brown beans\n1 pack of Knorr cubes\n1/2 custard of onions\n1 pack of salt\n5 cups of ground melon (egusi)\n5 cups of ground ogbono\n1/2 custard of ground crayfish\n1 pack of Peak milk refill powder\n1 pack of Milo refill\n1 cup of custard powder\n1 pack of sugar\n1 pack of Vival detergent\n3 Vival tablet soaps\n1 live chicken\n5 smoked catfish\nRipe plantain',
 'Delta State', 'package', 403500, NULL, 10, NULL, 1, 50, 'img/photos/stall.jpg', 0, 1),

((SELECT id FROM categories WHERE slug = 'bulk-food-packages'),
 'Essential Pantry Package', 'essential-pantry-package', 'KFL-BP-004',
 'An essential pantry restock with rice, oils, pasta, beans, seasonings and breakfast items.',
 '25kg CAP rice\n3kg Kings vegetable oil refill\n3 yam tubers\n2L red palm oil\n1 carton of Super Pack Indomie\n8 Golden Penny spaghetti packs\n2 cups of ground ogbono\n2 cups of ground egusi\n2kg semolina\n1 custard of brown beans\n1 pack of Vival detergent\n2 Vival tablet soaps\n1 pack of Knorr chicken cubes\n1 pack of salt\nOnions\n1 Milo refill\n1 Dano milk refill\n1 pack of sugar',
 'Delta State', 'package', 174500, NULL, 10, NULL, 1, 50, 'img/photos/shelf-packaged.jpg', 0, 1),

((SELECT id FROM categories WHERE slug = 'bulk-food-packages'),
 'Starter Pantry Package', 'starter-pantry-package', 'KFL-BP-005',
 'A compact starter bundle covering rice, noodles, pasta, cooking oil and breakfast staples.',
 '25kg Royal Stallion rice\n8 Golden Penny spaghetti packs\n1 carton of Indomitable noodles\n3 rolls of Sonia sachet tomatoes\n1L Kings vegetable oil\n1 pack of Cabin biscuits\n1 Milo refill\n1 Three Crowns milk refill\n1 pack of Golden Morn\n2 tins of Titus fish\n1 pack of Knorr cubes',
 'Delta State', 'package', 110000, NULL, 10, NULL, 1, 50, 'img/photos/oil-cartons.jpg', 0, 1);
