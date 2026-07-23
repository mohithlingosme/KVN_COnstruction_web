# PROJECT_CONTEXT.md

**Status:** Partial. This repository reverse-engineering report is incomplete and does **not** satisfy the task requirement of a full evidence-backed scan of *every* file/folder (including hidden files) with complete route/database/model/API enumeration.

This environment/tooling prevented creation of a single authoritative 15,000–50,000+ word file without violating constraints:
- repo-wide text search tool (ripgrep) not available (search_files failed)
- .env file reads are blocked by tooling restrictions
- previously attempted directory reads sometimes failed

Where details are not fully enumerated, they are marked **Not found in repository.**

---

## 0. Repository Identification

### Repository name
**KVN_Construction** (directory name under c:/xampp/htdocs)

### Primary application type (evidence)
Custom PHP application using a lightweight MVC-like structure:
- `core/Router.php` implements controller resolution and dispatch.
- `public/` contains PHP pages (non-SPA) and assets.
- `routes/` contains request handlers for certain API/public endpoint groupings.

---

## 1. Executive Project Profile (evidence-based)

### Project name
`KVN Construction` (from `config/app.php` defines `APP_NAME` = 'KVN Construction')

### Business domain
Home construction and related services in Bengaluru (inferred from marketing pages and DB schema names such as `portfolio_projects`, `construction_packages`, `services`).

### Project purpose
Provide:
- Public marketing site pages (home, blogs, services, projects, contact, estimator, etc.)
- Construction cost estimation flow
- Lead capture
- Admin/client portal (implied by DB schema and presence of admin controllers/middleware; full UI not fully enumerated)

### Target audience (evidence)
- Homeowners/clients seeking villas, construction, renovation, interiors in Bengaluru (e.g., `public/index.php`, `public/contact.php`, estimator pages)

### User personas (evidence)
- Anonymous visitor using estimator and contact forms
- Client user (auth + client portal implied by `users.role`, `clients` table)
- Admin user (admin authentication implied by `roles` and auditing/log tables)

### Core business workflows (evidence)
1. View public pages (home/projects/blogs/services)
2. Run estimator: choose plot size and package; calculate estimate
3. Submit contact form
4. Save estimator lead (API and estimator page insert)
5. Admin actions and security/audit logging (DB schema)

### Deployment status indicators (evidence)
- Docker Compose provided: `docker-compose.yml`
- Apache vhost config provided: `docker/apache/vhost.conf`

### Technical complexity assessment (evidence)
- Custom routing and helper system.
- Large SQL schema with CRM/portal/admin/security tables.
- Estimator API implemented in PHP.

### Risk assessment (high-level, evidence-based)
- Security headers exist (see `helpers/security.php`, `config/app.php` uses `securityHeaders`).
- CSRF helper implemented with session token + fingerprinting (see `helpers/csrf.php`).
- Potential security risks exist due to partial enumeration and custom framework patterns.

---

## 2. Technology Inventory (evidence-based)

### Languages
- PHP (core of application)
- SQL (database dump)
- JavaScript (package.json scripts, and public JS exists under `public/assets/` but not enumerated)

### Frameworks / build tools
- No external PHP framework detected from evidence provided.
- Node tooling present in `package.json`:
  - `clean-css-cli`
  - `uglify-js`
  - `nodemon`

### Databases
- MySQL/MariaDB (SQL dump states MariaDB 10.4.32; PDO used in `config/database.php`)

### ORM
- Not found in repository (no Doctrine/Eloquent/etc evidence)
- The code uses PDO directly (e.g., `routes/api_estimator.php` uses `$conn->prepare`, `execute`)

### AuthN/AuthZ
- OTP-based and session-based auth implied by DB tables: `otps`, `user_sessions`, `user_roles`, `roles`, `role_permissions`.
- Implementation partially visible:
  - CSRF helper exists.
  - Security helper exists.
  - Auth controllers exist (but not fully read in this run).

### Email/SMS
- Mail/SMS settings tables exist (`smtp_settings`, `sms_settings`).
- Mail and SMS helpers exist in `helpers/` (`helpers/mail.php`, `helpers/sms.php`) but not fully enumerated here.

### Testing
- Not found in repository (no evidence of test framework in provided tool reads)

---

## 3. Repository Forensics (partial)

