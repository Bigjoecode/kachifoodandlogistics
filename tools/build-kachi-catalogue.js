/**
 * Turn the additional food photography in kachi-images/ into lightweight,
 * consistently cropped catalogue assets.
 *
 * Run with: npm run kachi-images
 */
const sharp = require('sharp');
const fs = require('fs');
const path = require('path');

const ROOT = path.join(__dirname, '..');
const SOURCE = path.join(ROOT, 'kachi-images');
const OUTPUT = path.join(ROOT, 'assets', 'img', 'photos');

// Explicit filenames keep the mapping stable and make every product/photo
// association reviewable. Photos not listed here remain unused intentionally.
const PLAN = [
  ['WhatsApp Image 2026-08-14 at 8.07.37 AM.jpeg',     'prod-poundo-yam-flour', 'Poundo yam flour'],
  ['WhatsApp Image 2026-08-14 at 8.07.57 AM.jpeg',     'prod-semovita',         'Semovita'],
  ['WhatsApp Image 2026-08-14 at 8.08.05 AM (2).jpeg', 'prod-spaghetti',        'Packaged spaghetti'],
  ['Tomatoes.jpeg',                                    'prod-tomatoes',         'Fresh tomatoes'],
  ['WhatsApp Image 2026-08-14 at 8.07.58 AM (2).jpeg', 'prod-pepper-rodo',      'Fresh red pepper'],
  ['WhatsApp Image 2026-08-14 at 8.08.10 AM (1).jpeg', 'prod-carrots',          'Fresh carrots'],
  ['WhatsApp Image 2026-08-14 at 8.07.59 AM (1).jpeg', 'prod-puna-yam',         'Puna yam tubers'],
  ['WhatsApp Image 2026-08-14 at 8.07.47 AM (1).jpeg', 'prod-plantain',         'Green plantain'],
  ['potatoes.jpeg',                                    'prod-sweet-potato',     'Sweet potatoes'],
  ['palm oil.jpeg',                                    'prod-red-palm-oil',     'Red palm oil in jerrycans'],
  ['full chicken.jpeg',                                'prod-whole-chicken',    'Whole chicken'],
  ['WhatsApp Image 2026-08-14 at 8.08.07 AM.jpeg',     'prod-turkey-wings',     'Turkey wings'],
  ['WhatsApp Image 2026-08-14 at 8.07.59 AM.jpeg',     'prod-goat-meat',        'Fresh goat meat'],
  ['WhatsApp Image 2026-08-14 at 8.07.35 AM.jpeg',     'prod-catfish',          'Fresh catfish'],
  ['WhatsApp Image 2026-08-14 at 8.08.09 AM.jpeg',     'prod-crayfish',         'Dried crayfish'],
  ['stock fish.jpeg',                                  'prod-stockfish',        'Stock fish'],
  ['Egg Crates.jpeg',                                  'prod-eggs',             'Eggs in crates'],
  ['WhatsApp Image 2026-08-14 at 8.07.57 AM (1).jpeg', 'prod-seasoning-cubes',  'Seasoning cubes'],
  ['WhatsApp Image 2026-08-14 at 8.07.37 AM (1).jpeg', 'prod-tomato-paste',     'Tomato paste'],
  ['WhatsApp Image 2026-08-14 at 8.08.08 AM.jpeg',     'prod-yellow-garri',     'Yellow garri'],
  ['Blended Pepper.jpeg',                              'prod-blended-pepper',   'Blended pepper'],
  ['coconut.jpeg',                                     'prod-coconut',          'Fresh coconuts'],
  ['Curry Leaf.jpeg',                                  'prod-curry-leaf',       'Fresh curry leaves'],
  ['custard.jpeg',                                     'prod-custard',          'Custard powder'],
  ['fresh ginger.jpeg',                                'prod-fresh-ginger',     'Fresh ginger'],
  ['fresh vegetable.jpeg',                             'prod-fresh-vegetables', 'Fresh leafy vegetables'],
  ['Fresh Garlic.jpeg',                                'prod-garlic',           'Fresh garlic'],
  ['ogbono.jpeg',                                      'prod-ogbono',           'Ogbono'],
  ['oha leaf.jpeg',                                    'prod-oha-leaf',         'Fresh oha leaves'],
  ['Pomo.jpeg',                                        'prod-pomo',             'Pomo'],
  ['scent leaf.jpeg',                                  'prod-scent-leaf',       'Fresh scent leaves'],
  ['Snails.jpeg',                                      'prod-snails',           'Snails'],
  ['titus.jpeg',                                       'prod-titus-tin',        'Titus fish tin'],
  ['Basmati Rice 5kg.jfif',                            'prod-basmati-rice',     'Basmati rice'],
  ['Curry Powder 1kg.jfif',                            'prod-curry-powder',     'Curry powder'],
  ['Dried Thyme 1kg.jfif',                             'prod-dried-thyme',      'Dried thyme'],
  ['Fresh Beef 20kg.jfif',                             'prod-fresh-beef',       'Fresh beef'],
  ['Frozen Croaker Fish 20kg.jfif',                    'prod-frozen-croaker',   'Frozen croaker fish'],
  ['Frozen Mixed Vegetables 10kg.jfif',                'prod-frozen-mixed-veg', 'Frozen mixed vegetables'],
  ['frozen titus fish.jfif',                           'prod-frozen-titus',     'Frozen Titus fish'],
  ['Garden Egg (Basket).jfif',                         'prod-garden-egg',       'Fresh garden eggs'],
  ['Groundnut Oil 25L.jfif',                           'prod-groundnut-oil',    'Groundnut oil'],
  ['Ijebu Garri 50kg.jfif',                            'prod-ijebu-garri',      'Ijebu garri'],
  ['Indomie Noodles (Carton of 40).jfif',              'prod-indomie',          'Indomie noodles'],
  ['Iodised Table Salt 50kg.jfif',                     'prod-table-salt',       'Iodised table salt'],
  ['Irish Potato 50kg.jfif',                           'prod-irish-potato',     'Irish potatoes'],
  ['Margarine 5kg.jfif',                               'prod-margarine',        'Margarine'],
  ['Red Onions 100kg.jfif',                            'prod-red-onions',       'Red onions'],
  ['Suya Spice 2kg.jfif',                              'prod-suya-spice',       'Suya spice'],
  ['White Garri 50kg.jfif',                            'prod-white-garri',      'White garri'],
  ['assorted meat.jpeg',                               'prod-assorted-meat',    'Assorted meat'],
  ['Bell Pepper.jpeg',                                 'prod-bell-peppers',     'Bell peppers'],
  ['cocumber.jpeg',                                    'prod-cucumber',         'Fresh cucumbers'],
  ['garbage.jpeg',                                     'prod-cabbage',          'Fresh cabbage'],
  ['okro.jpeg',                                        'prod-okra',             'Fresh okra'],
  ['lettuce.jpeg',                                     'prod-spring-onions',    'Fresh spring onions'],
  ['perewinkle.jpeg',                                  'prod-periwinkle',       'Periwinkle'],
];

(async () => {
  fs.mkdirSync(OUTPUT, { recursive: true });

  for (const [filename, name, description] of PLAN) {
    const source = path.join(SOURCE, filename);
    if (!fs.existsSync(source)) {
      throw new Error(`Missing source image: ${filename}`);
    }

    const pipeline = () => sharp(source)
      .rotate()
      .resize(900, 900, { fit: 'cover', position: 'attention' });

    await pipeline().jpeg({ quality: 80, mozjpeg: true }).toFile(path.join(OUTPUT, `${name}.jpg`));
    await pipeline().webp({ quality: 74 }).toFile(path.join(OUTPUT, `${name}.webp`));
    console.log(`wrote ${name}.jpg/webp — ${description}`);
  }
})();
