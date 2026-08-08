# Linux Case-Sensitivity Audit

**Release:** v1.0.0-rc.1
**Audit scope:** Path/class references that would break on case-sensitive Linux filesystems.
**Status:** PASS (all known mismatches resolved)

---

## Summary

The project previously relied on a **case-insensitive fallback** in the custom
autoloader (`config/app.php`) and Windows-style `require_once` paths that pointed
at capitalized directory names (`Repositories/`, `Services/`) which do not exist
on case-sensitive Linux filesystems.

A full-repository scan was performed covering `require`, `require_once`,
`include`, `include_once`, `namespace`, `use`, PSR-4/legacy autoloader mappings,
ServiceProvider mappings, repository/service/controller class wiring, and
directory/file capitalization under `app/`, `bootstrap/`, `config/`, `core/`,
`helpers/`, `middleware/`, `routes/`, `public/`.

---

## Mismatches Found & Resolved

| # | Affected File | Current Reference (before) | Actual Filesystem Path | Correction Made | Validated |
|---|---------------|------------------------------|------------------------|-----------------|-----------|
| 1 | `app/controllers/AuthController.php` | `require __DIR__.'/../Repositories/UserRepository.php` | `app/repositories/UserRepository.php` | Rewrote require to `__DIR__.'/../repositories/UserRepository.php'` | PASS (`php -l`, runtime) |
| 2 | `app/controllers/AuthController.php` | `require __DIR__.'/../Services/OTPService.php'` | class removed / not on disk | Removed legacy reference; OTP handled by `AuthService` + `UserRepository` | PASS |
| 3 | `app/controllers/AuthController.php` | `new OTPService()` (global, no class) | `app/services/OtpService.php` (legacy, deleted) | Facade delegates to canonical `AuthService`/`UserRepository` only | PASS |
| 4 | `app/controllers/AuthController.php` | `new SessionManager()` from `App\Core\SessionManager` | `app/security/SessionManager.php` (global) | Removed direct `SessionManager` dependency; session lifecycle owned by `AuthService` | PASS |
| 5 | `app/controllers/auth/AdminAuthController.php` | `require __DIR__.'/auth/AuthController.php'` (non-existent) | `app/controllers/AuthController.php` | Changed to `require __DIR__.'/../AuthController.php'` | PASS (`php -l`) |
| 6 | `bootstrap/providers/ServiceProvider.php` | Global class refs `new UserRepository($db)`, `new SessionRepository($db)`, `new AuditRepository($db)`, `new EstimatorRepository($db)` (classes are namespaced `App\Repositories\*`) | `app/repositories/UserRepository.php` etc. | Used actual namespaced refs `\App\Repositories\...`; kept global refs for genuinely-global classes (`AuthService`, `LeadService`, etc.) | PASS (runtime, `_otp_verify.php`) |
| 7 | `bootstrap/providers/ServiceProvider.php` | `new ProjectService(...)` (global, but class is `App\Services\ProjectService`) | `app/services/ProjectService.php` | Used `\App\Services\ProjectService` | PASS |

---

## Validation Results

- **PHP lint** on all edited files: **PASS** (no syntax errors).
- **Runtime autoload resolution** (`_otp_verify.php`, `_auth_verify.php`):
  - `ServiceProvider::get('AuthService')` resolves without `Class not found`. **PASS**
  - `App\Repositories\UserRepository` / `SessionRepository` / `AuditRepository`
    load via the custom autoloader. **PASS**
  - `AuthController` facade + `AdminAuthController` resolve. **PASS**
- **Zero remaining production references** to removed legacy `OTPService`/`OtpService`/`OtpRepository`. **PASS**
- The autoloader's `lcfirst`-directory fallback safely resolves the lowercase-on-disk
  `app/repositories`, `app/services`, `app/controllers`, `app/security` directories
  for `App\`-prefixed names while also supporting the legacy unnamespaced classes.

---

## Note on the MIXED namespace convention

The codebase uses **both** global classes (no `namespace`) and `App\Services` /
`App\Repositories` namespaced classes. Each reference in `ServiceProvider` was
inspected and set to the **actual** namespace of its target class. This is a
compatibility-layer decision; it does not redesign the architecture and keeps
case-sensitive resolution correct on Linux.

