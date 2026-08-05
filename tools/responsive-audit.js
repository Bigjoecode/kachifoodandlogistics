/**
 * Responsive audit. Loads every route at several widths and reports:
 *   - horizontal page overflow (the page itself scrolling sideways)
 *   - individual elements wider than the viewport
 *   - touch targets under 44px
 *   - body text under 12px
 *
 * Usage: node tools/responsive-audit.js            (public pages)
 *        node tools/responsive-audit.js --admin    (public + back office)
 */
const puppeteer = require('puppeteer-core');

const BASE = process.env.BASE || 'http://localhost/kachifoodandlogistics';
const EDGE = 'C:\\Program Files (x86)\\Microsoft\\Edge\\Application\\msedge.exe';

const WIDTHS = [320, 390, 768, 1024];

const PUBLIC = [
  '/', '/products', '/products/local-rice-50kg', '/category/frozen-foods', '/cart',
  '/logistics', '/services', '/about', '/faqs', '/contact', '/quote', '/track',
  '/login', '/register', '/nothing-here',
];

const ADMIN = [
  '/admin', '/admin/orders', '/admin/bookings', '/admin/products',
  '/admin/products/new', '/admin/categories', '/admin/customers',
  '/admin/messages', '/admin/settings',
];

/** Everything the audit measures, run inside the page. */
function measure() {
  const vw = window.innerWidth;
  const problems = [];

  const docWidth = Math.max(
    document.documentElement.scrollWidth,
    document.body ? document.body.scrollWidth : 0
  );
  if (docWidth > vw + 1) {
    problems.push({ type: 'page-overflow', detail: `document ${docWidth}px vs viewport ${vw}px` });
  }

  const describe = (el) => {
    const cls = (el.className && el.className.baseVal !== undefined ? el.className.baseVal : el.className) || '';
    const short = String(cls).trim().split(/\s+/).slice(0, 4).join('.');
    return el.tagName.toLowerCase() + (el.id ? '#' + el.id : '') + (short ? '.' + short : '');
  };

  document.querySelectorAll('body *').forEach((el) => {
    const style = getComputedStyle(el);
    if (style.display === 'none' || style.visibility === 'hidden' || style.position === 'fixed') return;

    const rect = el.getBoundingClientRect();
    if (rect.width === 0 && rect.height === 0) return;

    // An element sticking out past the right edge, unless it is inside a
    // deliberately scrollable container (tables, chart strips).
    if (rect.right > vw + 1 || rect.left < -1) {
      let scrollable = false;
      for (let p = el.parentElement; p; p = p.parentElement) {
        const ps = getComputedStyle(p);
        if (ps.overflowX === 'auto' || ps.overflowX === 'scroll' || ps.overflowX === 'hidden') { scrollable = true; break; }
      }
      if (!scrollable) {
        problems.push({ type: 'element-overflow', detail: `${describe(el)} right=${Math.round(rect.right)} vw=${vw}` });
      }
    }
  });

  // Touch targets, only meaningful on a touch viewport.
  //   Controls (buttons, inputs, selects) -> 44px, the Apple HIG bar.
  //   Standalone links               -> 24px, WCAG 2.2 AA (2.5.8).
  //   Links inside running prose are exempt: WCAG treats them as inline text.
  if (vw < 768) {
    document.querySelectorAll('a[href], button, input[type="submit"], select, summary').forEach((el) => {
      const style = getComputedStyle(el);
      if (style.display === 'none' || style.visibility === 'hidden') return;
      if (el.closest('p, address, .prose-body, .tick-list, dd, .hint, .error-text')) return;
      if (el.classList.contains('skip-link')) return;   // visible only on focus

      // WCAG 2.5.8 "Inline" exception: a link sitting inside a sentence of
      // other text is sized by the line-height around it, so it is exempt.
      const parent = el.parentElement;
      if (parent && el.tagName === 'A') {
        const parentText = parent.textContent.trim();
        const ownText = el.textContent.trim();
        if (parentText.length > ownText.length + 3) return;
      }

      const rect = el.getBoundingClientRect();
      if (rect.width === 0 || rect.height === 0) return;

      // .btn-link is a button element styled as an inline text link, so it is
      // held to the link threshold rather than the control one.
      const isControl = el.matches('button, input, select, summary, .btn') && !el.matches('.btn-link');
      const min = isControl ? 44 : 24;

      if (rect.height < min - 0.5) {
        problems.push({
          type: 'touch-target',
          detail: `${describe(el)} ${Math.round(rect.width)}x${Math.round(rect.height)} (min ${min})`,
        });
      }
    });
  }

  // Text too small to read comfortably on a phone.
  document.querySelectorAll('p, li, td, dd, dt, label, span').forEach((el) => {
    if (!el.textContent.trim()) return;
    const size = parseFloat(getComputedStyle(el).fontSize);
    if (size && size < 12) {
      problems.push({ type: 'tiny-text', detail: `${describe(el)} ${size}px` });
    }
  });

  return problems;
}

(async () => {
  const withAdmin = process.argv.includes('--admin');
  const routes = withAdmin ? [...PUBLIC, ...ADMIN] : PUBLIC;

  const browser = await puppeteer.launch({
    executablePath: EDGE,
    headless: 'new',
    args: ['--no-sandbox', '--disable-dev-shm-usage', '--hide-scrollbars'],
  });

  const page = await browser.newPage();
  // Always measure the CSS on disk, never a cached copy from an earlier run.
  await page.setCacheEnabled(false);
  page.setDefaultNavigationTimeout(60000);

  if (withAdmin) {
    await page.setViewport({ width: 1280, height: 900 });
    await page.goto(`${BASE}/admin/login`, { waitUntil: 'domcontentloaded' });
    await page.type('#email', process.env.ADMIN_EMAIL || 'admin@kachifoodandlogistics.com');
    await page.type('#password', process.env.ADMIN_PASSWORD || 'admin123');
    await Promise.all([
      page.waitForNavigation({ waitUntil: 'domcontentloaded' }),
      page.click('button[type="submit"]'),
    ]);
    console.log('signed into the back office\n');
  }

  const findings = {};
  let total = 0;

  for (const route of routes) {
    for (const width of WIDTHS) {
      await page.setViewport({ width, height: 900, deviceScaleFactor: 1, isMobile: width < 768, hasTouch: width < 768 });
      await page.goto(BASE + route, { waitUntil: 'networkidle2', timeout: 45000 });

      const problems = await page.evaluate(measure);
      if (!problems.length) continue;

      // Collapse duplicates so one repeated card does not flood the report.
      const seen = new Map();
      problems.forEach((p) => {
        const key = p.type + '|' + p.detail.replace(/\d+/g, '#');
        if (!seen.has(key)) seen.set(key, { ...p, count: 1 });
        else seen.get(key).count++;
      });

      findings[`${route} @${width}`] = [...seen.values()];
      total += seen.size;
    }
  }

  await browser.close();

  const keys = Object.keys(findings);
  if (!keys.length) {
    console.log(`No responsive issues across ${routes.length} routes x ${WIDTHS.join('/')}px.`);
    return;
  }

  console.log(`${total} distinct issue(s) across ${keys.length} route/width combinations:\n`);
  keys.forEach((key) => {
    console.log(key);
    findings[key].forEach((p) => console.log(`   [${p.type}] ${p.detail}${p.count > 1 ? ` (x${p.count})` : ''}`));
    console.log('');
  });
})().catch((e) => { console.error(e); process.exit(1); });
