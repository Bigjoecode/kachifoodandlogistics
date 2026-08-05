# KACHI Foodstuff Supplies & Logistics — build notes

Implementation notes for the platform built from the spec in [READ.md](READ.md).
`READ.md` is the product brief and is left untouched; this file records what actually exists.

**Stack:** PHP 8.2 + MySQL/MariaDB, no Composer. Tailwind CSS v4 is compiled ahead of time; the output is committed, so the site runs under XAMPP with no build step at request time.
**Local URL:** <http://localhost/kachifoodandlogistics/>

---

## Quick start

1. Start **Apache** and **MySQL** in XAMPP.
2. Open <http://localhost/kachifoodandlogistics/install.php> and click **Run the installer**.
   It creates the `kachi_food_logistics` database, loads the schema, seeds 47 products across
   8 categories, and creates three accounts, three demo orders and two demo logistics bookings.
3. Open the site. Delete `install.php` before going live.

| Role     | Email                             | Password   |
|----------|-----------------------------------|------------|
| Admin    | `admin@kachifoodandlogistics.com` | `admin123` |
| Staff    | `ops@kachifoodandlogistics.com`   | `ops12345` |
| Customer | `amaka@bellabites.ng`             | `demo1234` |

### Rebuilding assets

The CSS and brand images are committed, so the site runs as-is. Rebuild them only after
editing templates or swapping the logo:

```bash
npm install          # once
npm run css          # rebuild assets/css/tailwind.css  (npm run css:watch to watch)
npm run images       # regenerate assets/img/ from kachilogo.jpeg and k.jpeg
```

`DB_PORT` in [config/config.php](config/config.php) is set to **3307** — that is what this XAMPP
stack runs MySQL on. A stock XAMPP install uses 3306.

Business-facing values (contact details, service areas, delivery pricing, bank details, CAC number)
are edited at runtime under **Admin → Settings**, not in code.

---

## Front end

Built with **Tailwind CSS v4**, compiled to a real stylesheet — no CDN dependency at runtime.
Source of truth is [assets/css/tailwind.src.css](assets/css/tailwind.src.css); the build output
is `assets/css/tailwind.css` (~70KB unzipped).

| Spec | Where it lives |
|---|---|
| Navy `#082C5C` | `--color-navy-700` in the `@theme` block, with a full 11-step ramp built around it |
| Orange `#F58220` | `--color-orange-500`, used for CTAs, accents and the cart counter |
| Light Gray `#F8F9FB` | `--color-ink-50`, the page background |
| Dark Text `#1A1A1A` | `--color-ink-900` |
| Montserrat ExtraBold | `--font-display` on all headings, prices and stat figures |
| Inter | `--font-sans`, body text |

Repeated patterns (`.btn`, `.card`, `.input`, `.badge`, `.table`, `.tick-list`) are defined once
as Tailwind components, so the templates stay readable and cannot drift apart between pages.

The back office still runs on the original `assets/css/app.css`. Only the storefront was
redesigned, so admin styling is untouched and unaffected.

### Icons

[app/icons.php](app/icons.php) holds a hand-built inline SVG set — one visual language throughout
(24×24 viewBox, 1.75 stroke, round caps, no fills). Icons inherit `currentColor` and are sized with
utility classes. `category_icon()` maps each catalogue category to its glyph. No emoji are used as
icons anywhere.

### Brand imagery

[tools/build-images.js](tools/build-images.js) turns the two client files into web assets:

| Source | Produces |
|---|---|
| `kachilogo.jpeg` | `logo.png` (white background knocked out to transparency, brand colours preserved exactly), `logo-white.png` (navy ink flipped to white for dark surfaces, orange kept), `logo-mark.png`, favicon, apple-touch-icon, `og-image.jpg` |
| `k.jpeg` | Cropped photography panels — `truck.jpg` (hero and logistics), `signage.jpg` (about, coverage), `merch.jpg` (about), plus JPEG **and** WebP at sensible widths |

Products without an uploaded photo fall back to a branded category tile rather than a broken frame,
so the catalogue never looks half-finished.

### Responsive behaviour

[tools/responsive-audit.js](tools/responsive-audit.js) drives every route in a real browser at
**320 / 390 / 768 / 1024px** and fails on horizontal page overflow, elements wider than the
viewport, undersized touch targets and sub-12px text.

Thresholds follow the actual standards rather than one blanket number:

- **44px** for controls — buttons, inputs, selects (Apple HIG).
- **24px** for standalone links (WCAG 2.2 AA, 2.5.8 Target Size Minimum).
- Links inside a sentence are exempt, which is the WCAG *Inline* exception. The audit detects
  that structurally by comparing the link text against its parent's text, instead of
  maintaining a hand-written list of container classes.

Current state across all 24 routes (storefront **and** back office):

```
No responsive issues across 24 routes x 320/390/768/1024px.
```

Getting there needed real fixes, not just calibration: footer and utility-bar links given
proper hit areas, breadcrumbs lifted to 24px, sub-12px labels raised to the 12px floor, and a
`max-width: 768px` block in `app.css` that lifts every back-office control to 44px while
desktop keeps its denser sizing.

