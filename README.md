# KACHI Foodstuff Supplies & Logistics

Foodstuff supply and logistics platform for [kachifoodandlogistics.com](https://kachifoodandlogistics.com)
— wholesale and retail food ordering, plus standalone vehicle hire and haulage booking,
serving Asaba and the rest of Delta State.

**Stack:** PHP 8.2 · MySQL/MariaDB · Tailwind CSS v4 · no Composer, no framework.

---

## What it does

**Storefront**
- Catalogue of 47 products across 8 categories, with search, filtering, sorting and pagination.
- Dual pricing on every product: a retail price and an optional wholesale price that the cart
  applies automatically once the line quantity reaches the threshold.
- Cart and checkout capturing delivery address, date, time window, service level and payment method.
- Bulk quote requests for volumes or items that are not on the catalogue.
- Order tracking on a reference plus the email or phone on the order.

**Logistics module** — independent of the catalogue
- Booking form covering seven vehicle classes from motorcycle to flatbed.
- Live price estimator (base fare × distance band, payload surcharge, urgency multiplier,
  optional loading crew). It runs client-side for instant feedback and is **recalculated
  server-side on submit** — the browser figure is never trusted.
- Dispatch assigns a driver and vehicle registration, and moves the job through its timeline.

**Accounts** — order history, booking history, profile, password.

**Back office** — dashboard, orders, logistics bookings, products, categories, customers,
messages and settings. Staff reach everything except settings, role changes and deletions.

---

## Running it locally

Requires PHP 8.2+, MySQL/MariaDB, and Node 18+ only if you intend to rebuild CSS or images.

```bash
git clone https://github.com/Bigjoecode/kachifoodandlogistics.git
cd kachifoodandlogistics

cp config/config.local.example.php config/config.local.php   # set your DB credentials
```

Then open `/install.php` in a browser and run the installer. It creates the database, loads the
schema, seeds the catalogue and creates the demo accounts printed on screen.

**Delete `install.php` and change the seeded passwords before going live.**

### Rebuilding assets

Compiled CSS and processed images are committed, so the site runs as-is. Rebuild only after
editing templates or replacing the logo:

```bash
npm install
npm run css      # assets/css/tailwind.src.css -> assets/css/tailwind.css
npm run css:watch
npm run images   # regenerates assets/img/ from kachilogo.jpeg and k.jpeg
```

### Checks

```bash
node tools/responsive-audit.js --admin   # overflow, touch targets and text size, 320-1024px
node tools/shots.js home:/ cart:/cart    # screenshots + browser console errors
```

---

## Configuration

Credentials and anything server-specific go in `config/config.local.php`, which is git-ignored.
`config/config.php` guards every constant with `defined()`, so the local file always wins.
Copy `config/config.local.example.php` to start.

Business-facing values — contact details, social profiles, service areas, delivery pricing,
bank details, CAC number — are edited at runtime under **Admin → Settings**, not in code.

---

## Deployment

Pushing to `main` deploys to the live server over SSH via
[`.github/workflows/deploy.yml`](.github/workflows/deploy.yml).
Setup instructions, including the required repository secrets, are in
[`docs/DEPLOYMENT.md`](docs/DEPLOYMENT.md).

---

## Documentation

- [BUILD.md](BUILD.md) — architecture, design system, what exists and how it was verified
- [docs/DEPLOYMENT.md](docs/DEPLOYMENT.md) — SSH keys, GitHub secrets, first deploy, rollback
