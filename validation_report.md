# Repository-Wide Validation Report

> Generated: Phase 2 Architecture Stabilisation
> Scope: Duplicate classes, constants, functions, debug code, and anti-patterns

---

## 1. DUPLICATE NAMESPACES

| Namespace | Files | Status |
|-----------|-------|--------|
| `App\Core` | `app/Core/Database.php`, `Router.php`, `SessionManager.php`, `Service.php`, `Repository.php`, `index.php` | ✅ Clean |
| `App\Controllers` | `PublicController`, `ClientController`, `EstimatorController`, `AuthController`, `admin/AdminController`, `admin/LeadController`, `admin/MediaController`, `admin/ProjectController`, `auth/AdminAuthController` | ✅ Clean |
| `App\Repositories` | `UserRepository`, `LeadRepository`, `ProjectRepository`, `BlogRepository`, `ContentRepository`, `EstimatorRepository`, `InvoiceRepository`, `MediaRepository`, `QuotationRepository`, `SupportRepository` | ✅ Clean |
| `App\Services` | `AuthService`, `ContentService`, `EstimatorService`, `InvoiceService`, `LeadService`, `MediaService`, `OtpService`, `ProjectService`, `QuotationService`, `SupportService`, `UserService` | ✅ Clean |
| `App\Models` | `Lead`, `User` | ✅ Clean |

**⚠️ Fixed**: `config/database.php` had `namespace App\Core;` causing class collision with `app/Core/Database.php` (resolved in Phase 2)

---

## 2. DUPLICATE CLASSES

| Class | Files | Status |
|-------|-------|--------|
| `Database` | `config/database.php` (global `\Database`), `app/Core/Database.php` (`App\Core\Database`) | ⚠️ Different namespaces, both loaded. **Risk: LOW** |
| `Router` | `core/Router.php` (global), `app/Core/Router.php` (PSR-4) | ⚠️ **UNUSED**: `core/Router.php` is never instantiated by modern framework |
| `SessionManager` | `app/Core/SessionManager.php` (OOP), `helpers/session.php` (procedural) | ⚠️ **DUPLICATE IMPLEMENTATION** - Same functionality, different architecture |
| `AdminAuthController` | `app/controllers/auth/AdminAuthController.php` | ✅ Legitimate inheritance from AuthController |

---

## 3. DUPLICATE CONSTANTS

| Constant | Defined In | Status |
|----------|-----------|--------|
| `SESSION_TIMEOUT` | `config/app.php` (guarded=3600), `helpers/session.php` (guarded=3600), `helpers/auth.php` | ✅ **FIXED** - was raw define |
| `DEFAULT_RATE_LIMIT` | `helpers/rateLimiter.php` | ✅ **FIXED** - was raw define |
| `DEFAULT_RATE_WINDOW` | `helpers/rateLimiter.php` | ✅ **FIXED** - was raw define |
| `STRICT_ADMIN_IP_CHECK` | `middleware/admin.php` | ✅ **FIXED** - was raw define |
| `STRICT_ADMIN_AGENT_CHECK` | `middleware/admin.php` | ✅ **FIXED** - was raw define |
| `OTP_EXPIRY_MINUTES` | `config/app.php` (guarded=5), `helpers/otp.php` (raw=5) | ⚠️ **UNGUARDED** in helpers/otp.php |
| `OTP_MAX_ATTEMPTS` | `config/app.php` (guarded=3), `helpers/otp.php` (raw=5) | ⚠️ **UNGUARDED + DIFFERENT VALUE** (3 vs 5) |
| `ALLOWED_IMAGE_TYPES` | `config/app.php` (raw), `helpers/upload.php` (raw) | ⚠️ **FATAL IF BOTH LOADED** |
| `ALLOWED_DOCUMENT_TYPES` | `config/app.php` (raw), `helpers/upload.php` (raw) | ⚠️ **FATAL IF BOTH LOADED** |
| `CONFIG_PATH` | `config/app.php` (guarded), `tests/*.php` (5 files, raw) | ⚠️ **UNGUARDED in test files** |
| `APP_URL` | `config/app.php` (guarded), `tests/Fakes/ConfigFake.php` (raw), `tests/run.php` (raw) | ⚠️ **UNGUARDED in test files** |

---

## 4. DUPLICATE HELPER FUNCTIONS