### Back office on mobile

The sidebar is an off-canvas drawer below 1024px, opened from a sticky bar that
carries the menu button and the current page title. It closes on Escape, on the scrim,
on its own close button, and on following a nav link; focus moves into it on open and
returns to the button on close; focus is trapped while it covers the page; page scroll
is locked meanwhile. Desktop keeps the permanent sidebar and none of that code runs.

Data tables stack into labelled cards below 768px. Labels are copied from each table's
own `<th>` cells by `app.js`, so every admin table gets it with no per-page markup and
the labels cannot drift from the headers.

[tools/admin-drawer-test.js](tools/admin-drawer-test.js) drives all of this in a real
browser — 19 checks covering open, close by four routes, ARIA state, scroll lock, focus,
table stacking and desktop inertness.

### Accessibility and performance

- Every interactive control is at least 44px tall (`min-h-11` on `.btn`, `.input`, `.qty`).
- Focus rings are restyled, never removed; a skip link jumps to `#main`.
- `prefers-reduced-motion` disables all animation, including the reveal-on-scroll.
- Alerts use `role="alert"`; the flash region is an `aria-live` polite region.
- Images carry explicit `width`/`height` to avoid layout shift; below-fold images are lazy-loaded.
- Prices and totals use tabular figures so they do not jitter.
- Headings use `text-wrap: balance`; body copy is capped for line length.

---

## Deployment

Pushing to `main` triggers [`.github/workflows/deploy.yml`](.github/workflows/deploy.yml):
build the stylesheet, fail if it came out empty, `php -l` every file, rsync over SSH, then
health-check the homepage and fail the job on anything but a 200.

`rsync --delete` keeps the server matching the repo, except for server-owned paths that must
survive a deploy: `config/config.local.php`, `assets/uploads/`, and `install.php`.

Credentials never enter the repository. `config/config.php` guards every environment constant
with `defined()`, so a git-ignored `config/config.local.php` on each machine takes precedence.
[config/config.local.example.php](config/config.local.example.php) is the template.

Full setup — SSH key authorisation in cPanel, the required repository secrets, first deploy and
rollback — is in [docs/DEPLOYMENT.md](docs/DEPLOYMENT.md).

---

## What was built

### Storefront
- **Homepage** with the spec's hero (*Foodstuff Supplies & Logistics You Can Trust*), all three CTAs
  (Order Food / Book Logistics / Explore Products), a Trusted By row, both business lines, the
  category grid, featured lines, coverage map of towns, trust signals and the four-step explainer.
- **Catalogue** across the spec's 8 categories, with search, category pages, price-range filter,
  sorting and pagination.
- **Wholesale and retail pricing on every product.** Each line carries a retail price, an optional
  wholesale price and the quantity at which it applies. The cart re-prices each line by quantity on
  every render, shows which lines are on the wholesale rate, and tells you how many more units would
  unlock it. Optional sale price overrides retail below the bulk threshold.
- **Product pages** with unit pricing, a wholesale panel showing the per-unit saving, stock state,
  origin, minimum order, related products and a **WhatsApp order button** with the product pre-filled.
- **Cart and checkout** capturing delivery address, date, time window, service level and payment
  method. Guest or signed-in; signed-in profiles pre-fill the form.
- **Bulk quote requests** for volumes or items not on the catalogue, priced later by the sales desk.
- **Order tracking** — one form handles both reference types and routes on the prefix.
- Services, About, **FAQs** and Contact pages.
- **Floating WhatsApp button** site-wide.

### Logistics module (independent of the catalogue)
- `/logistics` booking form: service type, vehicle, pickup and destination, date, time window,
  distance band, weight, urgency and an optional loading crew.
- **Live price estimator.** Base fare by vehicle × distance band, plus an overload surcharge when the
  weight exceeds the vehicle's payload, plus an urgency multiplier, plus the labour fee. It runs
  client-side for instant feedback and is **recalculated server-side on submit** — the client figure
  is never trusted. Seven vehicle classes from Motorcycle to Flatbed.
- Booking confirmation, timeline, and tracking under the same `/track` form.
- Dispatch side: quote a firm price, assign a driver and vehicle registration, move status through
  `pending → quoted → confirmed → assigned → in_transit → completed`, and add timeline notes.

### Accounts
Register, sign in, edit profile and default delivery details, change password, and see **both**
order history and logistics booking history with live status and full timelines.

### Back office (`/admin`)
Dashboard (orders, quotes, logistics awaiting pricing, revenue, 14-day chart, pipeline, best sellers,
low stock, latest bookings), Orders, Logistics bookings, Products (full CRUD with image upload and
dual pricing), Categories, Customers, Messages and Settings.

Staff reach everything except Settings, role changes and deletions; those are admin-only.

### SEO
- `LocalBusiness` + `Organization` + `WebSite`/`SearchAction` schema on every page, driven by the
  live settings so the address and service areas stay in sync.
- `Product` + `BreadcrumbList` schema on product pages, `FAQPage` on `/faqs` generated from the same
  array that renders the accordion, so the two cannot drift apart.
