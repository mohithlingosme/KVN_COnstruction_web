# Project Onboarding - KVN Construction Platform

This document helps a new developer set up the project locally and understand the main moving parts.

---

## 1) What this project is
KVN Construction Platform is a PHP web application (custom MVC-like structure) with:
- Public frontend (marketing pages + lead/project inquiry flows)
- Client area (client authentication and project-related actions)
- Admin console (admin authentication + management)
- OTP-based authentication and rate limiting
- Integrations for Mail/SMS providers (configurable)

---

## 2) Local setup (XAMPP / Windows)

### Prerequisites
- PHP (compatible with the project; verify your XAMPP PHP version)
- Apache + PHP enabled
- MySQL/MariaDB

Recommended PHP extensions:
- `pdo_mysql`
- `mysqli`
- `mbstring`

### Step A: Point Apache to the correct web root
This repository should be deployed under:
- Project root: `c:/xampp/htdocs/KVN_Construction`
- Public web root (important): `c:/xampp/htdocs/KVN_Construction/public`

### Step B: Configure APP_URL
The app uses constants from `config/app.php`.
Default currently is:
- `APP_URL = http://localhost/kvn_construction/public`

If your virtual host/path differs, update `APP_URL`.

### Step C: Import the database schema
A SQL dump exists at:
- `database/migration/Kvnc_platform.sql`

Import it into a database named:
- `kvnc_platform`

### Step D: Verify DB credentials
The PDO database connector is implemented by the `Database` class and configured in:
- `config/database.php`

Update:
- host
- db_name
- username
- password

---

## 3) First-run verification checklist (what must work)
Before you start developing, verify the end-to-end user journeys. This helps catch missing/unfinished routes, controllers, views, DB writes, and broken forms.

### A) Pages & routes (quick smoke)
1. Start MySQL and Apache.
2. Open the public landing page:
   - `http://localhost/kvn_construction/public/`
3. Verify these commonly linked pages return **200 (not 404)**:
   - Home/landing
   - Projects listing and details (`public/projects.php`, `public/project-details.php`)
   - Blog listing/details (`public/blogs.php`, `public/blog-details.php`)
   - Contact (`public/contact.php`)
   - Estimator (`public/estimator.php`)
   - Login/OTP verification pages (e.g. `public/login.php`, `public/verify-phone-otp.php`)

If any page returns **404**, check:
- `routes/*.php` for the matching route
- `public/*.php` (some pages are standalone includes)
- middleware guards (`middleware/auth.php`, `middleware/admin-auth.php`, etc.)

### B) Authentication flows (OTP + sessions)
Verify these:
- OTP login: entering phone/login -> OTP -> successful session
- OTP resend and OTP max-attempts enforcement

Where to look if OTP login fails:
- `helpers/otp.php`, `helpers/rateLimiter.php`
- `app/services/OTPService.php`, `app/security/OTPManager.php`
- DB writes to `otp`-related tables (from `Kvnc_platform.sql`)

If login loops or fails:
- check `config/app.php` OTP constants
- confirm provider config in `config/mail/smtp.php` and/or `config/sms/twilio.php`

### C) Leads & quotations (public -> DB persistence)
Verify submission forms:
- Lead submission (name/phone/email + requirements) saves correctly
- Quotation submission (project/quotation creation flow) saves correctly

If submissions don’t persist:
- inspect controller handling: `app/controllers/*` and their `POST` handlers
- inspect repositories: `app/repositories/*`
- confirm relevant database tables exist and match the schema in `database/migration/Kvnc_platform.sql`

### D) Payments (whether it works)
Verify at least one payment flow end-to-end:
- payment initiation
- payment record creation/update
- payment status reflected in UI (project/quotation view)

If payments fail:
- check `app/services/PaymentService.php`
- check `app/models/Payment.php`
- check config for payment provider (wherever used by PaymentService)

### E) Project tracking (whether it works)
Verify:
- project creation/registration
- project status updates appear in the client area/admin area

