-- KACHI Foodstuff Supplies & Logistics — demo catalogue (Delta State).
-- Users are created by install.php so passwords get hashed properly.

INSERT INTO categories (id, name, slug, description, icon, sort_order) VALUES
(1, 'Grains & Dry Foods',    'grains-dry-foods',    'Rice, beans, garri, flours and pasta bought direct from mills and aggregators.', 'GD', 1),
(2, 'Vegetables',            'vegetables',          'Farm-fresh vegetables moved on a 24-hour cycle across Delta State.',             'VG', 2),
(3, 'Tubers',                'tubers',              'Yam, plantain and potatoes, hand-selected and counted into lots.',               'TB', 3),
(4, 'Oils',                  'oils',                'Palm, vegetable and groundnut oils in sealed kegs, plus spreads.',               'OL', 4),
(5, 'Proteins',              'proteins',            'Poultry, meat and fish, chilled or frozen, held at temperature throughout.',     'PR', 5),
(6, 'Spices & Seasonings',   'spices-seasonings',   'Salt, cubes, curry, thyme and the spice blends every kitchen runs on.',          'SP', 6),
(7, 'Frozen Foods',          'frozen-foods',        'Cold-chain lines held at -18C from the processor to your door.',                 'FZ', 7),
(8, 'Household Essentials',  'household-essentials','Water, detergents and cleaning supplies added to the same delivery.',            'HH', 8);

INSERT INTO products (category_id, name, slug, sku, summary, description, origin, unit, retail_price, wholesale_price, wholesale_min_qty, sale_price, min_order, stock_qty, is_featured, is_active) VALUES
-- Grains & Dry Foods -------------------------------------------------------
(1, 'Local Rice 50kg', 'local-rice-50kg', 'KFL-GR-001',
 'Abakaliki local rice, destoned twice and bagged fresh.',
 'Milled in Ebonyi and destoned twice before bagging. Full flavour, holds together when cooked in volume, and moisture is held under 14 percent so it stores well in ambient warehousing. Supplied in branded 50kg sacks.',
 'Ebonyi State', '50kg bag', 88000.00, 82500.00, 10, NULL, 1, 420, 1, 1),

(1, 'Foreign Parboiled Rice 50kg', 'foreign-parboiled-rice-50kg', 'KFL-GR-002',
 'Long grain imported parboiled rice, low broken-grain ratio.',
 'Consistent grain length with very little breakage, which is why hotels and event caterers keep coming back to it. Sealed 50kg bags, palletised on request.',
 'Imported', '50kg bag', 96000.00, 90000.00, 10, 92000.00, 1, 380, 1, 1),

(1, 'Parboiled Rice 25kg', 'parboiled-rice-25kg', 'KFL-GR-003',
 'Half-bag format for restaurants with limited storage.',
 'The same mill run as the 50kg line, packed in 25kg units for kitchens ordering weekly rather than monthly.',
 'Ebonyi State', '25kg bag', 47500.00, 44500.00, 10, NULL, 1, 310, 0, 1),

(1, 'Basmati Rice 5kg', 'basmati-rice-5kg', 'KFL-GR-004',
 'Aged long grain basmati for jollof and biryani service.',
 'Aged for a full year before milling, so the grain lengthens properly and stays separate. Popular with hotels and event caterers.',
 'Imported', '5kg pack', 18500.00, 16800.00, 12, NULL, 1, 240, 0, 1),

(1, 'Brown Honey Beans 50kg', 'brown-honey-beans-50kg', 'KFL-GR-005',
 'Oloyin beans, hand-sorted, sweet and quick cooking.',
 'Graded to remove stones and broken seed, then stored in ventilated bays. Stock rotates within 21 days so nothing sits and ages.',
 'Kano State', '50kg bag', 118000.00, 111000.00, 5, NULL, 1, 150, 1, 1),

(1, 'White Beans 50kg', 'white-beans-50kg', 'KFL-GR-006',
 'Clean white beans for schools and institutional catering.',
 'Uniform seed size with a low split rate, so it cooks evenly in large pots. The standard choice for school and camp feeding programmes.',
 'Kano State', '50kg bag', 104000.00, 97500.00, 5, NULL, 1, 130, 0, 1),

(1, 'White Garri 50kg', 'white-garri-50kg', 'KFL-GR-007',
 'Fine white garri processed in Delta State.',
 'Processed locally and sieved fine. Sun-dried and bagged the same day so it holds its crispness in storage.',
 'Delta State', '50kg bag', 52000.00, 48000.00, 5, NULL, 1, 200, 0, 1),