- Canonical URLs, per-page meta descriptions, Open Graph and Twitter tags.
- `/sitemap.xml` generated from live catalogue data — new products appear without anyone editing a
  file. `robots.txt` blocks admin, account, cart, checkout and order pages.

---

## Layout

```
kachifoodandlogistics/
├── index.php              Front controller: route table, CSRF gate, guards, dispatch
├── install.php            One-shot installer (delete after setup)
├── robots.txt  .htaccess
├── config/                Constants and the PDO wrapper
├── app/
│   ├── helpers.php        Escaping, URLs, flash, CSRF, money, status vocabulary
│   ├── auth.php  cart.php
│   └── models/            Product, Category, Order, Booking, User, Message, Setting
├── pages/                 One file per route: logic on top, markup below
│   └── admin/  account/  auth/  errors/
├── partials/              header, footer, admin chrome, cards, pagination, timelines, schema
├── assets/css  assets/js  assets/uploads
└── database/              schema.sql, seed.sql
```

Adding a page is one line in the `$routes` array in [index.php](index.php) plus one file in `pages/`.

### Data model

| Table | Holds |
|---|---|
| `users` | Customers, staff and admins (`role`), with default delivery details |
| `categories` / `products` | Catalogue, dual pricing, stock, origin, images |
| `orders` / `order_items` / `order_events` | Food orders and quotes, line snapshots, status timeline |
| `logistics_bookings` / `logistics_events` | Vehicle bookings, pricing, driver assignment, timeline |
| `contact_messages` | Contact-form inbox |
| `settings` | Runtime business configuration |

Order lines store a **snapshot** of name, unit and price — renaming or re-pricing a product later
never rewrites historical orders.

---

## Security

- All queries are prepared statements through the `Db` wrapper.
- Every POST route is CSRF-checked centrally in `index.php` before any handler runs.
- Passwords use `password_hash()`/`password_verify()`; session id regenerates on login, logout
  and registration.
- Output escaped with `e()` at the point of rendering.
- Route guards declared in the route table, not left to each page to remember.
- Tracking requires reference **plus** a matching email or phone, so references are not enumerable.
- Confirmation pages are visible only to the session that placed the order, the account that owns
  it, or staff.
- Uploads are extension- and MIME-checked, size-capped, renamed randomly, and served from a
  directory where PHP execution is disabled.
- The logistics estimate is always recomputed server-side; the client-side figure is display only.
- An admin cannot change their own role or disable their own account.

---

## Verification

All PHP files pass `php -l`. Every front-end route was exercised end to end against Apache and
rendered in a real browser (Edge, headless) at 1440px and 390px with **no console errors**:

- storefront pages, 404s and guard redirects
- wholesale pricing (2 units → retail, 12 units → wholesale rate applied in the cart)
- add-to-cart → update → checkout → confirmation → tracking
- logistics booking → confirmation → tracking, plus rejection of an invalid vehicle type
- tracking with both `KFL-` and `KFL-L-` references, and rejection of a wrong contact
- registration, account, orders and bookings pages
- admin sign-in and every admin screen
- admin writes: order status, payment, re-pricing, booking quote, driver assignment, booking status,
  product create, and rejection of a wholesale price above retail
- POST without a CSRF token is rejected
- seeded booking estimates recomputed with `Booking::estimate()` and confirmed to match byte for byte

No page emits a PHP warning, notice or deprecation.

Screenshots are regenerated with `node tools/shots.js home:/ products:/products` (any
`name:path` pairs), which also reports browser console errors.

---

## Deferred from the spec

These are in `READ.md` but **not** built in this pass:

| Area | Note |
|---|---|
| Email / SMS / push notifications | Nothing is sent. Confirmations are on-screen and in the database. Wiring PHPMailer or an SMTP relay is the natural next step. |
| Paystack / Flutterwave | Checkout records payment intent and shows bank details; no gateway integration. |
| Driver dashboard, GPS, live map | Driver name and vehicle reg are recorded and shown on the timeline, but there is no driver login or GPS feed. |
| Blog, recipes, articles | Not built. This is the biggest remaining SEO lever in the spec. |
| Reviews and ratings | Not built. |
| Wishlist, recently viewed, saved shopping lists, reorder | Not built. |
| Coupons and flash sales | Sale price per product exists; no coupon engine. |
| Location landing pages (`/food-supplies-asaba`, etc.) | Not built. Highest-priority SEO item in the spec after the blog. |
| Price tracker / "rice price today" pages | Not built. |
| AI shopping assistant, smart search with typo tolerance | Search is LIKE-based over name, summary, SKU and origin. |
| Delivery time calculator, Google Maps embeds | Distance is a band the customer selects, not a computed route. |
| PWA, offline support, dark mode | Not built. |
| Product galleries | One image per product. |

Also worth knowing: stock decrements when an order is placed and is **not** returned if the order is
later cancelled. Delivery pricing for food orders is a flat fee plus a free-delivery threshold;
zone or weight-based rating would need a rate table.
