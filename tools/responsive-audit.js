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

    // Visually-hidden helpers are 1px boxes by design.
    if (el.closest('.sr-only, .skip-link')) return;
    // Decorative glows are absolutely positioned and deliberately clipped.
    if (style.position === 'absolute' && el.getAttribute('aria-hidden') !== 'false' && !el.textContent.trim()) return;

    const rect = el.getBoundingClientRect();
    if (rect.width === 0 && rect.height === 0) return;

    // (a) Sticking out past the viewport. Only an explicitly scrollable
    //     ancestor excuses this; overflow:hidden does not, because that
    //     silently clips content rather than making it reachable.
    if (rect.right > vw + 1 || rect.left < -1) {
      let scrollable = false;
      for (let p = el.parentElement; p; p = p.parentElement) {
        const ox = getComputedStyle(p).overflowX;
        if (ox === 'auto' || ox === 'scroll') { scrollable = true; break; }
      }
      if (!scrollable) {
        problems.push({ type: 'element-overflow', detail: `${describe(el)} right=${Math.round(rect.right)} vw=${vw}` });
      }
    }

    // (b) Escaping its own container. This is the class of bug that a
    //     viewport-only check misses entirely: a control can sit inside the
    //     screen while still spilling out of the card that holds it.
    const parent = el.parentElement;
    if (parent && parent !== document.body) {
      const pStyle = getComputedStyle(parent);
      const pRect = parent.getBoundingClientRect();
      const escapes = pStyle.overflowX === 'visible' && pRect.width > 0 &&
                      (rect.right > pRect.right + 1.5 || rect.left < pRect.left - 1.5);
      if (escapes && style.position !== 'absolute' && style.position !== 'sticky') {
        problems.push({
          type: 'escapes-container',
          detail: `${describe(el)} spills ${Math.round(Math.max(rect.right - pRect.right, pRect.left - rect.left))}px out of ${describe(parent)}`,
        });
      }
    }

    // (c) Real content clipped by an overflow:hidden box. Measured from
    //     in-flow children only: absolutely positioned decoration is meant
    //     to be clipped and would otherwise flood the report.
    if (style.overflowX === 'hidden' && el.clientWidth > 0 && el.children.length &&
        !(el instanceof SVGElement) && el.tagName !== 'svg') {
      let widest = 0;
      for (const child of el.children) {
        const cs = getComputedStyle(child);
        if (cs.position === 'absolute' || cs.position === 'fixed' || cs.display === 'none') continue;
        widest = Math.max(widest, child.getBoundingClientRect().right);
      }
      const boxRight = rect.left + el.clientWidth;
      if (widest > boxRight + 2) {
        problems.push({
          type: 'content-clipped',
          detail: `${describe(el)} content reaches ${Math.round(widest - rect.left)}px in a ${el.clientWidth}px box`,
        });
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
