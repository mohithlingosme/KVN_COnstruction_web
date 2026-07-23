# Pending Tasks for KVN Construction Platform

This document details all the remaining work required to bring the KVN Construction Platform to a production-ready and fully functional state.

---

## 1. Test Suite Stability & Execution
Currently, the test suite (`php tests/run.php`) fails to execute because of function conflicts between the test bootstrapping and production files.

- [ ] **Fix `destroySession()` Redeclaration Error:** 
  - Wrap the `destroySession` function in [helpers/session.php](file:///c:/xampp/htdocs/KVN_Construction/helpers/session.php#L664-L749) in an `if (!function_exists('destroySession'))` block. This prevents PHP from throwing a Fatal Error when [tests/bootstrap.php](file:///c:/xampp/htdocs/KVN_Construction/tests/bootstrap.php#L102-L108) stubs it for isolation.
- [ ] **Address Other Potential Redeclaration Conflicts:**
  - Audit and wrap other external-facing stubbed functions such as `sendOtpSms()` and `sendOtpEmail()` in [helpers/sms.php](file:///c:/xampp/htdocs/KVN_Construction/helpers/sms.php) and [helpers/mail.php](file:///c:/xampp/htdocs/KVN_Construction/helpers/mail.php) in similar conditional checks to ensure safety if OTP helpers are lazy-loaded during a test.
- [ ] **Run Test Suite:**
  - Run `php tests/run.php` and verify that all unit/API tests (OTP authentication, dashboard queries, and API Estimator endpoints) pass with zero failures.

---

## 2. Database & Controller Schema Synchronization
There are structural discrepancies between what the controller code expects and what is defined in the database schema. These mismatch issues must be synchronized to prevent runtime query failures or silent data loss.

- [ ] **Align `estimator_packages` Columns:**
  - Sync [routes/api_estimator.php](file:///c:/xampp/htdocs/KVN_Construction/routes/api_estimator.php) which queries `features_json` with the database table `estimator_packages` which only has a `features` column.
- [ ] **Align `estimator_pricing` Schema:**
  - Fix the mismatch where [routes/api_estimator.php](file:///c:/xampp/htdocs/KVN_Construction/routes/api_estimator.php) queries `estimator_pricing` expecting columns `item_name`, `item_type`, `unit`, `rate`, `quantity`, and `package_id`, but the SQL dump defines `estimator_pricing` with `package_name`, `price_per_sqft`, `description`, `status`.
- [ ] **Sync `plot_size` vs `plot_area` on Estimator Leads:**
  - Fix discrepancies where [routes/api_estimator.php](file:///c:/xampp/htdocs/KVN_Construction/routes/api_estimator.php) inserts into `estimator_leads` using `plot_size`, but the SQL schema defines this field as `plot_area`.
  - Fix [public/estimator.php](file:///c:/xampp/htdocs/KVN_Construction/public/estimator.php) which attempts to create/insert into the `estimator_leads` table using `plot_size`.
- [ ] **Apply Pending Migrations:**
  - Run indices migration: `database/migration/index_migration.sql`
  - Run consolidated duplicate tables migration: `database/migration/consolidate_duplicate_tables.sql`

---

## 3. Security Remediation (Medium Severity Heuristics)
The security audit flagged 95 potential heuristic matches that should be addressed before deploying to a public-facing server.

- [ ] **Prepared Statements Everywhere:**
  - Refactor files flagged in the security audit (such as `app\controllers\admin\AdminController.php`, `app\models\Lead.php`, and `core\Model.php`) to ensure they use parameterized prepared queries instead of direct string interpolations.
- [ ] **Form CSRF Protection:**
  - Verify that all administrative and client forms in the admin (`public/admin/...`) and client (`public/client/...`) areas have CSRF tokens appended, and validate them on submission using `validateCsrf()`.
- [ ] **Add deployment `Dockerfile`:**
  - Create the missing container setup file `Dockerfile` in the root directory to match the `docker-compose.yml` configurations.

---

## 4. Frontend Verification & Complete Route Audit
A complete manual validation is necessary to ensure the user interface is visual and functional on all layouts.

- [ ] **Fix Resource Path Broken Links:**
  - Fix all 404 images across the public marketing pages.
  - Correct any broken favicon, CSS, and JS asset routes.
  - Create or add the missing `favicon.ico` under the `public/` directory.
- [ ] **Perform Responsive Design Check:**
  - Test layouts on mobile, tablet, and desktop viewports to ensure proper responsiveness (containers, navigation bars, pricing calculators).
- [ ] **Manual Route Execution Check:**
  - Verify that each page loads without throwing PHP errors or server crashes:
    * **Public Pages:** Homepage (`index.php`), About (`about-us.php`), Services (`services.php`), Projects (`projects.php`/`project-details.php`), Estimator (`estimator.php`), Blogs (`blogs.php`/`blog-details.php`), Testimonials, Gallery, FAQ, Contact (`contact.php`), Privacy, and Terms.
    * **Admin Area:** Login, Dashboard, User Management, Lead pipeline, Estimator packages/pricing/requests.
    * **Client Portal:** Client Dashboard, Client Projects, Payments/Invoices, Support tickets.

---

## 5. SEO Optimization
A significant portion of pages lack proper search engine indicators, which violates semantic requirements.

- [ ] **Title Tags:**
  - Add descriptive, relevant `<title>` tags to the 42 pages currently missing them (including the homepage, services, projects, contact, and FAQ pages).
- [ ] **Meta Descriptions, Canonical Links, and OpenGraph Tags:**
  - Implement unique meta descriptions and canonical links for all 149 scanned public/marketing pages.
  - Add standard OpenGraph (`og:title`, `og:description`, `og:image`, `og:url`) meta tags to projects, blogs, and landing pages for better social media link styling.
- [ ] **Heading Structure:**
  - Standardize headers so there is exactly one `<h1>` tag per page, followed by a logical hierarchy of `<h2>` and `<h3>` tags.
- [ ] **Structured Data (Schema.org):**
  - Implement JSON-LD structured data on service pages, portfolio projects, and blogs to enable Google search rich snippets.

---

## 6. Production Environment Configurations
Ensure configuration parameters are set up securely for production environments.

- [ ] **Uncomment HTTPS Redirect in `.htaccess`:**
  - Uncomment the force-SSL redirect rules in `.htaccess` once the domain has an active SSL certificate.
- [ ] **SMTP / Mail Setup:**
  - Configure production SMTP host, port, username, and password credentials in `.env` (or database settings) to replace Mailhog configurations.
- [ ] **SMS API Integration:**
  - Insert valid API authorization keys for Fast2SMS (or selected provider) in the production configuration.
- [ ] **reCAPTCHA Config:**
  - Set up active Google reCAPTCHA keys on public-facing forms (lead generation and contact forms) to block bot spam.
- [ ] **Permissions Hardening:**
  - Restrict access permissions on sensitive server folders (`config/`, `app/`, `helpers/`, `middleware/`, `routes/`, `storage/`) so they cannot be read directly from the browser.

---

## 7. Deliverables & Handoff Documentation
To complete this build phase, provide clear instructions for operators and clients.

- [ ] **Write `ADMIN_GUIDE.md`:**
  - Explain how admins can manage leads, adjust estimator pricing tables, approve testimonials, and add blog posts.
- [ ] **Write `CLIENT_GUIDE.md`:**
  - Guide clients through portal registration, checking project milestones, uploading documents, and paying invoices.
- [ ] **Compile `CHANGELOG.md`:**
  - Chronologically detail all bug-fixes, migrations, stability alignments, and testing extensions applied.