| Function | Files | Status |
|----------|-------|--------|
| `sanitize()` vs `sanitizeInput()` | `helpers/security.php`, `middleware/security.php` | ⚠️ **DUPLICATE** - same purpose, different names |
| `checkRateLimit()` | `helpers/rateLimiter.php` (DB-backed), `middleware/security.php` (session-backed) | ✅ Both guarded with `if (!function_exists())` |
| `generateCsrfToken()` | `helpers/csrf.php`, `middleware/security.php` | ✅ Guarded |
| `generateDeviceHash()` | `helpers/session.php` (procedural), `app/services/AuthService.php` (private) | ⚠️ **DUPLICATE LOGIC** |
| `generateFingerprint()` vs `generateSessionFingerprint()` | `app/services/AuthService.php`, `helpers/session.php` | ⚠️ **DUPLICATE LOGIC** |
| `destroySession()` vs `logoutUser()` | `helpers/session.php`, `helpers/auth.php` | ⚠️ **DUPLICATE** - same logic |
| `isLoggedIn()` vs `is_logged_in()` | `helpers/session.php`, `config/app.php` | ⚠️ **DUPLICATE** - snake_case vs camelCase |
| `isAdmin()` vs `is_admin()` | `helpers/session.php`, `config/app.php` | ⚠️ **DUPLICATE** |
| `isClient()` vs `is_client()` | `helpers/session.php`, `config/app.php` | ⚠️ **DUPLICATE** |
| `validatePasswordStrength()` | `helpers/security.php`, `app/services/AuthService.php` (private) | ⚠️ **DUPLICATE LOGIC** |
| `escapeUrl()` | `helpers/functions_security.php`, `middleware/security.php` | ⚠️ **DUPLICATE** - different implementations |

---

## 5. DEBUG CODE (`die()` / `exit()` / `var_dump()` / `print_r()` / `dd()`)

### `die()` in PRODUCTION FILES (>40 occurrences)

| File | Count | Context |
|------|-------|---------|
| `core/Controller.php` | 4 | View/model not found, abort |
| `core/Router.php` | 1 | Middleware not found |
| `core/View.php` | 3 | View/partial not found |
| `public/estimator.php` | 3 | Legacy file |
| `public/admin/media/*.php` (4 files) | 8 | Admin media pages |
| `public/admin/portfolio/edit.php` | 4 | Portfolio editor |
| `public/admin/portfolio/featured.php` | 2 | Portfolio featured |
| `public/admin/portfolio/index.php` | 1 | Portfolio list |
| `public/admin/quotations/pdf.php` | 3 | PDF generation |
| `public/admin/services/edit.php` | 4 | Service editor |
| `public/admin/testimonials/approvals.php` | 2 | Testimonial approvals |
| `public/admin/testimonials/featured.php` | 2 | Testimonial featured |
| `public/admin/testimonials/index.php` | 1 | Testimonial list |
| `public/admin/testimonials/videos.php` | 2 | Testimonial videos |
| `public/admin/videos/categories.php` | 2 | Video categories |
| `public/admin/videos/index.php` | 2 | Video list |
| `public/client/quotations/view.php` | 2 | Client quotation view |

### `exit()` in `app/` Controllers

| File | Count | Context |
|------|-------|--------|
| `app/controllers/AuthController.php` | 3 | JSON responses |
| `app/controllers/EstimatorController.php` | 3 | JSON + redirect |
| `app/controllers/admin/MediaController.php` | 1 | Redirect |
| `app/controllers/admin/ProjectController.php` | 1 | Redirect |

### `var_dump()` / `print_r()` / `dd()` in PRODUCTION

| File | Line(s) | Risk |
|------|---------|------|
| `app/views/auth/admin-login.php` | 399, 407 | ⚠️ **DEBUG CODE IN PRODUCTION VIEW** |

---

## 6. ARCHITECTURAL ANTI-PATTERNS

### `->connect()` calls

| File | Line | Issue |
|------|------|-------|
| `core/Model.php` | 48 | `$this->db->connect()` - `\Database` class has `getConnection()`, NOT `connect()` |
| `_runtime_test.php` | 155 | Debug script only |

### `findByClient()` calls

| File | Status |
|------|--------|
| `app/repositories/ProjectRepository.php` | ✅ Exists |
| `app/repositories/QuotationRepository.php` | ✅ Exists |
| `app/services/InvoiceService.php` | ✅ **FIXED** - was calling non-existent `findByClient()` |

### `new PDO()` in production code

| File | Line | Context |
|------|------|---------|
| `app/Core/Database.php` | 37 | ✅ Proper singleton |
| `config/database.php` | 35 | ✅ Legacy singleton |
| `tests/*.php` | 14 occurrences | ✅ Test files only |

### **CRITICAL**: `$GLOBALS['conn']` type mismatch

