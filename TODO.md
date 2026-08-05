# KVN Construction Platform - Migration TODO

> Current Phase: **Phase 9 Enterprise Modernization (PHP Architecture Completion)**

## Overall Modernization Progress

| Module | Status | Migrated | Coverage |
|--------|--------|----------|----------|
| Architecture | ✅ | - | 100% |
| Repository Infrastructure | ✅ | - | 100% |
| Service Infrastructure | ✅ | - | 100% |
| Public Website | ✅ | - | 100% |
| Client Portal | ✅ | - | 100% |
| Admin CMS | ✅ | - | 100% |
| Blogs | ✅ | - | 100% |
| Quotations | ✅ | - | 100% |
| Reports | ✅ | - | 100% |
| Middleware | ✅ | - | 100% |
| Settings | ✅ Complete | 6/6 pages | 100% |
| Security | ✅ Complete | 5/5 pages | 100% |
| Helpers | ✅ Complete | 5/5 helpers | 100% |
| Services (SQL-free) | ✅ Complete | 2/2 services | 100% |
| Routes (SQL-free) | ✅ Complete | 1/1 route | 100% |
| Auth Handlers (SQL-free) | ✅ Complete | 7/7 handlers | 100% |
| Legacy Models (dead code) | ⏳ Deferred | - | - |

**Overall modernization: ~90%**

---

## Phase 9 Tasks

### Step 1 — Complete Repository Audit
- [x] Scan entire codebase for `$conn`, `$db`, `mysqli`, `PDO`, `prepare()`, `query()`, `bind_param`, `fetch_assoc`, `fetch_all`, `num_rows`, `CREATE TABLE`, `ALTER TABLE`, `DROP TABLE`
- [x] Classify each occurrence as ALLOWED (repositories, database layer, tests) or NOT ALLOWED (pages, controllers, services, middleware, helpers)
- [x] Identify remaining SQL in services, helpers, routes, and pages

### Step 2 — Refactor Services (SQL eliminated)
- [x] `app/services/AdminUserService.php` — removed all direct SQL, delegated to UserRepository, SessionRepository, AuditRepository
- [x] `app/services/AuthService.php` — removed all direct SQL, delegated to UserRepository, SessionRepository, AuditRepository

### Step 3 — Refactor Routes (SQL eliminated)
- [x] `routes/api_estimator.php` — removed all direct SQL, delegated to EstimatorRepository

### Step 4 — Refactor Helpers (SQL eliminated)
- [x] `helpers/mail.php` — removed `$conn` and SQL, delegated to MailRepository
- [x] `helpers/sms.php` — removed `$conn` and SQL, delegated to SmsRepository
- [x] `helpers/session.php` — already clean (verified)
- [x] `helpers/security.php` — already clean (verified)
- [x] `helpers/rateLimiter.php` — already clean (verified)

### Step 5 — Refactor Security (SQL eliminated)
- [x] `app/security/SessionManager.php` — removed all direct SQL, delegated to SessionRepository

### Step 6 — Refactor Auth Handlers (legacy User model eliminated)
- [x] `public/auth/verify-reset-otp-handler.php` — uses UserRepository
- [x] `public/auth/resend-reset-otp-handler.php` — uses UserRepository
- [x] `public/forgot-password.php` — uses UserRepository
- [x] `public/reset-password.php` — uses UserRepository
- [x] `public/auth/phone-login-handler.php` — uses AuthService
- [x] `public/auth/register-handler.php` — uses AuthService
- [x] `public/auth/resend-otp-handler.php` — uses AuthService
- [x] `public/auth/verify-phone-otp-handler.php` — uses AuthService

### Step 7 — Refactor Admin Pages (legacy models eliminated)
- [x] `public/admin/dashboard.php` — removed legacy Lead model, uses AdminController
- [x] `public/admin/logout.php` — removed `$conn` from controller instantiation
- [x] `public/logout.php` — removed `$conn` from controller instantiation
- [x] `public/auth/admin-login-handler.php` — removed `$conn` from controller instantiation

