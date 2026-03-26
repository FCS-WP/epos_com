# EPOS.com — Website Architecture

## 1. Infrastructure & Hosting

### Deployment Environments

| Environment | Trigger | Method | Target |
|---|---|---|---|
| **Production SFTP** | Tag `production-*` | GitHub Actions → FTP-Deploy-Action | SFTP server |
| **DigitalOcean Docker** | Tag `deploy-*` | GitHub Actions → SSH + docker compose | DO Droplet (`/var/www/epos_com`) |

### Docker Setup

```
┌─────────────────────────────────────────────────────────┐
│  DigitalOcean Droplet (deployer@DO_HOST)                │
│                                                         │
│  ┌─────────────── Docker Network: webnet ─────────────┐ │
│  │                                                     │ │
│  │  ┌─────────────────────┐   ┌────────────────────┐  │ │
│  │  │ WordPress (6.9)     │   │ MySQL (shared)     │  │ │
│  │  │ Container: $ID      │──▶│ Host: mysql        │  │ │
│  │  │ User: www-data      │   │ Prefix: fcs_data_  │  │ │
│  │  │ Port: 80 → ${PORT}  │   │                    │  │ │
│  │  │                     │   └────────────────────┘  │ │
│  │  │ Apache (mod_rewrite)│                            │ │
│  │  │ PHP 8.x             │                            │ │
│  │  │ Memory: 256M/512M   │                            │ │
│  │  │ Upload: 20MB max    │                            │ │
│  │  └─────────────────────┘                            │ │
│  └─────────────────────────────────────────────────────┘ │
└─────────────────────────────────────────────────────────┘
```

### Volume Mounts

| Host Path | Container Path |
|---|---|
| `./src/.htaccess` | `/var/www/html/.htaccess` |
| `./src/wp-content/themes` | `/var/www/html/wp-content/themes` |
| `./src/wp-content/plugins` | `/var/www/html/wp-content/plugins` |
| `./src/wp-content/uploads` | `/var/www/html/wp-content/uploads` |

Default WordPress themes (`twentytwenty*`) are excluded via empty volume overrides.

### Key .env Configuration

| Variable | Description |
|---|---|
| `PROJECT_ID` | Docker container name |
| `PORT` / `PROJECT_HOST` | Local dev port and host |
| `THEME_NAME` | Active theme (default: "zippy") |
| `WORDPRESS_DB_*` | Database connection |
| `WORDPRESS_TABLE_PREFIX` | `fcs_data_` |
| `WP_POST_REVISIONS` | `false` (disabled) |
| `FS_METHOD` | `direct` |

### Security (.htaccess)

- XML-RPC: **Disabled** (blocked via htaccess)
- URL Rewriting: Pretty permalinks via `mod_rewrite`
- Authorization header preserved for REST API requests

---

## 2. Application Stack

### Theme Hierarchy

```
zippy (Parent Theme v3.19.6)
  ├── Based on: Flatsome / UX-Themes
  ├── Multi-Purpose WooCommerce Theme
  └── Built-in CPTs: blocks, featured_item

    └── zippy-child (Child Theme v3.0)
          ├── functions.php ─── Loads all /includes/*.php via glob
          ├── header.php ────── Multi-region routing
          ├── header-my.php ── Malaysia region header (/my/*)
          ├── header-360.php ── EPOS 360 header (/my/epos360/*)
          ├── /woocommerce/ ── Template overrides (checkout, cart, emails)
          ├── /acf-field/ ──── ACF field configurations
          ├── /site-structure/ & /template-parts/
          └── /includes/ ───── Framework modules (see below)
```

### Multi-Region Routing (header.php)

```
REQUEST_URI
  ├── /my/epos360/*  →  header-360.php (EPOS 360 product line)
  ├── /my/*           →  header-my.php  (Malaysia market, my_menu)
  └── (default)       →  template-parts  (Global / SG site)
```

### Theme Includes (`/includes/`)

**Core Framework Files:**

| File | Purpose |
|---|---|
| `shin_helper.php` | Utility functions, health-check endpoints |
| `shin_cpt.php` | Custom post types (properties + taxonomy) |
| `shin_custom.php` | Custom functionality |
| `shin_optimise.php` | Performance optimizations |
| `shin_admin.php` | Admin customizations |
| `shin_block.php` | Block editor customizations |
| `shin_acf.php` | Advanced Custom Fields setup |
| `zippy_checkout.php` | Checkout field modifications |
| `shin_popup_geoip.php` | Geolocation-based popups |
| `zippy_footer.php` | Footer customizations |
| `zippy_my_menu.php` | Menu setup |
| `epos_gsap.php` | GSAP animation setup |