(1, 'Yellow Garri 50kg', 'yellow-garri-50kg', 'KFL-GR-008',
 'Palm-oil enriched yellow garri, coarse grade.',
 'Enriched with red palm oil during frying, which gives it the deep colour and richer taste. Coarse sieved for eba.',
 'Delta State', '50kg bag', 58000.00, 54000.00, 5, 55000.00, 1, 180, 1, 1),

(1, 'Ijebu Garri 50kg', 'ijebu-garri-50kg', 'KFL-GR-009',
 'Sour, coarse-grade Ijebu garri for soaking.',
 'Fermented to the traditional Ijebu profile and sieved coarse. Sharp and sour, which is exactly what it is meant to be.',
 'Ogun State', '50kg bag', 62000.00, 57500.00, 5, NULL, 1, 140, 0, 1),

(1, 'Semovita 10kg', 'semovita-10kg', 'KFL-GR-010',
 'Fine-milled semovita in catering packs.',
 'Consistent granulation for a smooth swallow that does not go lumpy. Packed in 10kg moisture-barrier bags.',
 'Lagos', '10kg bag', 21500.00, 19800.00, 12, NULL, 1, 260, 0, 1),

(1, 'Poundo Yam Flour 10kg', 'poundo-yam-flour-10kg', 'KFL-GR-011',
 'Instant pounded yam flour, no lumps.',
 'Made from graded white yam and milled fine. Mixes smooth in hot water without the fight.',
 'Benue State', '10kg bag', 27500.00, 25200.00, 10, NULL, 1, 190, 0, 1),

(1, 'Spaghetti 500g (Carton of 20)', 'spaghetti-500g-carton', 'KFL-GR-012',
 'Durum wheat spaghetti in catering cartons.',
 'Durum semolina pasta that keeps its bite after holding. Twenty 500g packs per carton.',
 'Lagos', 'carton of 20', 13500.00, 12400.00, 10, NULL, 1, 640, 0, 1),

(1, 'Indomie Noodles (Carton of 40)', 'indomie-noodles-carton', 'KFL-GR-013',
 'Chicken flavour instant noodles by the carton.',
 'Forty 70g packs per carton. Moves fast in shops and school canteens, so we hold deep stock.',
 'Nigeria', 'carton of 40', 9800.00, 9100.00, 20, NULL, 1, 900, 0, 1),

-- Vegetables ---------------------------------------------------------------
(2, 'Fresh Tomatoes (Basket)', 'fresh-tomatoes-basket', 'KFL-VG-001',
 'Field-fresh tomatoes moved within 24 hours of harvest.',
 'Picked at breaker stage and loaded the same evening so they arrive firm rather than bruised. Sold by the standard raffia basket, roughly 50kg.',
 'Plateau State', 'basket', 48000.00, 44000.00, 5, 45000.00, 1, 70, 1, 1),

(2, 'Fresh Pepper Rodo (Basket)', 'fresh-pepper-rodo-basket', 'KFL-VG-002',
 'Hot rodo pepper, graded and basket packed.',
 'Sorted for ripeness and packed in ventilated baskets so it does not sweat and spoil in transit.',
 'Kaduna State', 'basket', 42000.00, 38500.00, 5, NULL, 1, 65, 0, 1),

(2, 'Red Onions 100kg', 'red-onions-100kg', 'KFL-VG-003',
 'Cured red onions with a long shelf life.',
 'Field-cured for seven days before bagging, which cuts storage spoilage considerably. Netted 100kg sacks.',
 'Sokoto State', '100kg sack', 92000.00, 86000.00, 5, NULL, 1, 85, 0, 1),

(2, 'Garden Egg (Basket)', 'garden-egg-basket', 'KFL-VG-004',
 'Fresh garden egg, sorted by size.',
 'Harvested and delivered on a 24-hour cycle. Sorted so a basket is not half small fruit.',
 'Delta State', 'basket', 26000.00, 23500.00, 5, NULL, 1, 55, 0, 1),

(2, 'Fresh Carrots 20kg', 'fresh-carrots-20kg', 'KFL-VG-005',
 'Firm graded carrots for kitchens and juice bars.',
 'Size-graded and brushed clean, packed without washing to protect the skin and extend shelf life.',
 'Plateau State', '20kg bag', 24000.00, 21800.00, 5, NULL, 1, 90, 0, 1),

