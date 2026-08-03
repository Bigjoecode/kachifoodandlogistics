# Deployment

Pushing to `main` builds the CSS, lints every PHP file, and syncs the result to the live
server over explicit FTPS. The workflow is [`.github/workflows/deploy.yml`](../.github/workflows/deploy.yml).

The dedicated FTP account is restricted to `public_html`, and its password is stored in
the `FTP_DEPLOY` repository secret. Database migrations run idempotently before the first
web request is handled because shared hosting does not provide shell access.

---

## One-time setup

### 1. Authorise the deploy key in cPanel

A dedicated ed25519 keypair was generated for this. The **private** key is on the machine
that ran the setup, at `keys/deploy_key` — it is git-ignored and must never be committed.

The **public** key is:

```
ssh-ed25519 AAAAC3NzaC1lZDI1NTE5AAAAILthLfCTjsHp3nuql3i4Q3aBnMfXWkXXbI1FWfbFRLxs github-actions-deploy@kachifoodandlogistics
```

In cPanel:

1. **SSH Access → Manage SSH Keys → Import Key**
2. Leave the private key box empty. Paste the public key above into the **public key** box.
3. Name it `github-actions-deploy` and import.
4. Back on Manage SSH Keys, find it under *Public Keys* and click **Manage → Authorize**.

It must read **authorized**. An imported-but-unauthorised key will not let the deploy in.

### 2. Find your deploy path

SSH in and check where the site is served from:

```bash
ssh -p 22 YOUR_CPANEL_USER@kachifoodandlogistics.com
pwd                 # usually /home/YOUR_CPANEL_USER
ls public_html      # the document root for the primary domain
```

For a primary domain the deploy path is normally `/home/YOUR_CPANEL_USER/public_html`.
If the site is an addon domain, it will be something like
`/home/YOUR_CPANEL_USER/kachifoodandlogistics.com`.

### 3. Add the repository secrets

**GitHub → Settings → Secrets and variables → Actions → New repository secret**

| Secret | Value | Required |
|---|---|---|
| `SSH_PRIVATE_KEY` | The entire contents of `keys/deploy_key`, including the `-----BEGIN…` and `-----END…` lines and the trailing newline | yes |
| `SSH_HOST` | `kachifoodandlogistics.com`, or the server IP | yes |
| `SSH_USER` | Your cPanel username | yes |
| `DEPLOY_PATH` | e.g. `/home/YOUR_CPANEL_USER/public_html` | yes |
| `SSH_PORT` | Only if your host does not use 22 | no |
| `HEALTHCHECK_URL` | Defaults to `https://kachifoodandlogistics.com/` | no |

### 4. Create the server-side config

The workflow never uploads credentials, so create this once on the server:

```bash
ssh YOUR_CPANEL_USER@kachifoodandlogistics.com
cd ~/public_html
cp config/config.local.example.php config/config.local.php
nano config/config.local.php
```

Fill in the production values:

```php
define('APP_ENV', 'production');      // turns debug output off
define('APP_DOMAIN', 'https://kachifoodandlogistics.com');
define('DB_HOST', 'localhost');
define('DB_PORT', '3306');
define('DB_NAME', 'kachifoo_live');
define('DB_USER', 'kachifoo_app');
define('DB_PASS', '…');
```

`config.local.php` is in the rsync exclude list, so deploys never overwrite it.

### 5. Set up the database, once

Upload `install.php` manually the first time (the workflow excludes it), open it in a
browser, run it, then **delete it from the server**. After that, apply schema changes with
SQL migrations rather than re-running the installer, which drops every table.

---

## Deploying

```bash
git push origin main
```

Watch it under the repository's **Actions** tab. The job:

1. installs dependencies and rebuilds `assets/css/tailwind.css`
2. fails the build if the stylesheet came out empty
3. runs `php -l` over every PHP file
4. rsyncs to the server
5. applies each file in `database/migrations/` exactly once
6. requests the homepage and fails if it does not return `200`

`workflow_dispatch` lets you redeploy from the Actions tab without a new commit.

### What is never overwritten

`rsync --delete` keeps the server matching the repository, except for these, which are
server-owned:

- `config/config.local.php` — production credentials
- `assets/uploads/` — customer-uploaded product images
- `install.php` — kept off the server entirely after first setup
- `node_modules/`, `tools/`, `docs/`, `.github/`, `README.md`, `BUILD.md`, `package*.json`,
  `assets/css/tailwind.src.css` — build and development files with no place in production

---

## Rolling back

```bash
git revert <bad-commit>
git push origin main
```

That redeploys the previous state through the same pipeline. Avoid `push --force`: the
deploy mirrors whatever `main` holds, so a forced rewrite can wipe files on the server
before you notice.

---

## Security notes

- The repository is **public**. Never commit credentials. `config.local.php`, `keys/`,
  `.env` and `READ.md` are all git-ignored.
- `READ.md` is excluded because it contains a plaintext mailbox password. Redact that block
  before committing any version of it.
- Rotate the `info@` mailbox password — it has been shared in plaintext.
- Change the seeded `admin123` / `ops12345` passwords immediately after the first install.
- The deploy key only grants what the cPanel account can do. Revoke it in
  **SSH Access → Manage SSH Keys** if it is ever exposed, then generate a new pair:
  ```bash
  ssh-keygen -t ed25519 -C "github-actions-deploy@kachifoodandlogistics" -f keys/deploy_key -N ""
  ```

---

## Troubleshooting

| Symptom | Cause |
|---|---|
| `Permission denied (publickey)` | Key imported but not **Authorized** in cPanel, or `SSH_PRIVATE_KEY` is missing its BEGIN/END lines |
| `Host key verification failed` | `SSH_HOST` does not match the real hostname |
| Deploy succeeds, site 500s | `config/config.local.php` missing or wrong on the server — check the cPanel error log |
| Styles missing after deploy | Look at the *Build the stylesheet* step; the `test -s` guard should have caught an empty file |
| Health check fails but the site loads | `HEALTHCHECK_URL` is wrong, or the host blocks the runner — set it to a URL that returns 200 |
