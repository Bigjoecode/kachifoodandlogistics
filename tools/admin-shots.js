/** Screenshots of the back office, signed in, including the open drawer. */
const puppeteer = require('puppeteer-core');
const fs = require('fs');
const path = require('path');

const BASE = process.env.BASE || 'http://localhost/kachifoodandlogistics';
const EDGE = 'C:\\Program Files (x86)\\Microsoft\\Edge\\Application\\msedge.exe';
const OUT = path.join(__dirname, 'shots');
fs.mkdirSync(OUT, { recursive: true });

(async () => {
  const browser = await puppeteer.launch({
    executablePath: EDGE, headless: 'new',
    args: ['--no-sandbox', '--disable-dev-shm-usage', '--hide-scrollbars'],
  });
  const page = await browser.newPage();
  await page.setCacheEnabled(false);
  page.setDefaultNavigationTimeout(60000);

  await page.setViewport({ width: 1280, height: 900 });
  await page.goto(`${BASE}/admin/login`, { waitUntil: 'domcontentloaded' });
  await page.type('#email', 'joseph@kachifoodandlogistics.com');
  await page.type('#password', 'CorrectHorse99');
  await Promise.all([
    page.waitForNavigation({ waitUntil: 'domcontentloaded' }),
    page.click('button[type="submit"]'),
  ]);

  const shot = async (name, url, width, opts = {}) => {
    await page.setViewport({ width, height: 900, deviceScaleFactor: 2, isMobile: width < 1024, hasTouch: width < 1024 });
    await page.goto(BASE + url, { waitUntil: 'networkidle2' });
    if (opts.openDrawer) {
      await page.click('[data-drawer-toggle]');
      await new Promise((r) => setTimeout(r, 500));
    }
    const file = path.join(OUT, `${name}.png`);
    await page.screenshot({ path: file, fullPage: !!opts.full });
    console.log(file);
  };

  await shot('admin-m-dash', '/admin', 390);
  await shot('admin-m-drawer', '/admin', 390, { openDrawer: true });
  await shot('admin-m-orders', '/admin/orders', 390, { full: true });

  await browser.close();
})().catch((e) => { console.error(e); process.exit(1); });