**Sub-Module Directories:**

| Directory | Purpose |
|---|---|
| `fb_tracking/` | Facebook Pixel (client) + CAPI (server-side) |
| `gtm_tracking/` | Google Tag Manager (region-based GTM IDs) |
| `hubspot_intergration/` | HubSpot CRM contact/deal sync |
| `ant_bot/` | DingTalk daily order report bot |
| `epos_my_custom_flow/` | Malaysia-specific checkout, cart, shortcodes, popups |
| `workable/` | Workable API integration (careers page) |
| `page/` | Page-specific logic (epos360, bluetap_onboarding) |

### Custom Post Types

| Post Type | Source | Taxonomy |
|---|---|---|
| `properties` | `shin_cpt.php` (child theme) | `categories_properties` |
| `blocks` | Parent theme (UX-Blocks) | `block_categories` |
| `featured_item` | Parent theme (Portfolio) | `featured_item_category`, `featured_item_tag` |

---

## 3. Plugin Architecture

### zippy-core (v8.0)

Custom core plugin with MVC modular architecture.

- **REST API Namespace:** `zippy-core/v2`
- **Architecture:** `Core_Module` abstract class, Singleton pattern
- **Auth Middleware:** `Core_Middleware::admin_only`

**Modules:**

```
src/modules/
  ├── orders/       ─── Order CRUD, export CSV/PDF, invoice generation (Dompdf)
  │   ├── controllers/
  │   ├── routes/
  │   ├── services/
  │   ├── models/
  │   └── templates/   (invoice-template.php)
  ├── products/     ─── Product management API
  ├── settings/     ─── Site configuration API
  ├── shipping/     ─── Shipping tax recalculation
  └── postal_code/  ─── Postal code data
```

**Additional Components:** `Zippy_Analytics`, `Zippy_User_Account_Expiry`, `Zippy_MPDA_Consent`

### zippy-pay (v7.0.1)

Payment gateway integration plugin.

| Gateway | Status | Integration |
|---|---|---|
| **2C2P** | **Active** | JWT signed, Drop-in UI (pgw-sdk-4.2.1.js), Credit Card / TNG |
| PayNow | Available | QR-based, Zippy REST API (rest.zippy.sg) |
| Antom | Available | Alipay, Zippy REST API session-based |
| Adyen | Available | International payments |

**Callbacks:** `?wc-api=zippy_2c2p_transaction`, `?wc-api=zippy_2c2p_redirect`

### Third-Party Plugins

| Plugin | Purpose |
|---|---|
| **WooCommerce** | E-commerce platform |
| **ACF Pro** | Custom fields (stores API tokens, pixel IDs as options) |
| **Rank Math SEO** | Sitemap, schema, on-page SEO |
| **HubSpot (leadin)** | Lead capture & form integration |
| **Unbounce** | Landing page builder |
| **Classic Editor** | Classic WordPress editor |
| **Custom Post Type UI** | CPT management interface |
| **Aryo Activity Log** | Admin activity tracking |

---

## 4. External Integrations

### Payment

| Service | API | Details |
|---|---|---|
| **2C2P** | `/paymentToken`, `/transactionStatus`, `/paymentInquiry` | JWT (HS256) signed, Drop-in pgw-sdk UI, Credit Card & Touch 'n Go |

### CRM

| Service | API | Details |
|---|---|---|
| **HubSpot** | `api.hubapi.com/crm/v3/` | Upsert contacts on order, create deals on payment, UTM sync, lifecycle tracking. Bearer token from ACF options |

### Analytics & Tracking

| Service | Implementation | Details |
|---|---|---|
| **Facebook / Meta** | Pixel (client) + CAPI (server) | Event deduplication via `PURCHASE_{id}`. CAPI on `woocommerce_order_status_processing` |
| **Google Tag Manager** | `wp_head` + `wp_body_open` | Public: `GTM-TBKNQPPH`, MY region (`/my/*`): `GTM-P4PQK8KM` |

### Notifications

| Service | Trigger | Details |
|---|---|---|
| **DingTalk (Ant Bot)** | Daily schedule | Order report: revenue, devices, channels, top states. HMAC-SHA256 auth via `oapi.dingtalk.com/robot/send` |
| **Telegram** | CI/CD events | Deploy start/complete notifications, PR creation alerts |

### Other

| Service | Purpose |
|---|---|
| **Workable** | Careers page - job listings & applications |
| **Unbounce** | Custom landing pages |
| **Rank Math** | SEO, sitemap generation |

---

## 5. Frontend Build