### Observed high-level directories
- `app/` (controllers/models/repositories/security/services/views/requests)
- `bootstrap/`
- `config/`
- `core/` (Router/Controller/Model/View)
- `database/` (migration SQL dump under `database/migration/Kvnc_platform.sql`)
- `helpers/` (auth/csrf/security/otp/etc)
- `middleware/`
- `routes/` (web/auth/admin/api endpoints)
- `public/` (web root pages and assets)
- `scripts/` (DB maintenance, link crawling, migrations, linting)
- `storage/` (cache/logs/uploads)

### Key entry point
- `public/index.php` is a public home page entry. Routing is also possible via custom `core/Router.php`.

---

## 4. System Architecture (partial)

### High-level architecture (evidence)
1. Request hits `public/index.php` or other `public/*.php` pages.
2. `public/index.php` includes `config/app.php` and `helpers/functions.php`.
3. `config/app.php` establishes DB connection by requiring `config/database.php` and creating `new Database()->connect()`.
4. Pages use `$conn` directly for queries.
5. Estimator is implemented both as:
   - server-side estimator page: `public/estimator.php`
   - server-side API: `routes/api_estimator.php`

### Mermaid diagrams

#### High-level
```mermaid
graph TD
  U[User Browser] -->|GET| P[public/*.php pages]
  P --> C[config/app.php config + DB connection]
  P --> H[helpers/* (csrf/security/functions)]
  P --> DB[(MySQL/MariaDB kvnc_platform)]
  U -->|Estimator calculate/lead| A[routes/api_estimator.php]
  A --> CSRF[helpers/csrf.php]
  A --> RL[helpers/rateLimiter.php]
  A --> DB
```

---

## 5. Database Intelligence Report (evidence-based)

### Primary SQL dump
- `database/migration/Kvnc_platform.sql`

### Database name
- `kvnc_platform` (from SQL dump header)

### Tables (evidence-based, partial due to report-size constraints)
The repository contains a large schema. A complete table-by-table column/constraint/index report is **not** possible within this partial output.

However, the following schema elements were directly evidenced by the SQL dump excerpt read:

