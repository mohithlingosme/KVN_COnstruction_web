# Repository Audit Report

Audit date: 2026-07-23

## Architecture

This is a PHP 8.2 server-rendered construction-management application. `public/` is the web root; public and authenticated pages are mostly standalone PHP entry points. Shared configuration, helpers, security, models, controllers, and middleware live outside the web root. Docker runs Apache with `public/` as `DocumentRoot`; MySQL 8 is used for persistence. Node tooling only minifies CSS and JavaScript.

## Repository Tree

```
.
├── app/                 # active controllers, models, security, layouts
├── config/              # application and database configuration
├── core/                # Controller, Model, Router, View base classes
├── database/migration/  # canonical schema and additive migrations
├── docker/              # Apache and PHP runtime configuration
├── helpers/             # auth, CSRF, session, upload, security helpers
├── middleware/          # authentication/role guards
├── public/              # Apache document root, public/admin/client pages/assets
├── tests/               # lightweight PHP unit/API test suite
├── uploads/             # runtime upload storage (not source)
├── Dockerfile
├── docker-compose.yml
├── package.json
└── README.md
```

## Inventory

- 269 repository files excluding `.git` and `node_modules`; 216 are PHP files after cleanup.
- Active application flow uses direct public PHP routes and shared helpers. The legacy MVC router is currently not instantiated by a public entry point.
- Existing uncommitted changes pre-dated this audit and were preserved.

## Duplicate and Unused Files

- Removed 59 confirmed-unused, tracked, zero-byte scaffold files from `app/`, `bootstrap/`, `config/{mail,security,sms}/`, and `routes/`. They had no references in non-test PHP files and contained no implementation.
- Identical image copies remain under `public/assets/images/`, `public/blogs/`, and `public/portfolio/`; these may be deliberate public URL compatibility assets, so they were retained.
- Minified CSS/JS output is build artefact, not duplicate source.

## Imports, Dependencies, and Circularity

- PHP syntax check passed for every PHP file.
- No missing static include target was found by source inspection; dynamic includes require runtime coverage.
- No Composer or Python dependency manifests exist. `npm audit --omit=dev` reported zero vulnerabilities.
- No circular dependency was detected in the directly loaded bootstrap/helper paths.

## Routes and HTTP Findings

- Fixed the legacy nested-controller router bug: it resolved a nested controller file then discarded that path and tried loading it from the controller root, causing 404s. It now keeps and loads the resolved path.
- Public routes are file-backed with extensionless rewriting in `public/.htaccess`; Docker correctly exposes `public/` as the document root.
- The root project entry point redirects to `public/` for non-Docker/XAMPP root access.

## Database and API Findings

- **Fixed:** Docker previously mounted empty `database/schema/` into MySQL initialization. Fresh containers consequently had no tables and DB-backed pages/API endpoints could return 500. `docker-compose.yml` now mounts the canonical schema, then index and consolidation migrations in order.
- Estimator API responses are covered for success, validation, CSRF, throttling, 404, and database-facing behavior by the test suite.
- The initialization scripts only run on a newly created MySQL volume; existing deployments need the normal migration process, not volume deletion.

## Verification

- `php -l` on all PHP files: passed.
- `php tests/run.php`: 26 passed, 0 failed.
- `npm run build`: passed.
- `npm audit --omit=dev`: 0 vulnerabilities.
- `docker compose config --quiet`: passed (the obsolete Compose `version` field was removed).

## Limitations / Follow-up

- Full browser and live-MySQL end-to-end testing needs an initialized database instance and representative non-production data.
- The pre-existing dirty worktree should be reviewed separately before release; it was intentionally not folded into this audit commit.
