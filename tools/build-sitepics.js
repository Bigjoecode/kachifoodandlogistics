/**
 * Turns the client's photography in sitepics/ into web assets.
 *
 * The source files are phone photos with meaningless names and several near
 * duplicates. This maps the useful ones to purposeful names, crops them to the
 * aspect ratios the templates actually use, and writes JPEG + WebP.
 *
 * Product photos are only assigned where the contents are unambiguous. Two of
 * the grain shots could be millet, sorghum or paddy rice; those are used as
 * general catalogue imagery rather than being labelled as a specific product,
 * because a wrong photo on a product page is worse than no photo.
 *
 * Run with:  npm run sitepics
 */
const sharp = require('sharp');
const fs = require('fs');
const path = require('path');

const ROOT = path.join(__dirname, '..');
const SRC = path.join(ROOT, 'sitepics');
const OUT = path.join(ROOT, 'assets', 'img', 'photos');
fs.mkdirSync(OUT, { recursive: true });

/** Source files, sorted, so the 1-based numbers below are stable. */
const files = fs.readdirSync(SRC).filter((f) => /\.(jpe?g|png)$/i.test(f)).sort();

/** [source number, output name, width, height, what it shows] */
const PLAN = [
  // --- Fleet and operations, used across the site -----------------------
  [1,  'fleet-truck',    1600, 900, 'KACHI branded delivery truck, "Now in Asaba"'],
  [1,  'fleet-truck-sq',  900, 900, 'KACHI branded delivery truck'],
  [2,  'fleet-car',      1200, 800, 'KACHI branded company car listing its services'],
  [16, 'haulage',        1600, 900, 'Sacks being loaded onto a haulage truck'],

  // --- Market and storefront -------------------------------------------
  [12, 'market',         1600, 900, 'The Asaba market district we buy and deliver in'],
  [19, 'stall-team',     1200, 800, 'KACHI staff at the market stall'],
  [17, 'stall',          1200, 800, 'Stall stocked with sacks, tins and dry goods'],
  [18, 'stock-basins',   1200, 800, 'Basins of assorted grains, beans and dry goods'],
  [23, 'stock-sacks',    1600, 900, 'Open sacks of grains and beans on the stall'],
  [32, 'stall-serving',  1200, 800, 'Serving a customer at the stall'],
  [20, 'shelf-packaged', 1200, 800, 'Shelf of packaged goods, oils and tomato paste'],
  [14, 'oil-cartons',    1200, 800, 'Cooking oil kegs stacked beside carton goods'],

  // --- Category headers --------------------------------------------------
  [23, 'cat-grains',      900, 600, 'Grains and dry foods'],
  [31, 'cat-oils',        900, 600, 'Cooking oil in bulk kegs'],
  [20, 'cat-household',   900, 600, 'Packaged household goods'],
  [6,  'grains-generic',  900, 600, 'Loose grain in an open sack'],

  // --- Product photography (only where the contents are unmistakable) ---
  [5,  'prod-rice-white',      900, 900, 'White rice in an open 50kg sack'],
  [24, 'prod-rice-parboiled',  900, 900, 'Parboiled rice in an open sack'],
  [26, 'prod-rice-parboiled-2',900, 900, 'Parboiled rice, close crop'],
  [4,  'prod-beans-brown',     900, 900, 'Brown honey beans in an open sack'],
  [10, 'prod-beans-white',     900, 900, 'White beans in an open sack'],
  [31, 'prod-oil-kegs',        900, 900, 'Cooking oil in 25L kegs'],
  [29, 'prod-groundnuts',      900, 900, 'Raw groundnuts in an open sack'],
];

(async () => {
  console.log(`${files.length} source photos\n`);
  const written = [];

  for (const [num, name, w, h, alt] of PLAN) {
    const source = files[num - 1];
    if (!source) {
      console.warn(`  !! source #${num} missing, skipping ${name}`);
      continue;
    }

    const base = path.join(OUT, name);
    const pipeline = () => sharp(path.join(SRC, source)).resize(w, h, { fit: 'cover', position: 'attention' });

    await pipeline().jpeg({ quality: 80, mozjpeg: true }).toFile(`${base}.jpg`);
    await pipeline().webp({ quality: 74 }).toFile(`${base}.webp`);

    const kb = (fs.statSync(`${base}.jpg`).size / 1024).toFixed(0);
    const kbw = (fs.statSync(`${base}.webp`).size / 1024).toFixed(0);
    written.push(name);
    console.log(`  ${name.padEnd(24)} ${String(w).padStart(4)}x${h}  ${kb}kb jpg / ${kbw}kb webp   ${alt}`);
  }

  // A manifest keeps alt text with the image instead of scattered in templates.
  const manifest = {};
  for (const [num, name, w, h, alt] of PLAN) {
    manifest[name] = { alt, width: w, height: h };
  }
  fs.writeFileSync(path.join(OUT, 'manifest.json'), JSON.stringify(manifest, null, 2));

  const total = fs.readdirSync(OUT)
    .filter((f) => /\.(jpg|webp)$/.test(f))
    .reduce((sum, f) => sum + fs.statSync(path.join(OUT, f)).size, 0);
  console.log(`\n${written.length} images, ${(total / 1024 / 1024).toFixed(1)}MB total`);
})().catch((e) => { console.error(e); process.exit(1); });