### Webpack 5 Pipeline

```
Source                     Build Pipeline              Output
─────────────────          ─────────────────           ─────────────────
assets/sass/app.scss  ──▶  SASS → CSS (sass-loader)
                           ES6+ → ES5 (babel-loader)  ──▶  assets/dist/css/main.min.css
assets/js/app.js      ──▶  MiniCssExtract                  assets/dist/js/main.min.js
                           CssMinimizer (prod)
```

### Build Commands

| Command | Description |
|---|---|
| `npm run dev` | Watch mode + BrowserSync (proxied to `PROJECT_HOST`) |
| `npm run build` | Development build |
| `npm run dist` | Production build (minified) |

**Node.js:** v18.18.2 | **jQuery:** 3.7 (global via ProvidePlugin)

### Frontend Libraries

| Library | Purpose |
|---|---|
| jQuery 3.7 | DOM manipulation (global) |
| Slick Carousel | Slider/carousel components |
| intl-tel-input | International phone input (checkout) |
| GSAP | Scroll animations |

### SCSS Structure (app.scss)

Imports organized by: mixins/variables → components (header, footer, buttons, spacing, icons) → page-specific styles (MY region pages, checkout, BlueTap, EPOS 360, shop, careers, video hub, FAQ, partner program)

### JS Modules (app.js)

Imports: Slick library → video hub → careers → checkout (+ phone validation) → scroll navigation → BlueTap onboarding → promo popup → tab switching → animated carousel

---

## 6. CI/CD Pipeline

### Branch Strategy

```
update-*  ──Auto PR──▶  master  ──Merge──▶  production  ──Tag──▶  Deploy
(feature)               (integration)        (prod state)
                                                 │
                                          ┌──────┴──────┐
                                          ▼              ▼
                                   production-*      deploy-*
                                   (SFTP Deploy)   (Docker Deploy)
```

### Workflow 1: Auto PR + Notify (`git_action_noti.yml`)

**Trigger:** Push to `update-*` or `main`

1. Auto-create PR to `master` (via `peter-evans/create-pull-request@v6`)
2. Assign reviewer: `ThanhNha`
3. Send Telegram notification with PR URL + commit details

### Workflow 2: Production SFTP (`git_action_deploy.yml`)

**Trigger:** Tag `production-*`

1. Send Telegram: deployment started
2. Checkout code + setup Node.js v20
3. `npm install` + `npm run build`
4. SFTP sync to server (via `SamKirkland/FTP-Deploy-Action@v4.3.5`)
   - Excludes: `.git`, `node_modules`, `wp-content/plugins`
5. Send Telegram: deployment complete

### Workflow 3: Docker Deploy (`digital_deploy.yml`)

**Trigger:** Tag `deploy-*`

1. SSH into DigitalOcean (via `appleboy/ssh-action@v1.0.3`)
2. `git fetch` + `git reset --hard` to tag
3. Generate `.env` from GitHub Secrets
4. `yarn install --frozen-lockfile` + `yarn build`
5. Cleanup: `rm -rf node_modules && yarn cache clean`
6. `docker compose up -d --build`
7. Fix permissions: `chown -R 33:33 src/wp-content && chmod -R 775 src/wp-content`
8. `docker image prune -f`
9. Send Telegram: deployment success

### GitHub Secrets Required

| Category | Secrets |
|---|---|
| **SFTP** | `FPT_SERVER`, `FPT_USERNAME`, `FTP_PASSWORD`, `FPT_DIR_PATH` |
| **DigitalOcean** | `DO_HOST`, `DO_SSH_KEY` |
| **Database** | `WORDPRESS_DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASSWORD` |
| **App** | `PROJECT_ID`, `THEME_NAME` |

---

## 7. REST API Reference

### zippy-core/v2 (Admin-only)

| Method | Endpoint | Purpose |
|---|---|---|
| `GET` | `/orders` | List orders (paginated, filterable) |
| `GET` | `/get-order-info` | Order detail |
| `GET` | `/order-details` | Full order breakdown |
| `POST` | `/bulk-action-update-order-status` | Bulk status update |
| `POST` | `/move-to-trash` | Trash orders |
| `POST` | `/export-orders` | Export CSV/PDF |
| `POST` | `/remove-item-order` | Remove line item |
| `POST` | `/update-quantity-order-item` | Update item qty |
| `POST` | `/apply_coupon_to_order` | Apply coupon |
| `POST` | `/add-items-order` | Add products |
| `POST` | `/download-invoice` | Generate PDF invoice |
| `POST` | `/send-order-email` | Send WC email |

### Portal API

