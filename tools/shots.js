/**
 * Screenshot helper for design review.
 *   node tools/shots.js <slug>:<path> [...]   e.g. node tools/shots.js home:/ cart:/cart
 * Writes full-page desktop + mobile PNGs to tools/shots/.
 */
const puppeteer = require('puppeteer-core');
const fs = require('fs');
const path = require('path');

const BASE = process.env.BASE || 'http://localhost/kachifoodandlogistics';
const EDGE = 'C:\\Program Files (x86)\\Microsoft\\Edge\\Application\\msedge.exe';
const OUT = path.join(__dirname, 'shots');
fs.mkdirSync(OUT, { recursive: true });

const targets = process.argv.slice(2).map((arg) => {
  const i = arg.indexOf(':');
  return { name: arg.slice(0, i), url: arg.slice(i + 1) };
});

(async () => {
  const browser = await puppeteer.launch({
    executablePath: EDGE,
    headless: 'new',
    args: ['--no-sandbox', '--disable-dev-shm-usage', '--hide-scrollbars'],
  });

  const errors = [];

  for (const { name, url } of targets) {
    for (const [label, viewport] of [
      ['desktop', { width: 1440, height: 1000, deviceScaleFactor: 1 }],
      ['mobile', { width: 390, height: 844, deviceScaleFactor: 2, isMobile: true, hasTouch: true }],
    ]) {
      const page = await browser.newPage();
      await page.setViewport(viewport);
      page.on('pageerror', (e) => errors.push(`${name} ${label}: ${e.message}`));
      page.on('console', (m) => { if (m.type() === 'error') errors.push(`${name} ${label}: ${m.text()}`); });

      await page.goto(BASE + url, { waitUntil: 'networkidle2', timeout: 45000 });
      // Let reveal-on-scroll finish so nothing is captured mid-fade.
      await page.evaluate(() => window.scrollTo(0, document.body.scrollHeight));
      await new Promise((r) => setTimeout(r, 700));
      await page.evaluate(() => window.scrollTo(0, 0));
      await new Promise((r) => setTimeout(r, 300));

      const file = path.join(OUT, `${name}-${label}.png`);
      await page.screenshot({ path: file, fullPage: label === 'desktop' });
      console.log(`${file}`);
      await page.close();
    }
  }

  await browser.close();
  if (errors.length) {
    console.log('\nBROWSER ERRORS:');
    errors.forEach((e) => console.log('  ' + e));
  } else {
    console.log('\nno browser console errors');
  }
})().catch((e) => { console.error(e); process.exit(1); });