| File | Expects | Gets |
|------|---------|------|
| `helpers/session.php` | PDO | ⚠️ Could be **mysqli** |
| `helpers/security.php` | PDO | ⚠️ Could be **mysqli** |
| `helpers/rateLimiter.php` | PDO | ⚠️ Could be **mysqli** |
| `middleware/auth.php` | PDO | ⚠️ Could be **mysqli** |
| `public/includes/db.php` | Sets `$conn` as **mysqli** | ⚠️ **BREAKS PDO-dependent helpers** |
| `middleware/admin.php` | PDO (uses `$conn->prepare()`) | ⚠️ **FATAL ERROR if mysqli loaded** |

### SQL Injection in `public/client/payments/invoices.php`

```php
$check = $conn->query("SELECT id FROM client_invoices WHERE client_id = $clientId LIMIT 1");
```

- Direct variable interpolation with NO prepared statement
- Uses **mysqli** API while rest of modern code uses **PDO**
- Creates tables (DDL) on every page load
- Inserts demo data on every request

---

## 7. NAMESPACE vs GLOBAL CLASS MISMATCH

| File | Issue | Impact |
|------|-------|--------|
| `app/controllers/AuthController.php` | **No namespace** (global), uses `require` instead of autoloader | Bypasses PSR-4 |
| `app/controllers/admin/AdminController.php` | **No namespace** (global), uses `new $repo()` directly | Bypasses DI/ServiceProvider |
| `app/services/AuthService.php` | **No namespace** (global), extends `\Service` | Cannot use `use App\Services\AuthService` |
| `app/repositories/LeadRepository.php` | **No namespace** (global), extends `\Repository` | Mixed with PSR-4 repos |
| `app/repositories/BlogRepository.php` | **No namespace** (global), extends `\Repository` | Mixed with PSR-4 repos |
| `app/repositories/EstimatorRepository.php` | **No namespace** (global), extends `\Repository` | Mixed with PSR-4 repos |
| `app/repositories/MediaRepository.php` | **No namespace** (global), extends `\Repository` | Mixed with PSR-4 repos |
| `app/repositories/QuotationRepository.php` | **No namespace** (global), extends `\Repository` | Mixed with PSR-4 repos |

---

## 8. HELPER FILES WITH UNGUARDED FUNCTION DEFINITIONS

| File | Risk | Details |
|------|------|---------|
| `helpers/auth.php` | LOW | `loginUser()`, `logoutUser()` - no guards, but single load expected |
| `helpers/formatter.php` | LOW | 7 functions, no guards |
| `helpers/functions.php` | LOW | 3 functions, no guards |
| `helpers/api_response.php` | LOW | 10 constants (guarded), functions unguarded |
| `helpers/otp.php` | **MEDIUM** | Raw `define()` calls without `if (!defined())` |
| `helpers/rateLimiter.php` | ✅ **FIXED** | All constants now guarded |
| `helpers/upload.php` | **HIGH** | Raw `define()` for `ALLOWED_IMAGE_TYPES`, `ALLOWED_DOCUMENT_TYPES` conflicts with `config/app.php` |
| `helpers/mail.php` | LOW | SMTP constants guarded |
| `helpers/sms.php` | LOW | Constants guarded |
| `helpers/seo.php` | LOW | No constants, 8 functions unguarded |

---

## RISK DASHBOARD

| Severity | Count | Key Items |
|----------|-------|-----------|
| 🔴 **CRITICAL** | 2 | `$GLOBALS['conn']` PDO/mysqli type mismatch; `invoices.php` SQL injection |
| 🟠 **HIGH** | 3 | Un-guarded constants in `helpers/otp.php`, `helpers/upload.php`; class collision (Database, Router) |
| 🟡 **MEDIUM** | 12 | Duplicate function implementations; debug code in view; namespace mismatches; test files with raw defines |
| 🟢 **LOW** | 8 | Single-load helpers without guards; die() in view rendering |
| ✅ **FIXED** | 8 | All Phase 2 fixes confirmed |

---

## PHASE 3 RECOMMENDATIONS (Priority Order)

1. **P0**: Standardize `$GLOBALS['conn']` to always be PDO (or deprecate completely)
2. **P0**: Guard all remaining raw `define()` calls in `helpers/otp.php`, `helpers/upload.php`
3. **P1`: Consolidate `isLoggedIn()`/`is_logged_in()`, `isAdmin()`/`is_admin()`, `isClient()`/`is_client()`
4. **P1**: Consolidate `core/Router.php` → remove (unused, superseded by `App\Core\Router`)
5. **P1**: Consolidate 4 session implementations → `App\Core\SessionManager`
6. **P1**: Remove debug `var_dump()` from `app/views/auth/admin-login.php`
7. **P2**: Convert un-namespaced global controllers/repos to PSR-4 `App\*` namespace
8. **P2`: Guard all test file `define()` calls with `if (!defined())`
9. **P3**: Replace `die()` in core framework files with proper exception throwing