- **Namespace:** `portal/v1`
- **Internal URL:** `http://host.docker.internal:8000`

### WC-API Callbacks

| Endpoint | Purpose |
|---|---|
| `?wc-api=zippy_2c2p_transaction` | 2C2P backend payment callback |
| `?wc-api=zippy_2c2p_redirect` | 2C2P frontend redirect handler |

---

## 8. Backup & Monitoring

### Automated Backup (`backup.sh`)

Scheduled backup script running on the DigitalOcean server.

```
┌──────────────────────────────────────────────────────┐
│  backup.sh (cron scheduled)                          │
│                                                      │
│  1. Load .env variables                              │
│  2. Database Backup (mysqldump → gzip)               │
│  3. Files Backup (wp-content/uploads → tar.gz)       │
│  4. Send DB dump to Telegram                         │
│  5. Cleanup old backups                              │
└──────────────────────────────────────────────────────┘
```

**Backup Flow:**

| Step | Action | Details |
|---|---|---|
| 1 | **Database dump** | `docker exec mysql mysqldump` → gzip compressed |
| 2 | **Files archive** | `tar -czf` on `src/wp-content/uploads` |
| 3 | **Telegram upload** | DB dump sent as document to Telegram group |
| 4 | **Cleanup: DB** | Delete DB backups older than **7 days** |
| 5 | **Cleanup: Files** | Keep only **2 most recent** upload backups |

**Backup Storage:**

| Type | Location | Filename Pattern | Retention |
|---|---|---|---|
| Database | `/backups/wordpress/` | `epos_com_db_YYYY-MM-DD_HHMMSS.sql.gz` | 7 days |
| Uploads | `/backups/wordpress/` | `epos_com_files_YYYY-MM-DD_HHMMSS.tar.gz` | 2 latest |

**Telegram Notification:**
- Bot sends DB backup file as document to chat
- Chat ID: `-1002034905977` / Topic ID: `6433`

### Server Monitoring (`mornitor.sh`)

Lightweight health-check script that alerts via Telegram when thresholds are exceeded.

```
┌──────────────────────────────────────────────────────┐
│  mornitor.sh (cron scheduled)                        │
│                                                      │
│  ┌─────────────┐     ┌─────────────┐                │
│  │ Check Disk   │     │ Check RAM   │                │
│  │ Threshold:   │     │ Threshold:  │                │
│  │   > 90%      │     │   > 90%     │                │
│  └──────┬───────┘     └──────┬──────┘                │
│         │                    │                        │
│         ▼                    ▼                        │
│  ┌─────────────────────────────────────┐             │
│  │ If exceeded → Telegram CRITICAL     │             │
│  │ alert with hostname + usage %       │             │
│  └─────────────────────────────────────┘             │
└──────────────────────────────────────────────────────┘
```

**Monitored Metrics:**

| Metric | Command | Threshold | Alert Level |
|---|---|---|---|
| **Disk usage** | `df /` | > 90% | CRITICAL |
| **RAM usage** | `free` (used/total) | > 90% | WARNING |

**Alert Details:**

| Metric | Telegram Message |
|---|---|
| Disk | "CRITICAL: Disk Space Low on {hostname} — Now: {usage}% full. Please clean up backups or logs!" |
| RAM | "WARNING: High RAM Usage on {hostname} — Now: {usage}%. Building assets might fail!" |

**Telegram Notification:**
- Chat ID: `-1002034905977` / Topic ID: `6436` (separate topic from backups)
- Format: Markdown

### Activity Logging

| Component | Tool | Details |
|---|---|---|
| **Admin activity** | Aryo Activity Log plugin | Tracks admin actions, content changes |
| **Payment events** | `ZIPPY_Pay_Logger::log_checkout()` | All 2C2P API calls, retries, outcomes |
| **Order sync** | HubSpot integration logs | Contact upsert, deal creation events |
| **Deploy events** | Telegram notifications | CI/CD deploy start/complete, PR creation |
| **Daily reports** | DingTalk (Ant Bot) | Revenue, devices, channels, top states |

---

## 9. Known Issues & Limitations

### Infrastructure

| Issue | Details | Impact |
|---|---|---|
| **No staging environment** | Only production SFTP and Docker deploy — no staging/QA server for pre-release testing | Changes are tested locally or deployed directly to production |
| **Docker image version mismatch** | `Dockerfile` uses `wordpress:6.3`, `docker-compose.yml` uses `wordpress:6.9` | Potential inconsistency between build and runtime environments |
| **`ALLOW_UNFILTERED_UPLOADS=true`** | Allows all file types to be uploaded via WordPress media | Security risk — any file type (PHP, EXE) can be uploaded by admin users |
| **`git reset --hard` in deploy** | Docker deploy workflow uses `git reset --hard origin/production` | Destroys any manual changes made directly on the server |
| **No SSL/TLS configuration** | `.htaccess` and Docker setup have no HTTPS enforcement | Assumed to be handled by external proxy/load balancer — not documented |