-- Tubers -------------------------------------------------------------------
(3, 'Puna Yam Tubers (Lot of 10)', 'puna-yam-tubers-lot-10', 'KFL-TB-001',
 'Large Puna yams, hand-selected and counted.',
 'Hand-selected for size and skin condition, counted into lots of ten and strapped for transit.',
 'Benue State', 'lot of 10', 38000.00, 34500.00, 5, NULL, 1, 120, 1, 1),

(3, 'Plantain (Bunch)', 'plantain-bunch', 'KFL-TB-002',
 'Mature green plantain by the bunch.',
 'Cut green so it travels well and ripens at your end rather than ours. Sold by the full bunch.',
 'Delta State', 'bunch', 9500.00, 8400.00, 10, NULL, 1, 160, 0, 1),

(3, 'Sweet Potato 50kg', 'sweet-potato-50kg', 'KFL-TB-003',
 'Orange-flesh sweet potato, size graded.',
 'Graded for even size so it cooks uniformly. Packed in breathable sacks.',
 'Kwara State', '50kg bag', 34000.00, 31000.00, 5, NULL, 1, 95, 0, 1),

(3, 'Irish Potato 50kg', 'irish-potato-50kg', 'KFL-TB-004',
 'Grade A potatoes for chipping and catering.',
 'Size-graded for chip yield, brushed clean and packed unwashed to protect the skin.',
 'Plateau State', '50kg bag', 56000.00, 51500.00, 5, NULL, 1, 110, 0, 1),

-- Oils ---------------------------------------------------------------------
(4, 'Red Palm Oil 25L', 'red-palm-oil-25l', 'KFL-OL-001',
 'Unrefined Delta palm oil in sealed 25 litre kegs.',
 'Pressed locally in Delta and Edo, filtered once and sealed at source. No dilution and no added colour. Kegs are tamper-banded.',
 'Delta State', '25L keg', 84000.00, 78000.00, 4, 79500.00, 1, 230, 1, 1),

(4, 'Vegetable Oil 25L', 'vegetable-oil-25l', 'KFL-OL-002',
 'Refined, bleached and deodorised vegetable oil.',
 'Standard RBD vegetable oil for kitchens that need a clean, odourless frying medium at volume.',
 'Lagos', '25L keg', 74000.00, 69000.00, 4, NULL, 1, 250, 0, 1),

(4, 'Groundnut Oil 25L', 'groundnut-oil-25l', 'KFL-OL-003',
 'Cold-pressed groundnut oil for commercial frying.',
 'High smoke point with a neutral finish, and it holds up through repeated fry cycles. Food-grade jerricans.',
 'Kano State', '25L keg', 96000.00, 90000.00, 4, NULL, 1, 170, 0, 1),

(4, 'Margarine 5kg', 'margarine-5kg', 'KFL-OL-004',
 'Bakery-grade margarine in catering tubs.',
 'Creams well for bakery work and holds shape at ambient temperature. Sold in 5kg tubs.',
 'Lagos', '5kg tub', 15500.00, 14200.00, 10, NULL, 1, 180, 0, 1),

-- Proteins -----------------------------------------------------------------
(5, 'Frozen Whole Chicken 10kg', 'frozen-whole-chicken-10kg', 'KFL-PR-001',
 'Whole broilers, IQF, held at -18C end to end.',
 'Individually quick frozen and held at -18C from the processor through our cold rooms to your door. Cartons are temperature-logged in transit.',
 'Ogun State', '10kg carton', 44000.00, 40500.00, 5, 42000.00, 1, 260, 1, 1),

(5, 'Frozen Turkey Wings 10kg', 'frozen-turkey-wings-10kg', 'KFL-PR-002',
 'Portioned turkey wings for grills and buka kitchens.',
 'Cut and portioned before freezing, so there is no thaw-and-refreeze cycle on your side.',
 'Imported', '10kg carton', 69000.00, 64000.00, 5, NULL, 1, 150, 0, 1),

(5, 'Fresh Goat Meat 20kg', 'fresh-goat-meat-20kg', 'KFL-PR-003',
 'Abattoir-fresh goat, cut to your specification.',
 'Slaughtered to order at a certified abattoir and cut to your spec. Delivered chilled within six hours.',
 'Delta State', '20kg lot', 168000.00, 158000.00, 2, NULL, 1, 40, 0, 1),

