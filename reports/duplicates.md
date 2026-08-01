# DUPLICATE IMPLEMENTATION ANALYSIS

> Generated: Complete analysis of all duplicate implementations
> Scoring: EXACT DUPLICATE | NEAR DUPLICATE | SAME RESPONSIBILITY | LEGACY REPLACEMENT

---

## 1. DATABASE ABSTRACTION (3 implementations)

| # | Implementation | Location | Type | Lines | Status |
|---|---------------|----------|------|-------|--------|
| 1 | `\Database` (legacy) | `config/database.php` | Singleton PDO | ~45 | ✅ Legacy |
| 2 | `App\Core\Database` (modern) | `app/Core/Database.php` | Singleton PDO | ~55 | ✅ Active |
| 3 | `mysqli $conn` (legacy bridge) | `public/includes/db.php` | mysqli singleton | ~20 | ⚠️ Active |

**Relationship**: NEAR DUPLICATE  
**All produce**: Database connection  
**Conflict**: #3 returns mysqli, #1 and #2 return PDO  
**Risk**: CRITICAL - `$GLOBALS['conn']` type mismatch causes fatal errors  
**Recommendation**: Deprecate #1 (config/database.php), migrate all users to #2, remove #3

---

## 2. SESSION MANAGEMENT (4 implementations)

| # | Implementation | Location | Type | Lines | Status |
|---|---------------|----------|------|-------|--------|
| 1 | `helpers/session.php` | Procedural | Function-based | ~1110 | ⚠️ Active (legacy) |
| 2 | `helpers/auth.php` | Procedural | Partial duplicate | ~120 | ⚠️ Deprecated |
| 3 | `App\Core\SessionManager` | OOP | Class-based | ~120 | ✅ Active (modern) |
| 4 | `AuthService::initializeSession()` | OOP | Method-based | ~50 | ✅ Active |

**Relationship**: SAME RESPONSIBILITY (4 different implementations)  
**Functions duplicated across all 4**:
- Session creation/initialization
- Session token generation
- Fingerprint/hash generation
- Session destruction
- Database session storage

**Key conflicts**:
- `helpers/session.php::generateSessionFingerprint()` = `AuthService::generateFingerprint()`  
- `helpers/session.php::generateDeviceHash()` = `AuthService::generateDeviceHash()`  
- `helpers/session.php::generateSessionToken()` = `App\Core\SessionManager::createSession()` token  
- `helpers/session.php::storeSessionInDatabase()` = `AuthService::initializeSession()` DB insert  

**Risk**: HIGH  
**Recommendation**: Consolidate all into `App\Core\SessionManager`, add backward-compatible function wrappers in `helpers/session.php`

---

## 3. ROUTING (2 implementations)

| # | Implementation | Location | Type | Lines | Status |
|---|---------------|----------|------|-------|--------|
| 1 | `core/Router.php` | Global | Legacy MVC | ~120 | ✅ DEAD (never instantiated) |
| 2 | `App\Core\Router` | PSR-4 | Modern routing | ~120 | ✅ Active |

**Relationship**: LEGACY REPLACEMENT  
**Risk**: LOW - #1 is dead code  
**Recommendation**: Delete `core/Router.php`

---

## 4. AUTHENTICATION HELPERS (duplicate functions)

| Function A | File A | Function B | File B | Score |
|-----------|--------|-----------|--------|-------|
| `is_logged_in()` | `config/app.php` | `isLoggedIn()` | `helpers/session.php` | EXACT SAME LOGIC |
| `is_admin()` | `config/app.php` | `isAdmin()` | `helpers/session.php` | EXACT SAME LOGIC |
| `is_client()` | `config/app.php` | `isClient()` | `helpers/session.php` | EXACT SAME LOGIC |
| `loginUser()` | `helpers/auth.php` | `initializeSessionSecurity()` | `helpers/session.php` | NEAR DUPLICATE |
| `logoutUser()` | `helpers/auth.php` | `destroySession()` | `helpers/session.php` | NEAR DUPLICATE |
| `current_url()` | `config/app.php` | N/A | - | UNIQUE |
| `json_response()` | `config/app.php` | N/A | - | UNIQUE (but similar to `View::json()`) |

---