If tracking doesn’t update:
- check `app/services/ProjectService.php`
- check `app/repositories/ProjectRepository.php`
- check views under `app/views/client/*` and `app/views/admin/*`

### F) Admin features (what to consider “unfinished”)
Verify admin console core features:
- admin login works
- CRUD for clients/projects/quotations/blogs (as applicable)
- audit log appears (if enabled)

Where to look:
- controllers: `app/controllers/admin/*`
- views: `app/views/admin/*`
- middleware: `middleware/admin-auth.php`, `middleware/admin.php`

---

### C) If you encounter issues, log where they break
When a feature fails, capture:
- which URL failed (route/page)
- whether it fails as 404/500/redirect
- which form field submission is affected
- relevant DB table(s) (from schema)
- relevant controller class (from the route)

Then use this quick search guide:
- route -> `routes/*.php`
- controller -> `app/controllers/**`
- DB -> `app/repositories/**` and `app/models/**`
- views -> `app/views/**`



---

## 4) Codebase map (how to navigate)

### Entry points
- `public/index.php` is the main entry/front controller for the app.

### Routing
- Route definitions live under `routes/`:
  - `routes/web.php`
  - `routes/auth.php`
  - `routes/client.php`
  - `routes/admin.php`
- Route provider configuration is in `routes/RouteServiceProvider.php`.

### Controllers
- `app/controllers/` - request handlers
  - `AuthController.php`
  - `AdminAuthController.php`
  - Subfolders for `admin/`, `auth/`, `client/`

### Domain models
- `app/models/` - data/domain entities
  - Examples: `Project.php`, `Quotation.php`, `Client.php`, `Lead.php`, `Payment.php`, etc.

### Repositories
- `app/repositories/` - wrappers around queries
  - Examples: `ProjectRepository.php`, `QuotationRepository.php`, etc.

### Services
- `app/services/` - business logic
  - `AuthService.php`, `OTPService.php`, `MailService.php`, `SMSService.php`, etc.

### Security & helpers
- `app/security/` - security primitives:
  - `Authenticator.php`, `Authorization.php`, `OTPManager.php`, `PasswordManager.php`, `SessionManager.php`
- `helpers/` - global helpers used throughout:
  - `otp.php`, `security.php`, `csrf.php`, `session.php`, `mail.php`, `sms.php`, `rateLimiter.php`, etc.

### Middleware
- `middleware/` - route guards:
  - `admin-auth.php`, `auth.php`, `client.php`, `guest.php`, etc.

### Views
- `app/views/` - PHP templates
  - `app/views/layouts/`
  - `app/views/public/`
  - `app/views/admin/`
  - `app/views/client/`

---

## 5) Configuration references (where to change what)

### App-level constants
- `config/app.php`
  - APP_URL
  - APP_ENV
  - session timeouts/names
  - OTP defaults (expiry/attempts/limits)
  - upload limits

### Database connector
- `config/database.php`
  - PDO connection parameters

### Mail/SMS providers
- `config/mail/smtp.php`
- `config/sms/twilio.php`

---

## 6) Typical local workflow

### Add a feature
1. Add/extend a route in `routes/`.
2. Implement controller logic in `app/controllers/`.
3. Put query logic in `app/repositories/` or models.
4. Put business logic in `app/services/`.
5. Render templates from `app/views/`.

### Keep things safe
- When touching auth/OTP/rate limiter code:
  - check `helpers/security.php` and `helpers/rateLimiter.php`
  - check `app/security/*`

---

## 7) Notes for contributors
- The project uses custom routing/bootstrapping (not composer-based Laravel).
- Prefer using existing helpers/utilities in `helpers/` rather than duplicating logic.
- Follow existing naming conventions for controllers/services/models.

---

## 8) Quick links
- Configuration: `config/app.php`, `config/database.php`
- Routing: `routes/`
- Core logic: `app/controllers/`, `app/services/`, `app/security/`
- Views/templates: `app/views/`
- Public entry: `public/index.php`