(5, 'Fresh Beef 20kg', 'fresh-beef-20kg', 'KFL-PR-004',
 'Chilled beef, boneless or bone-in to order.',
 'Cut to your specification on the morning of delivery and moved chilled, never frozen and thawed.',
 'Delta State', '20kg lot', 182000.00, 172000.00, 2, NULL, 1, 45, 0, 1),

(5, 'Fresh Cat Fish 10kg', 'fresh-cat-fish-10kg', 'KFL-PR-005',
 'Live-harvested cat fish, cleaned on request.',
 'Harvested from Delta ponds and delivered the same day. We can clean and portion it before dispatch if you ask.',
 'Delta State', '10kg lot', 42000.00, 38500.00, 5, NULL, 1, 75, 1, 1),

(5, 'Dried Crayfish 5kg', 'dried-crayfish-5kg', 'KFL-PR-006',
 'Ground or whole dried crayfish, well dried.',
 'Sun-dried properly so it does not go soft in storage. Available whole or ground to your preference.',
 'Rivers State', '5kg bag', 58000.00, 53500.00, 4, NULL, 1, 88, 0, 1),

(5, 'Stock Fish (Panla) 5kg', 'stock-fish-panla-5kg', 'KFL-PR-007',
 'Imported stock fish heads and cuts.',
 'Air-dried imported stock fish. Sold by weight in 5kg cartons, sorted into heads or body cuts.',
 'Imported', '5kg carton', 96000.00, 90000.00, 3, NULL, 1, 60, 0, 1),

(5, 'Eggs (Crate of 30)', 'eggs-crate-30', 'KFL-PR-008',
 'Farm-fresh eggs, candled and crate packed.',
 'Collected daily from farms around Asaba and candled before crating, so cracks do not reach you.',
 'Delta State', 'crate of 30', 5800.00, 5200.00, 20, NULL, 1, 500, 0, 1),

-- Spices & Seasonings ------------------------------------------------------
(6, 'Iodised Table Salt 50kg', 'iodised-table-salt-50kg', 'KFL-SP-001',
 'Free-flowing iodised salt in bulk sacks.',
 'Anti-caking treated so it stays free-flowing right through the rainy season.',
 'Lagos', '50kg bag', 19500.00, 17800.00, 5, NULL, 1, 300, 0, 1),

(6, 'Seasoning Cubes (Carton)', 'seasoning-cubes-carton', 'KFL-SP-002',
 'Bouillon cubes by the catering carton.',
 'Standard 4g cubes, packed for kitchens that go through a carton a week rather than a sachet.',
 'Nigeria', 'carton', 24500.00, 22500.00, 10, NULL, 1, 340, 0, 1),

(6, 'Curry Powder 1kg', 'curry-powder-1kg', 'KFL-SP-003',
 'Blended curry powder in sealed catering packs.',
 'Consistent blend batch to batch, so your stew tastes the same in March as it did in January.',
 'Nigeria', '1kg pack', 4200.00, 3700.00, 20, NULL, 1, 400, 0, 1),

(6, 'Dried Thyme 1kg', 'dried-thyme-1kg', 'KFL-SP-004',
 'Dried thyme leaf, screened for stalk.',
 'Screened to remove stalk so you are paying for leaf, not wood.',
 'Imported', '1kg pack', 6500.00, 5800.00, 20, NULL, 1, 320, 0, 1),

(6, 'Suya Spice 2kg', 'suya-spice-2kg', 'KFL-SP-005',
 'Yaji suya blend, ground fresh.',
 'Groundnut, ginger and pepper blend ground to order in small batches so it reaches you aromatic.',
 'Kano State', '2kg pack', 9800.00, 8900.00, 10, NULL, 1, 210, 0, 1),

(6, 'Tomato Paste 70g (Carton of 100)', 'tomato-paste-70g-carton', 'KFL-SP-006',
 'Triple-concentrated tomato paste sachets.',
 'Triple concentrate at 28 to 30 percent brix. Cartons of one hundred sachets with twelve months shelf life from dispatch.',
 'Imported', 'carton of 100', 35500.00, 32500.00, 5, 33500.00, 1, 380, 1, 1),

-- Frozen Foods -------------------------------------------------------------
(7, 'Frozen Croaker Fish 20kg', 'frozen-croaker-fish-20kg', 'KFL-FZ-001',
 'Size-graded croaker in 20kg master cartons.',
 'Graded at 300 to 500g per piece, glazed and block frozen. Delivered on refrigerated vehicles only.',
 'Imported', '20kg carton', 132000.00, 124000.00, 3, NULL, 1, 110, 0, 1),

