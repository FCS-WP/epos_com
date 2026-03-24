# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

EPOS.com — a WordPress/WooCommerce e-commerce platform with multi-region support (EPOS MY), custom plugin architecture, Docker containerization, and CI/CD deployments to SFTP (production) and DigitalOcean (Docker).

## Build Commands

```bash
npm install          # Install dependencies
npm run dev          # Development watch mode (webpack + BrowserSync live reload)
npm run build        # Development build
npm run dist         # Production build (minified)
```

No test or lint commands are configured. Node.js v18.18.2+ required.

## Deployment

- **Production SFTP:** Triggered by git tags matching `production-*` (GitHub Actions → SFTP deploy)
- **DigitalOcean Docker:** Triggered by git tags matching `deploy-*` (GitHub Actions → SSH + docker compose)
- **Auto PR:** Pushing to `update-*` branches auto-creates a PR to `master` with Telegram notification

## Architecture

### WordPress Theme (zippy-child)

The active child theme is `src/wp-content/themes/zippy-child/`. Parent theme is `zippy` (Flatsome-based).

- `functions.php` loads all includes from `/includes/` directory
- Include files follow `shin_*.php` naming (helper, cpt, custom, optimise, admin, block)
- Multi-region routing: `header.php` conditionally loads `header-my.php` or `header-360.php` based on `REQUEST_URI`
- WooCommerce template overrides live in `zippy-child/woocommerce/`
- SCSS/JS source files are in `zippy-child/assets/src/`, compiled output goes to `zippy-child/assets/dist/`

### Custom Plugin (zippy-core)

Located at `src/wp-content/plugins/zippy-core/`. Follows MVC pattern with modular architecture.

- `Core_Module` abstract class — all modules extend this
- Singleton pattern for route/module initialization
- Modules auto-loaded from `src/modules/`: orders, products, settings, shipping, postal_code
- Each module follows: `controllers/`, `routes/`, `services/`, `models/`, `templates/`
- REST API namespace: `zippy-core/v2`

### Payment Plugin (zippy-pay)

Located at `src/wp-content/plugins/zippy-pay/`. Handles payment gateway integrations (Alipay, 2C2P).

### Webpack Entry Points

Webpack compiles SCSS and JS from the child theme's `assets/src/` directory. Config uses:
- jQuery provided globally via `ProvidePlugin`
- BrowserSync proxied to `PROJECT_HOST` from `.env`
- CSS extracted via `MiniCssExtractPlugin`

### Key Integrations

- **Tracking:** Facebook pixel (`fb_tracking/`), GTM (`gtm_tracking/`), GA4
- **CRM:** HubSpot (`hubspot_intergration/`)
- **Geolocation:** IP-based popup system (`shin_popup_geoip.php`)
- **Animations:** GSAP (`epos_gsap.php`)
- **Recruitment:** Workable integration (`workable/`)

## Docker Setup

```bash
# Copy and configure environment
cp .env.sample .env
# Edit .env with database credentials and project settings

docker compose up -d --build
```

Uses `wordpress:6.9` image. Connects to external `webnet` network and shared MySQL service. The `.htaccess` disables XML-RPC for security.

## Branch Strategy

- `master` — main integration branch (PR target)
- `production` — current production state
- `update-*` — feature branches (auto-create PRs to master)
- Tags `production-*` trigger SFTP deploy; tags `deploy-*` trigger Docker deploy