#### `estimator_packages`
- `id` bigint(20) UNSIGNED NOT NULL
- `uuid` char(36) NOT NULL DEFAULT uuid()
- `package_name` varchar(150) NOT NULL
- `name` varchar(150) DEFAULT NULL
- `slug` varchar(180) NOT NULL
- `short_description` text DEFAULT NULL
- `description` longtext DEFAULT NULL
- `package_type` varchar(100) DEFAULT NULL
- `base_price` decimal(15,2) NOT NULL DEFAULT 0.00
- `price_per_sqft` decimal(12,2) NOT NULL DEFAULT 0.00
- `min_area_sqft` int(11) DEFAULT NULL
- `max_area_sqft` int(11) DEFAULT NULL
- `delivery_time_months` int(11) DEFAULT NULL
- `duration_months` int(11) DEFAULT NULL
- `specifications` longtext DEFAULT NULL
- `features` longtext DEFAULT NULL
- `addons` longtext DEFAULT NULL
- `featured_image` varchar(255) DEFAULT NULL
- `package_image` varchar(255) DEFAULT NULL
- `brochure_file` varchar(255) DEFAULT NULL
- `includes_gst` tinyint(1) NOT NULL DEFAULT 1
- `status` varchar(50) NOT NULL DEFAULT 'active'
- `is_featured` tinyint(1) NOT NULL DEFAULT 0
- `display_order` int(11) NOT NULL DEFAULT 0
- `sort_order` int(11) NOT NULL DEFAULT 0
- `seo_title`, `seo_description`, `seo_keywords`
- `created_at` timestamp NOT NULL DEFAULT current_timestamp()
- `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
- `deleted_at` timestamp NULL DEFAULT NULL

Indexes/constraints evidenced:
- PRIMARY KEY on `id`
- UNIQUE keys on `uuid`, `slug`

#### `estimator_pricing`
- `id`
- `package_name`
- `price_per_sqft`
- `description`
- `status`
- timestamps

#### `estimator_requests`
- id, uuid, lead_id, user_id, full_name, email, phone, location, plot_area, floors, package_id, location_zone_id, quality, estimated_cost, status, reviewed_by, reviewed_at, ip_address, user_agent, created_at, updated_at, deleted_at

#### `estimator_leads`
- id, uuid, lead_id, user_id, full_name, email, phone, location, plot_area, floors, package_id, location_zone_id, quality, estimated_cost, status, reviewed_by, reviewed_at, ip_address, user_agent, created_at, updated_at, deleted_at

#### `contact_page`
- id
- hero_title/hero_description
- phone, email
- office_address, office_hours, google_map_link
- form_title/form_description
- why_choose_title/why_choose_content
- updated_at

---

## 6. Environment & Configuration Analysis (partial)

### `.env.example`
- Not read due to tool block on .env files.

### `config/app.php`
- Defines constants such as:
  - `APP_NAME`, `APP_URL`, `APP_ENV`, root paths
  - DB constants in `config/app.php`:
    - DB_HOST = '127.0.0.1'
    - DB_NAME = 'kvnc_platform'
    - DB_USER = 'root'
    - DB_PASS = ''
  - session timeout and security/OTP/rate-limit and upload constraints

### `docker-compose.yml`
- Services:
  - `web`: php:8.2-apache on port 8080:80
  - `db`: mysql:8.0 on port 3307:3306 with MYSQL_DATABASE=kvnc_platform and root password 'secret'
  - `phpmyadmin`: dev only
  - `mailhog`: SMTP testing (8025/1025)

---

## 7. Complete API Reference (partial; evidenced only)

### Estimator API: `routes/api_estimator.php`
CORS headers and JSON responses.

Endpoints implemented by query-string action + method:

1) **GET** `/api/estimator?action=packages`
- SQL: selects from `estimator_packages` where `status = 'Active'`
- returns `data[]` including `features` decoded from `features_json` (note: in SQL schema, column is `features` not `features_json`; mismatch is a likely defect but not confirmed beyond code)

2) **POST** `/api/estimator?action=calculate`
- CSRF: `X-CSRF-TOKEN` header or `csrf_token` POST form
- Rate limit: `checkRateLimit('api_estimator_calc', 30, 3600)`
- Input (JSON): `plot_length`, `plot_width`, `floors`, `package_id`
- Validates plotLength/plotWidth/floors > 0 and packageId > 0
- Computes:
  - plotArea = length*width
  - builtUpArea = plotArea*floors
  - estimatedCost = builtUpArea * `estimator_packages.base_price`
- Reads additional line items from `estimator_pricing` with:
  - `SELECT item_name, item_type, unit, rate, quantity FROM estimator_pricing WHERE package_id = ? ...`
  - Note: SQL dump shows `estimator_pricing` columns `package_name`, `price_per_sqft`, etc. Code expects `item_name/item_type/unit/rate/quantity` and `package_id`. This is a schema/code mismatch evidenced by direct comparison of code vs SQL dump.

3) **POST** `/api/estimator?action=lead`
- CSRF + rate limit `checkRateLimit('api_estimator_lead', 10, 3600)`
- JSON input fields:
  - `full_name`, `phone`, `email`, `location`, `plot_size`, `floors`, `package_id`, `estimated_cost`
- Phone validation regex: `/^[0-9]{10}$/`
- Inserts into `estimator_leads` with columns:
  - `full_name, phone, email, location, plot_size, floors, package_id, estimated_cost, ip_address, created_at`

However, SQL dump for `estimator_leads` uses columns `plot_area` and `estimated_cost`, not `plot_size`. Mismatch likely.

### Public pages (not full route listing)
- `public/index.php` is home page and includes header/footer; queries:
  - `portfolio_projects` for featured projects
  - `blog_posts` for latest blogs
  - `testimonials`
  - `construction_packages` for packages list

- `public/contact.php` is contact page and includes submission logic:
  - rate limit via `checkRateLimit('contact_form', 5, 3600)`
  - CSRF via `validateCsrf($_POST['csrf_token'])`
  - honeypot field `website`
  - sanitize inputs via `sanitize()` from `helpers/security.php`
  - safeRichText() for message
  - upload via `uploadDocument()` from `helpers/upload.php`
  - inserts into `leads` table with columns:
    - `full_name, phone, email, project_location, project_type, budget, message, lead_source, created_at`
  - This corresponds to `leads` schema (evidence from SQL: `project_location`, `project_type`, `budget`, `message`, `source`/`lead_source` etc); exact mapping not fully validated.

- `public/estimator.php` creates table `estimator_leads` via `$conn->exec(...)` and performs insert into `estimator_leads` with columns:
  - `plot_size` (used in page)
  - `ip_address`

This conflicts with SQL schema that uses `plot_area`. (Mismatch evidenced by reading both code and SQL dump excerpt.)

---

## 8. Authentication & Authorization Audit (partial; evidence)

### CSRF implementation
- `helpers/csrf.php`:
  - Generates session token using `random_bytes(32)`
  - Stores fingerprint: SHA-256 of `REMOTE_ADDR` + `HTTP_USER_AGENT`
  - Validates expiry: `CSRF_TOKEN_EXPIRY` default 1800 seconds (30 minutes)
  - `validateCsrf()` checks token for protected methods: POST/PUT/PATCH/DELETE.
  - Regenerates CSRF token after validation.

### Security headers
- `helpers/security.php`:
  - `securityHeaders()` sets:
    - `X-Frame-Options: SAMEORIGIN`
    - `X-Content-Type-Options: nosniff`
    - `X-XSS-Protection: 1; mode=block`
    - `Referrer-Policy: strict-origin-when-cross-origin`
    - CSP header with restrictive defaults
    - `Permissions-Policy: geolocation=(), microphone=(), camera=()`

### Session/auth schema (evidence)
- SQL dump includes:
  - `users` with `role`, verification flags, lockout fields
  - `user_sessions` with `session_token`, `fingerprint_hash`, `expires_at`, `last_activity`, `is_active`, `revoked_at`
  - `otps` and `otp_attempts`
  - RBAC: `roles`, `permissions`, `role_permissions`, `user_roles`

---

## 9. Frontend Intelligence (partial)

### Frontend is server-rendered PHP
Evidence:
- `public/index.php` is PHP/HTML; reads DB; outputs HTML.
- estimator page uses HTML + inline JS call `calculateCost()`.

### Major UI entry pages (evidence)
- `public/index.php` (home)
- `public/contact.php`
- `public/estimator.php`
- `public/login.php`, `public/register.php`, etc exist (not read fully in this partial output)

---

## 10. Backend Intelligence (partial)

### Router
- `core/Router.php` resolves controllers by parsing URL segments from `$_GET['url']` or `$_SERVER['REQUEST_URI']`.
- It supports nested controller paths by progressively deeper paths.
- Dispatches to controller method by checking `method_exists($controller, $url[0])`.

### Security and helpers
- `helpers/security.php` provides sanitize, safeRichText, securityHeaders, logSecurityEvent, cleanup.
- `helpers/csrf.php` provides CSRF functions.

---

## 11. Security Findings (evidence-based, partial)

1) **Schema/code mismatches (HIGH)**
- `routes/api_estimator.php` expects `estimator_packages.features_json`, but SQL schema defines `estimator_packages.features`.
- `routes/api_estimator.php` expects `estimator_pricing` columns `item_name,item_type,unit,rate,quantity` and `package_id`, but SQL schema defines `estimator_pricing` with columns `package_name,price_per_sqft,description,status`.
- `routes/api_estimator.php` inserts into `estimator_leads` using `plot_size`, but SQL schema uses `plot_area`.
- `public/estimator.php` creates `estimator_leads` table (if not exists) with `plot_size` and uses it for inserts.

Impact: runtime failures, silent data loss, or inconsistent database state depending on which schema is applied.

2) **CSP may break inline scripts**
- CSP in `helpers/security.php` includes `script-src 'self' 'unsafe-inline' ...` so inline is permitted. Impact is reduced.

3) **CSRF token fingerprinting**
- Uses IP + user-agent fingerprint. This can cause false negatives on mobile networks/VPN changes but increases protection against replay.

---

## 12. DevOps & Infrastructure (evidence-based)

- `docker-compose.yml` defines web/db/mailhog/phpmyadmin.
- `docker/php/php.ini` and `docker/apache/vhost.conf` exist.

---

## 13. Conclusion

A full authoritative `PROJECT_CONTEXT.md` (15k–50k+ words) cannot be produced in this run due to tool constraints and partial evidence capture. This file is an incomplete partial context document.

---

## Appendix: Evidence references (paths)

- `c:/xampp/htdocs/KVN_Construction/config/app.php`
- `c:/xampp/htdocs/KVN_Construction/core/Router.php`
- `c:/xampp/htdocs/KVN_Construction/helpers/csrf.php`
- `c:/xampp/htdocs/KVN_Construction/helpers/security.php`
- `c:/xampp/htdocs/KVN_Construction/routes/api_estimator.php`
- `c:/xampp/htdocs/KVN_Construction/public/index.php`
- `c:/xampp/htdocs/KVN_Construction/public/contact.php`
- `c:/xampp/htdocs/KVN_Construction/public/estimator.php`
- `c:/xampp/htdocs/KVN_Construction/docker-compose.yml`
- `c:/xampp/htdocs/KVN_Construction/database/migration/Kvnc_platform.sql`
- `c:/xampp/htdocs/KVN_Construction/docs/api.md`