## 5. CSRF PROTECTION (3 implementations)

| # | Implementation | Location | Score |
|---|---------------|----------|-------|
| 1 | `helpers/csrf.php` | Primary CSRF system | ✅ Complete |
| 2 | `helpers/security.php::csrfToken()` | Token generation | ⚠️ Commented out (safe) |
| 3 | `middleware/security.php::validateCsrfToken()` | Guarded alternative | ✅ Uses `if (!function_exists())` |

**Risk**: LOW - all properly guarded  
**Recommendation**: Remove #2 commented code, keep #1 as primary, remove #3 in favor of #1

---

## 6. RATE LIMITING (3 implementations)

| # | Implementation | Location | Data Store | Score |
|---|---------------|----------|-----------|-------|
| 1 | `helpers/rateLimiter.php` | DB-backed | `rate_limits` table | ✅ Complete |
| 2 | `helpers/security.php::rateLimit()` | Session-backed | `$_SESSION['_rate_limit']` | ⚠️ Duplicate |
| 3 | `middleware/security.php::checkRateLimit()` | Session-backed | `$_SESSION['_rate_limit']` | ⚠️ Duplicate |
| 3b | `AuthService::checkRateLimit()` | Session-backed | `$_SESSION['_rate_limit']` | ⚠️ Duplicate |

**Risk**: MEDIUM - #2 and #3 are session-based (not persistent across requests without session)
**Recommendation**: Keep #1 as canonical, remove #2 and #3 implementations

---

## 7. PASSWORD VALIDATION (2 implementations)

| Function | Location | Score |
|----------|----------|-------|
| `validatePasswordStrength()` | `helpers/security.php` (public) | Public function |
| `validatePasswordStrength()` | `app/services/AuthService.php` (private) | Private method |

**Risk**: LOW - same algorithm  
**Recommendation**: AuthService should call the helper function instead of duplicating

---

## 8. SANITIZATION (2 implementations)

| Function | Location | Details |
|----------|----------|---------|
| `sanitize()` | `helpers/security.php` | Strips tags, escapes HTML |
| `sanitizeInput()` | `middleware/security.php` | Strips null bytes, HTML escapes |

**Risk**: LOW - minor implementation differences  
**Recommendation**: Standardize on one function

---

## 9. ESCAPING (2 implementations)

| Function | Location | Details |
|----------|----------|---------|
| `escapeUrl()` | `helpers/functions_security.php` | `rawurlencode()` |
| `escapeUrl()` | `middleware/security.php` | `FILTER_SANITIZE_URL` |

**Risk**: MEDIUM - different implementations produce different results
**Recommendation**: Standardize on `helpers/functions_security.php` version (context-aware)

---

## 10. VIEW RENDERING (2 implementations)

| # | Implementation | Location | Score |
|---|---------------|----------|-------|
| 1 | `View::render()` | `core/View.php` | Legacy View class |
| 2 | `Controller::view()` | `core/Controller.php` | Controller view method |

**Relationship**: NEAR DUPLICATE  
**Difference**: View class has layout support, Controller view method doesn't  
**Recommendation**: Keep View class, deprecate Controller::view()

---

## 11. DUPLICATE STATISTICS

| Category | Duplicate Count | Lines Wasted | Risk |
|----------|----------------|-------------|------|
| Database abstraction | 3 implementations | ~120 lines | 🔴 CRITICAL |
| Session management | 4 implementations | ~1400 lines | 🟠 HIGH |
| Routing | 2 implementations | ~240 lines | 🟢 LOW (dead) |
| Auth helper functions | 4 pairs | ~200 lines | 🟡 MEDIUM |
| CSRF protection | 3 implementations | ~400 lines | 🟢 LOW (guarded) |
| Rate limiting | 3 + 1 private | ~900 lines | 🟡 MEDIUM |
| Password validation | 2 implementations | ~50 lines | 🟢 LOW |
| Sanitization | 2 implementations | ~40 lines | 🟢 LOW |
| URL escaping | 2 implementations | ~20 lines | 🟡 MEDIUM |
| View rendering | 2 implementations | ~100 lines | 🟢 LOW |
| **TOTAL** | **24+** | **~3500 lines** | **MODERATE** |

