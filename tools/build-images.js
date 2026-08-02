/**
 * Prepares brand imagery from the two source files the client supplied.
 *
 *   kachilogo.jpeg  ->  transparent PNG logo (+ white knockout for navy surfaces,
 *                       + the emblem on its own for the favicon and compact marks)
 *   k.jpeg          ->  cropped photography panels (truck, signage, merchandise)
 *
 * Run with:  node tools/build-images.js
 */
const sharp = require('sharp');
const fs = require('fs');
const path = require('path');

const ROOT = path.join(__dirname, '..');
const OUT = path.join(ROOT, 'assets', 'img');
fs.mkdirSync(OUT, { recursive: true });

const out = (name) => path.join(OUT, name);

/**
 * Knock the white studio background out of a flat-colour logo.
 * Colours are left untouched; only the white field is removed and the
 * anti-aliased edge is feathered, so the navy and orange stay exact.
 */
async function whiteToAlpha(src, dest, { knockout = false } = {}) {
  const { data, info } = await sharp(src)
    .ensureAlpha()
    .raw()
    .toBuffer({ resolveWithObject: true });

  const { width, height, channels } = info;
  const rgba = Buffer.alloc(width * height * 4);

  for (let i = 0, j = 0; i < data.length; i += channels, j += 4) {
    let r = data[i], g = data[i + 1], b = data[i + 2];
    const distanceFromWhite = 255 - Math.min(r, g, b);

    let a;
    if (distanceFromWhite <= 10) a = 0;                                   // background
    else if (distanceFromWhite >= 45) a = 255;                            // solid ink
    else a = Math.round(((distanceFromWhite - 10) / 35) * 255);           // feathered edge

    if (knockout && a > 0) {
      // On navy surfaces the navy ink disappears, so flip it to white and
      // keep the orange, which still reads well.
      const isOrange = r > 150 && g > 70 && b < 130 && r > b + 60;
      if (!isOrange) { r = 255; g = 255; b = 255; }
    }

    rgba[j] = r; rgba[j + 1] = g; rgba[j + 2] = b; rgba[j + 3] = a;
  }

  // Header/footer render it at ~44px tall, so 640px wide is plenty and keeps
  // the file small. palette:true quantises the flat brand colours hard.
  await sharp(rgba, { raw: { width, height, channels: 4 } })
    .trim()
    .resize({ width: 640, withoutEnlargement: true })
    .png({ compressionLevel: 9, palette: true, quality: 90 })
    .toFile(dest);
}

/** Crop one panel out of the mockup sheet, using fractions of its size. */
async function panel(src, dest, { left, top, width, height }, targetWidth) {
  const meta = await sharp(src).metadata();
  const region = {
    left: Math.round(meta.width * left),
    top: Math.round(meta.height * top),
    width: Math.round(meta.width * width),
    height: Math.round(meta.height * height),
  };

  await sharp(src).extract(region).resize({ width: targetWidth }).jpeg({ quality: 82, mozjpeg: true }).toFile(dest);
  await sharp(src).extract(region).resize({ width: targetWidth }).webp({ quality: 78 }).toFile(dest.replace(/\.jpg$/, '.webp'));
}

(async () => {
  const logoSrc = path.join(ROOT, 'kachilogo.jpeg');
  const mockSrc = path.join(ROOT, 'k.jpeg');

  const logoMeta = await sharp(logoSrc).metadata();
  const mockMeta = await sharp(mockSrc).metadata();
  console.log(`source logo   ${logoMeta.width}x${logoMeta.height}`);
  console.log(`source mockup ${mockMeta.width}x${mockMeta.height}`);

  // --- Logo variants -------------------------------------------------------
  await whiteToAlpha(logoSrc, out('logo.png'));
  await whiteToAlpha(logoSrc, out('logo-white.png'), { knockout: true });
  console.log('wrote logo.png, logo-white.png');

  // The emblem is the left ~34% of the lockup: wheat arc + truck.
  await sharp(out('logo.png'))
    .metadata()
    .then(({ width, height }) =>
      sharp(out('logo.png'))
        .extract({ left: 0, top: 0, width: Math.round(width * 0.34), height })
        .trim()
        .toFile(out('logo-mark.png'))
    );

  await sharp(out('logo-mark.png')).resize(512, 512, { fit: 'contain', background: { r: 0, g: 0, b: 0, alpha: 0 } })
    .png().toFile(out('icon-512.png'));
  await sharp(out('logo-mark.png')).resize(192, 192, { fit: 'contain', background: { r: 0, g: 0, b: 0, alpha: 0 } })
    .png().toFile(out('icon-192.png'));
  await sharp(out('logo-mark.png')).resize(180, 180, { fit: 'contain', background: { r: 8, g: 44, b: 92, alpha: 1 } })
    .png().toFile(out('apple-touch-icon.png'));
  await sharp(out('logo-mark.png')).resize(48, 48, { fit: 'contain', background: { r: 0, g: 0, b: 0, alpha: 0 } })
    .png().toFile(out('favicon.png'));
  console.log('wrote logo-mark.png + icon set');

  // Full-width logo for social sharing, on brand navy.
  await sharp(out('logo-white.png'))
    .resize(1000, null, { fit: 'inside' })
    .extend({ top: 180, bottom: 180, left: 100, right: 100, background: { r: 8, g: 44, b: 92, alpha: 1 } })
    .flatten({ background: { r: 8, g: 44, b: 92 } })
    .jpeg({ quality: 88 })
    .toFile(out('og-image.jpg'));
  console.log('wrote og-image.jpg');

  // --- Photography panels cropped from the brand mockup sheet --------------
  await panel(mockSrc, out('truck.jpg'),    { left: 0.534, top: 0.020, width: 0.452, height: 0.455 }, 1200);
  await panel(mockSrc, out('signage.jpg'),  { left: 0.008, top: 0.020, width: 0.518, height: 0.455 }, 1200);
  await panel(mockSrc, out('merch.jpg'),    { left: 0.008, top: 0.492, width: 0.978, height: 0.412 }, 1400);
  await panel(mockSrc, out('uniform.jpg'),  { left: 0.008, top: 0.492, width: 0.212, height: 0.412 }, 600);
  await panel(mockSrc, out('cards.jpg'),    { left: 0.224, top: 0.492, width: 0.226, height: 0.412 }, 600);
  await panel(mockSrc, out('banner.jpg'),   { left: 0.796, top: 0.492, width: 0.190, height: 0.412 }, 600);
  console.log('wrote truck / signage / merch / uniform / cards / banner panels');

  const written = fs.readdirSync(OUT).sort();
  console.log('\n' + written.map((f) => `  ${f}  ${(fs.statSync(out(f)).size / 1024).toFixed(0)}kb`).join('\n'));
})().catch((err) => {
  console.error(err);
  process.exit(1);
});
