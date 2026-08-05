/**
 * Behavioural test for the back-office navigation drawer.
 * Verifies it opens, closes by every route a user might take, traps focus,
 * locks page scroll, and stays out of the way on desktop.
 */
const puppeteer = require('puppeteer-core');

const BASE = process.env.BASE || 'http://localhost/kachifoodandlogistics';
const EDGE = 'C:\\Program Files (x86)\\Microsoft\\Edge\\Application\\msedge.exe';

const results = [];
const check = (name, pass, detail) => {
  results.push({ name, pass, detail: detail || '' });
  console.log(`  ${pass ? 'PASS' : 'FAIL'}  ${name}${detail ? '  (' + detail + ')' : ''}`);
};

const state = (page) => page.evaluate(() => {
  const drawer = document.querySelector('[data-drawer]');
  const scrim = document.querySelector('[data-drawer-scrim]');
  const toggle = document.querySelector('[data-drawer-toggle]');
  return {
    open: drawer.classList.contains('is-open'),
    drawerX: Math.round(drawer.getBoundingClientRect().left),
    drawerVisible: getComputedStyle(drawer).transform,
    scrimHidden: scrim.hidden,
    expanded: toggle.getAttribute('aria-expanded'),
    bodyLocked: document.body.classList.contains('drawer-open'),
    burgerVisible: toggle.offsetParent !== null && toggle.getBoundingClientRect().height > 0,
    focusInDrawer: drawer.contains(document.activeElement),
  };
});

(async () => {
  const browser = await puppeteer.launch({
    executablePath: EDGE, headless: 'new',
    args: ['--no-sandbox', '--disable-dev-shm-usage', '--hide-scrollbars'],
  });
  const page = await browser.newPage();
  await page.setCacheEnabled(false);
  page.setDefaultNavigationTimeout(60000);

  // Sign in.
  await page.setViewport({ width: 1280, height: 900 });
  await page.goto(`${BASE}/admin/login`, { waitUntil: 'domcontentloaded' });
  await page.type('#email', 'joseph@kachifoodandlogistics.com');
  await page.type('#password', 'CorrectHorse99');
  await Promise.all([
    page.waitForNavigation({ waitUntil: 'domcontentloaded' }),
    page.click('button[type="submit"]'),
  ]);

  console.log('\n--- mobile (390px) ---');
  await page.setViewport({ width: 390, height: 844, isMobile: true, hasTouch: true });
  await page.goto(`${BASE}/admin`, { waitUntil: 'networkidle2' });

  let s = await state(page);
  check('burger is visible', s.burgerVisible);
  check('drawer starts closed', !s.open && s.drawerX < 0, `left=${s.drawerX}px`);
  check('scrim starts hidden', s.scrimHidden);
  check('aria-expanded is false', s.expanded === 'false');

  await page.click('[data-drawer-toggle]');
  await new Promise((r) => setTimeout(r, 450));
  s = await state(page);
  check('opens on tap', s.open && s.drawerX === 0, `left=${s.drawerX}px`);
  check('scrim shown when open', !s.scrimHidden);
  check('aria-expanded becomes true', s.expanded === 'true');
  check('page scroll locked', s.bodyLocked);
  check('focus moves into the drawer', s.focusInDrawer);

  await page.keyboard.press('Escape');
  await new Promise((r) => setTimeout(r, 450));
  s = await state(page);
  check('Escape closes it', !s.open, `left=${s.drawerX}px`);
  check('scroll lock released', !s.bodyLocked);

  await page.click('[data-drawer-toggle]');
  await new Promise((r) => setTimeout(r, 400));
  await page.click('[data-drawer-scrim]');
  await new Promise((r) => setTimeout(r, 450));
  s = await state(page);
  check('tapping the scrim closes it', !s.open);

  await page.click('[data-drawer-toggle]');
  await new Promise((r) => setTimeout(r, 400));
  await page.click('[data-drawer-close]');
  await new Promise((r) => setTimeout(r, 450));
  s = await state(page);
  check('close button works', !s.open);

  // Following a nav link should land on the new page with the drawer shut.
  await page.click('[data-drawer-toggle]');
  await new Promise((r) => setTimeout(r, 400));
  await Promise.all([
    page.waitForNavigation({ waitUntil: 'networkidle2' }),
    page.click('.admin-nav a[href*="/admin/orders"]'),
  ]);
  s = await state(page);
  check('navigating leaves the drawer closed', !s.open && !s.bodyLocked,
        page.url().includes('/admin/orders') ? 'landed on orders' : 'wrong page');

  // Tables must stack rather than scroll sideways.
  const table = await page.evaluate(() => {
    const t = document.querySelector('.admin-content .table');
    if (!t) return null;
    const row = t.querySelector('tbody tr');
    const cell = row && row.cells[0];
    return {
      stacked: t.classList.contains('table-stacked'),
      rowIsBlock: row ? getComputedStyle(row).display === 'block' : false,
      label: cell ? cell.getAttribute('data-label') : null,
      overflow: Math.round(document.documentElement.scrollWidth - window.innerWidth),
    };
  });
  if (table) {
    check('table rows stack into cards', table.stacked && table.rowIsBlock);
    check('cells are labelled from the headers', !!table.label, `first label: "${table.label}"`);
    check('no sideways page scroll', table.overflow <= 1, `${table.overflow}px overflow`);
  }

  console.log('\n--- desktop (1280px) ---');
  await page.setViewport({ width: 1280, height: 900 });
  await page.goto(`${BASE}/admin`, { waitUntil: 'networkidle2' });
  s = await state(page);
  check('burger hidden on desktop', !s.burgerVisible);
  check('sidebar permanently visible', s.drawerX === 0, `left=${s.drawerX}px`);

  await browser.close();

  const failed = results.filter((r) => !r.pass);
  console.log(`\n${results.length - failed.length}/${results.length} checks passed`);
  if (failed.length) process.exit(1);
})().catch((e) => { console.error(e); process.exit(1); });