### Step 8 — Repository Methods Added
- [x] `UserRepository` — added `deleteUser()`, `getUserActivity()`, `deleteSecurityLogsByUserId()`, `saveOtp()`, `verifyOtp()`, `expireOtp()`, `findActiveOtp()`, `incrementOtpAttempts()`, `markOtpUsed()`, `findByIdentifier()`, `incrementFailedAttempts()`, `resetFailedAttempts()`, `updateLastLogin()`, `getDashboardCounts()`
- [x] `EstimatorRepository` — added `getApiPackages()`, `getApiPackageById()`, `getApiPricingByPackage()`, `saveApiLead()`
- [x] `MailRepository` — created new repository with `log()`, `getRecent()`, `prune()`

### Step 9 — Audit & Verification
- [x] `php -l` on all 22 modified files — ALL PASS
- [x] Search for legacy SQL in services, helpers, routes, pages — ZERO remaining
- [x] No duplicate repository/service methods
- [x] No dead code introduced

### Step 10 — Final Fresh Audit (Post-Review)
- [x] Fresh repository-wide scan performed — **ZERO SQL violations in production code**
- [x] Dependency scan for `OtpService`/`OTPService` duplicate executed
- [x] `bootstrap/providers/ServiceProvider.php:72` references `new OtpService($db)` — **deletion BLOCKED** (prerequisite: zero references)
- [x] `app/services/OtpService.php` retained (byte-identical to `OTPService.php`, MD5 `2a2e4fd0c2edbb1ec07ea57798a06270`)
- [x] `UserRepository::exists()` duplicate — **NOT present** (UserRepository does not extend base Repository, no `exists()` method)
- [x] `app/security/SessionManager.php` retained — still used by AuthController (not dead code)
- [x] Reports updated: `php_architecture_completion.md`, `legacy_elimination.md`

### Deferred (Database Reconstruction Phase)
- [ ] Runtime database testing
- [ ] Browser/form submission testing
- [ ] Integration testing
- [ ] Legacy model files (`app/models/Lead.php`, `app/models/User.php`) removal
- [ ] `core/Model.php` legacy base class removal
- [ ] `app/security/PdoDatabase.php` legacy compatibility wrapper removal
- [ ] Resolve `OtpService`/`OTPService` duplicate: remove `'OtpService' => new OtpService($db)` from `ServiceProvider` resolver (or fix constructor to accept `OtpRepository`), then delete `app/services/OtpService.php`
- [ ] Verify `AuthController` `generateOTP()`/`verifyOTP()` method calls against `OTPService` API after DB phase

---

## Phase 9 Repository Method Inventory

### UserRepository (added 14 methods)
- `deleteUser(int $id): bool`
- `getUserActivity(int $userId, int $limit = 10): array`
- `deleteSecurityLogsByUserId(int $userId): bool`
- `saveOtp(int $userId, string $otp, string $purpose, int $expiryMinutes = 5): bool`
- `verifyOtp(int $userId, string $otp, string $purpose): bool`
- `expireOtp(int $userId, string $purpose): bool`
- `findActiveOtp(int $userId, string $purpose): ?array`
- `incrementOtpAttempts(int $otpId): bool`
- `markOtpUsed(int $otpId): bool`
- `findByIdentifier(string $identifier): ?array`
- `incrementFailedAttempts(int $userId): bool`
- `resetFailedAttempts(int $userId): bool`
- `updateLastLogin(int $userId): bool`
- `getDashboardCounts(): array`

### EstimatorRepository (added 4 methods)
- `getApiPackages(): array`
- `getApiPackageById(int $id): ?array`
- `getApiPricingByPackage(int $packageId): array`
- `saveApiLead(array $data): int`

### MailRepository (created new — 3 methods)
- `log(string $recipient, string $subject, string $status, string $error = '', ?string $ipAddress = null): bool`
- `getRecent(int $limit = 50): array`
- `prune(int $daysOld = 90): int`

### ServiceProvider (updated)
- Added `SessionRepository` and `AuditRepository` to resolver
- Updated `createAuthService()` to pass SessionRepository and AuditRepository