(7, 'Frozen Titus Fish 20kg', 'frozen-titus-fish-20kg', 'KFL-FZ-002',
 'Mackerel in master cartons, cold chain intact.',
 'Blast frozen at source and never allowed above temperature. The workhorse fish for buka kitchens.',
 'Imported', '20kg carton', 118000.00, 111000.00, 3, NULL, 1, 125, 1, 1),

(7, 'Frozen Mixed Vegetables 10kg', 'frozen-mixed-vegetables-10kg', 'KFL-FZ-003',
 'IQF mixed vegetables for fried rice service.',
 'Individually quick frozen so it pours rather than arriving as one block. Peas, carrot and sweetcorn.',
 'Imported', '10kg carton', 32000.00, 29000.00, 5, NULL, 1, 140, 0, 1),

-- Household Essentials -----------------------------------------------------
(8, 'Bottled Water 75cl (Pack of 12)', 'bottled-water-75cl-pack', 'KFL-HH-001',
 'NAFDAC-registered table water by the pack or pallet.',
 'Shrink-wrapped packs of twelve. Pallet quantities dispatch same day within Asaba.',
 'Delta State', 'pack of 12', 2600.00, 2200.00, 50, NULL, 1, 1800, 0, 1),

(8, 'Detergent Powder 10kg', 'detergent-powder-10kg', 'KFL-HH-002',
 'Bulk laundry detergent for hotels and schools.',
 'High-foam powder supplied in 10kg sacks, which works out far cheaper than buying sachets.',
 'Nigeria', '10kg sack', 16500.00, 14800.00, 10, NULL, 1, 220, 0, 1),

(8, 'Toilet Roll (Bale of 24)', 'toilet-roll-bale-24', 'KFL-HH-003',
 'Two-ply tissue in catering bales.',
 'Two-ply, 24 rolls to a bale. Standard supply for hotels, offices and event centres.',
 'Nigeria', 'bale of 24', 8900.00, 8100.00, 10, NULL, 1, 400, 0, 1),

(8, 'Bar Soap (Carton)', 'bar-soap-carton', 'KFL-HH-004',
 'Laundry bar soap by the carton.',
 'Long bars that do not crumble, packed by the carton for institutional laundries.',
 'Nigeria', 'carton', 21000.00, 19200.00, 10, NULL, 1, 260, 0, 1);

INSERT INTO settings (setting_key, setting_value) VALUES
('site_name',         'KACHI Foodstuff Supplies & Logistics'),
('contact_email',     'orders@kachifoodandlogistics.com'),
('contact_phone',     '0906 088 4920'),
('contact_phone_alt', '0806 142 8556'),
('whatsapp',          '2349060884920'),
('address',           'Odakpo Close, Doctor Street, off Specialist Hospital, Asaba'),
('service_areas',     'Asaba, Warri, Sapele, Ughelli, Abraka, Agbor, Oghara, Effurun'),
('delivery_fee',      '3500'),
('free_delivery_from','150000'),
('bank_name',         'Zenith Bank'),
('bank_account_name', 'Kachi Foodstuff Supplies and Logistics Ltd'),
('bank_account_no',   '1012345678'),
('cac_number',        'RC: 9651491'),
('opening_hours',     'Mon - Sat, 7:30am - 7:00pm | Sun, 10:00am - 4:00pm');

-- Catalogue photography bundled with the code (see assets/img/photos).
-- Kept in step with database/migrations/20260805_add_catalogue_photography.sql
-- so a fresh install and an upgraded one end up identical.
UPDATE products SET image = 'img/photos/prod-rice-white.jpg'       WHERE slug = 'local-rice-50kg';
UPDATE products SET image = 'img/photos/prod-rice-parboiled.jpg'   WHERE slug = 'foreign-parboiled-rice-50kg';
UPDATE products SET image = 'img/photos/prod-rice-parboiled-2.jpg' WHERE slug = 'parboiled-rice-25kg';
UPDATE products SET image = 'img/photos/prod-beans-brown.jpg'      WHERE slug = 'brown-honey-beans-50kg';
UPDATE products SET image = 'img/photos/prod-beans-white.jpg'      WHERE slug = 'white-beans-50kg';
UPDATE products SET image = 'img/photos/prod-oil-kegs.jpg'         WHERE slug = 'vegetable-oil-25l';
