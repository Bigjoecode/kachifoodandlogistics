-- Real photography for the catalogue, shot at the Asaba market stall.
--
-- Paths beginning "img/" are bundled with the code and deployed with it;
-- bare filenames are back-office uploads living in assets/uploads. Only
-- products whose contents are unmistakable in the photograph are assigned
-- one, so nobody is shown a picture of something they are not buying. The
-- ambiguous grain shots are used as page imagery instead.
--
-- Every statement skips products whose photo has already been set from the
-- back office, and re-running changes nothing.

UPDATE products SET image = 'img/photos/prod-rice-white.jpg'
    WHERE slug = 'local-rice-50kg' AND (image IS NULL OR image = '');

UPDATE products SET image = 'img/photos/prod-rice-parboiled.jpg'
    WHERE slug = 'foreign-parboiled-rice-50kg' AND (image IS NULL OR image = '');

UPDATE products SET image = 'img/photos/prod-rice-parboiled-2.jpg'
    WHERE slug = 'parboiled-rice-25kg' AND (image IS NULL OR image = '');

UPDATE products SET image = 'img/photos/prod-beans-brown.jpg'
    WHERE slug = 'brown-honey-beans-50kg' AND (image IS NULL OR image = '');

UPDATE products SET image = 'img/photos/prod-beans-white.jpg'
    WHERE slug = 'white-beans-50kg' AND (image IS NULL OR image = '');

UPDATE products SET image = 'img/photos/prod-oil-kegs.jpg'
    WHERE slug = 'vegetable-oil-25l' AND (image IS NULL OR image = '');