### CI/CD Pipeline

| Issue | Details | Impact |
|---|---|---|
| **Hardcoded Telegram credentials** | Bot token and chat ID are hardcoded in workflow YAML files, not stored as secrets | Credentials exposed in repository history |
| **No rollback mechanism** | Deploy workflows have no automatic rollback on failure | Failed deploys require manual intervention to restore previous version |
| **No build verification** | No test, lint, or smoke test step before deploying | Broken code can be deployed to production |
| **SFTP excludes plugins** | `git_action_deploy.yml` excludes `wp-content/plugins` from sync | Plugin updates must be managed separately (manually or via WP admin) |
| **`yarn` vs `npm` inconsistency** | SFTP deploy uses `npm install && npm run build`, Docker deploy uses `yarn install && yarn build` | Different lockfiles could produce different builds |

### Payment (2C2P)

| Issue | Details | Impact |
|---|---|---|
| **3 retries max, then `failed`** | Cron retries only 3 times (1 min → 5 min → 15 min) before marking order as failed | Orders that take longer than ~21 minutes to process will be marked failed even if payment succeeds later |
| **No webhook retry from 2C2P** | If backend callback is missed (server down), only client-side polling and cron retries can catch it | Potential for orphaned pending orders if all 3 paths fail |
| **Stub API files** | `zippy-tctp-api.php` and `zippy-tctp-services.php` are empty stubs | No abstraction layer for 2C2P API calls — all logic is in the gateway class |
| **Inactive gateways still loaded** | PayNow, Antom, Adyen integration files are loaded even though only 2C2P is active | Minor performance overhead from unused code |

### HubSpot Integration

| Issue | Details | Impact |
|---|---|---|
| **Typo in directory name** | `hubspot_intergration/` (should be `integration`) | Cosmetic but may cause confusion for new developers |
| **Typo in code** | ACF field named `secrect` instead of `secret` (DingTalk config) | Must keep the typo to match stored data |
| **No retry on API failure** | HubSpot API calls have no retry or queue mechanism | If HubSpot is down during checkout, contact/deal data is silently lost |
| **Duplicate contact creation risk** | PATCH by email → if 404 → POST new contact. Race conditions on concurrent orders from same email could create duplicates | Low probability but possible under load |

### Frontend & Theme

| Issue | Details | Impact |
|---|---|---|
| **No test or lint tooling** | `package.json` has no test, lint, or type-check scripts | Code quality relies entirely on manual review |
| **jQuery dependency** | Global jQuery 3.7 via ProvidePlugin — used throughout the codebase | Limits ability to adopt modern JS frameworks; larger bundle size |
| **Single CSS/JS bundle** | One `main.min.css` and one `main.min.js` for entire site | All page-specific styles/scripts loaded on every page (no code splitting) |
| **No cache busting** | Compiled assets don't include content hash in filenames | Browser caching may serve stale assets after deploys |

### Monitoring & Backup

| Issue | Details | Impact |
|---|---|---|
| **No upload backup sent to Telegram** | `backup.sh` only sends DB dump to Telegram — upload archive stays local only | If server disk fails, upload backups are lost |
| **No off-site backup** | Backups stored only on the same server (`/backups/wordpress/`) | Single point of failure — server loss = data loss |
| **Monitoring is alert-only** | `mornitor.sh` only alerts when thresholds exceeded — no historical metrics or dashboards | No trending, no capacity planning, no visibility into gradual degradation |
| **No container health monitoring** | Docker container health check exists (HTTP GET :80) but no alerting if it fails | Container could be unhealthy without notification |
| **No error log monitoring** | PHP errors, WooCommerce logs, and Apache logs are not monitored or aggregated | Application errors go unnoticed until users report them |

### Security

| Issue | Details | Impact |
|---|---|---|
| **No WAF or rate limiting** | No Web Application Firewall or brute-force protection configured | Relies on XML-RPC being disabled and obscurity |
| **No security plugin** | No Wordfence, Sucuri, or similar security plugin installed | No malware scanning, no login attempt limiting, no file integrity monitoring |
| **Admin credentials in ACF options** | HubSpot token, Facebook CAPI token, DingTalk secret stored as ACF option fields | Accessible to any WordPress admin user — no encryption at rest |
