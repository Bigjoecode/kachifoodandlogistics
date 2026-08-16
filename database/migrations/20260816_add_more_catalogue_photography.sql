-- Additional product photography supplied in kachi-images/. Existing images
-- uploaded through the back office always win.

UPDATE products SET image = 'img/photos/prod-poundo-yam-flour.jpg' WHERE slug = 'poundo-yam-flour-10kg' AND (image IS NULL OR image = '');
UPDATE products SET image = 'img/photos/prod-semovita.jpg'         WHERE slug = 'semovita-10kg' AND (image IS NULL OR image = '');
UPDATE products SET image = 'img/photos/prod-spaghetti.jpg'        WHERE slug = 'spaghetti-500g-carton' AND (image IS NULL OR image = '');
UPDATE products SET image = 'img/photos/prod-tomatoes.jpg'         WHERE slug = 'fresh-tomatoes-basket' AND (image IS NULL OR image = '');
UPDATE products SET image = 'img/photos/prod-pepper-rodo.jpg'      WHERE slug = 'fresh-pepper-rodo-basket' AND (image IS NULL OR image = '');
UPDATE products SET image = 'img/photos/prod-carrots.jpg'          WHERE slug = 'fresh-carrots-20kg' AND (image IS NULL OR image = '');
UPDATE products SET image = 'img/photos/prod-puna-yam.jpg'         WHERE slug = 'puna-yam-tubers-lot-10' AND (image IS NULL OR image = '');
UPDATE products SET image = 'img/photos/prod-plantain.jpg'         WHERE slug = 'plantain-bunch' AND (image IS NULL OR image = '');
UPDATE products SET image = 'img/photos/prod-sweet-potato.jpg'     WHERE slug = 'sweet-potato-50kg' AND (image IS NULL OR image = '');
UPDATE products SET image = 'img/photos/prod-red-palm-oil.jpg'     WHERE slug = 'red-palm-oil-25l' AND (image IS NULL OR image = '');
UPDATE products SET image = 'img/photos/prod-whole-chicken.jpg'    WHERE slug = 'frozen-whole-chicken-10kg' AND (image IS NULL OR image = '');
UPDATE products SET image = 'img/photos/prod-turkey-wings.jpg'     WHERE slug = 'frozen-turkey-wings-10kg' AND (image IS NULL OR image = '');
UPDATE products SET image = 'img/photos/prod-goat-meat.jpg'        WHERE slug = 'fresh-goat-meat-20kg' AND (image IS NULL OR image = '');
UPDATE products SET image = 'img/photos/prod-catfish.jpg'          WHERE slug = 'fresh-cat-fish-10kg' AND (image IS NULL OR image = '');
UPDATE products SET image = 'img/photos/prod-crayfish.jpg'         WHERE slug = 'dried-crayfish-5kg' AND (image IS NULL OR image = '');
UPDATE products SET image = 'img/photos/prod-stockfish.jpg'        WHERE slug = 'stock-fish-panla-5kg' AND (image IS NULL OR image = '');
UPDATE products SET image = 'img/photos/prod-eggs.jpg'             WHERE slug = 'eggs-crate-30' AND (image IS NULL OR image = '');
UPDATE products SET image = 'img/photos/prod-seasoning-cubes.jpg'  WHERE slug = 'seasoning-cubes-carton' AND (image IS NULL OR image = '');
UPDATE products SET image = 'img/photos/prod-tomato-paste.jpg'     WHERE slug = 'tomato-paste-70g-carton' AND (image IS NULL OR image = '');
UPDATE products SET image = 'img/photos/prod-yellow-garri.jpg'     WHERE slug = 'yellow-garri-50kg' AND (image IS NULL OR image = '');
