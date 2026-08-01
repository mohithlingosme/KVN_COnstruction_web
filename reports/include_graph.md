# FILE INCLUSION GRAPH

> Generated: Complete require/include dependency analysis
> Total include relationships mapped: ~240

---

## 1. BOOTSTRAP CHAIN

```
index.php (root)
  └── header('Location: public/')
  
public/index.php (homepage)
  ├── require_once ../config/app.php          [CONFIG + AUTOLOADER]
  ├── require_once ../helpers/functions.php   [UTILITY HELPERS]
  └── require_once ../app/Core/routes.php     [ROUTE DEFINITION]
       └── App\Core\Router::dispatch()
            └── require (controller file)      [DYNAMIC]

public/index.php (legacy homepage)
  └── include ../app/views/layouts/footer.php
```

## 2. CONFIG LOAD CHAIN

```
config/app.php
  ├── $envFile = dirname(__DIR__) . '/.env'           [ENVIRONMENT]
  ├── spl_autoload_register()                          [PSR-4 AUTOLOADER]
  │    ├── App\* → app/ directory
  │    └── Legacy fallback → core/, app/controllers/, etc.
  └── define() constants                                [APPLICATION CONFIG]
       ├── DB_HOST, DB_NAME, DB_USER, DB_PASS
       ├── SESSION_TIMEOUT, SESSION_NAME
       ├── OTP_*, RATE_LIMIT_*
       └── APP_NAME, APP_URL, APP_ENV
```

## 3. MODERN CONTROLLER CHAIN

```
App\Core\Router::dispatch()
  ↓
App\Controllers\PublicController
  └── new App\Services\ContentService()
       └── new App\Repositories\ContentRepository()
            └── App\Core\Database::getInstance()->getConnection() [PDO]

App\Controllers\ClientController
  ├── new App\Services\InvoiceService()
  │    └── new App\Repositories\InvoiceRepository()
  │         └── App\Core\Database::getInstance()->getConnection() [PDO]
  ├── new App\Services\SupportService()
  └── new App\Services\UserService()
       └── new App\Repositories\UserRepository()
            └── App\Core\Database::getInstance()->getConnection() [PDO]

App\Controllers\EstimatorController
  ├── require_once ../Services/EstimatorService.php  [LEGACY REQUIRE]
  └── new App\Services\EstimatorService()
       └── new App\Repositories\EstimatorRepository()
            └── parent::__construct(PDO $db)
```

## 4. LEGACY AUTH CONTROLLER CHAIN

```
AuthController (global)
  ├── require_once ../Core/SessionManager.php
  ├── require_once ../Repositories/UserRepository.php
  ├── require_once ../Services/OTPService.php
  ├── new UserRepository()
  │    └── new Database() → $database->connect()  [BROKEN - no connect()]
  ├── new OTPService()
  └── new SessionManager()
       └── App\Core\SessionManager::startSession()
```

## 5. ADMIN MIDDLEWARE CHAIN

```
public/admin/*.php
  ├── require_once ../../middleware/admin.php
  │    ├── require_once dirname(__DIR__) . '/config/app.php'
  │    ├── require_once HELPER_PATH . '/session.php'
  │    ├── require_once HELPER_PATH . '/security.php'
  │    ├── require_once HELPER_PATH . '/csrf.php'
  │    ├── validateSession()    [from helpers/session.php]
  │    │    └── uses $GLOBALS['conn']  [PDO or mysqli? Mismatch risk!]
  │    └── require_once ../../includes/db.php
  │         └── mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT)
  │              └── $conn = $legacyConnection  [mysqli, NOT PDO!]
  └── HTML + inline PHP queries
```

## 6. CLIENT MIDDLEWARE CHAIN

```
public/client/*.php
  ├── session_start()                               [RAW - no config]
  ├── require_once ../../includes/db.php            [MYSQLI CONNECTION]
  │    └── mysqli $conn
  └── Direct SQL queries (no prepared statements on some pages)
```

## 7. HELPER CROSS-REFERENCES

```
helpers/session.php
  ├── uses $GLOBALS['conn']                    [Expects PDO]
  ├── calls logSecurityEvent()                 [from helpers/security.php]
  └── calls destroySession()

helpers/security.php
  ├── defines: sanitize(), securityHeaders(), csrfToken()
  ├── requireCsrf()                            [calls verifyCsrfToken()]
  ├── logSecurityEvent()                       [uses $GLOBALS['conn']]
  └── calls securityHeaders()                  [auto-executed at bottom]

helpers/csrf.php
  ├── defines: generateCsrfToken(), verifyCsrfToken(), validateCsrf()
  ├── cleanupExpiredCsrf()                     [auto-executed at bottom]
  └── calls logSecurityEvent()                 [on fingerprint mismatch]

helpers/rateLimiter.php
  ├── defines: checkRateLimit()                [DB-backed]
  ├── uses $GLOBALS['conn']                    [Expects PDO]
  └── cleanupExpiredRateLimits()               [auto-executed at bottom]
```

## 8. SERVICE PROVIDER CHAIN

```
bootstrap/providers/ServiceProvider.php
  ├── require_once ../../config/database.php
  │    └── new Database() → getConnection()    [FIXED: was connect()]
  ├── match($class) → new *Service(Repository)
  │    ├── LeadService → LeadRepository
  │    ├── ProjectService → ProjectRepository
  │    ├── AuthService → UserRepository + PDO
  │    ├── OtpService → PDO
  │    ├── MediaService → MediaRepository + PDO
  │    ├── QuotationService → QuotationRepository
  │    └── EstimatorService → EstimatorRepository
  └── Used by: AdminController::__construct()
```

## 9. DEPENDENCY GRAPH (Graphviz DOT)

```
digraph G {
  rankdir=TB;
  node [shape=box, style=rounded];

  // Bootstrap
  "index.php (root)" -> "public/index.php" [label="redirect"];
  "public/index.php (homepage)" -> "config/app.php" [label="require_once"];
  "config/app.php" -> ".env" [label="read", style=dashed];
  "config/app.php" -> "PSR-4 Autoloader" [label="register"];
  "PSR-4 Autoloader" -> "app/Core/*" [label="loads"];
  "PSR-4 Autoloader" -> "core/*" [label="fallback"];
  "PSR-4 Autoloader" -> "app/controllers/*" [label="fallback"];
  "PSR-4 Autoloader" -> "app/services/*" [label="fallback"];
  "PSR-4 Autoloader" -> "app/repositories/*" [label="fallback"];
  "PSR-4 Autoloader" -> "app/models/*" [label="fallback"];

  // Modern chain
  "public/index.php" -> "app/Core/routes.php" [label="require_once"];
  "app/Core/routes.php" -> "App\\Core\\Router" [label="::dispatch()"];
  "App\\Core\\Router" -> "App\\Controllers\\PublicController" [label="instantiates"];
  "PublicController" -> "App\\Services\\ContentService" [label="new"];
  "ContentService" -> "App\\Repositories\\ContentRepository" [label="new"];
  "ContentRepository" -> "App\\Core\\Database" [label="getInstance()"];
  "App\\Core\\Database" -> "PDO" [label="new"];

  // Legacy chain
  "public/admin/*.php" -> "middleware/admin.php" [label="require_once"];
  "middleware/admin.php" -> "config/app.php" [label="require_once"];
  "middleware/admin.php" -> "helpers/session.php" [label="require_once"];
  "middleware/admin.php" -> "helpers/security.php" [label="require_once"];
  "middleware/admin.php" -> "helpers/csrf.php" [label="require_once"];
  "middleware/admin.php" -> "public/includes/db.php" [label="require_once"];
  "public/includes/db.php" -> "mysqli" [label="new"];
  "helpers/session.php" -> "$GLOBALS['conn']" [label="expects PDO", style=dotted, color=red];
  "public/includes/db.php" -> "$GLOBALS['conn']" [label="sets mysqli", style=dotted, color=red];

  // ServiceProvider
  "middleware/admin.php" -> "bootstrap/providers/ServiceProvider.php" [label="require_once"];
  "ServiceProvider" -> "config/database.php" [label="require_once"];
  "config/database.php" -> "PDO" [label="new (legacy)"];
  "ServiceProvider" -> "LeadService" [label="new"];
  "ServiceProvider" -> "ProjectService" [label="new"];
  "ServiceProvider" -> "AuthService" [label="new"];

  // Client - Legacy mysqli
  "public/client/payments/invoices.php" -> "public/includes/db.php" [label="require_once", color=red];
  "public/client/payments/invoices.php" -> "mysqli::query()" [label="SQL injection risk", color=red];

  // Conflicts
  "config/database.php" [color=orange, label="config/database.php\nnamespace App\\Core\n(FIXED - removed)"];
  "core/Model.php" -> "config/database.php" [label="require_once"];
  "core/Model.php" -> "Database::connect()" [label="BROKEN - no connect()", color=red];
}
```

## 10. INCLUDE PATH ANALYSIS

| Require type | Count | Examples |
|-------------|-------|----------|
| Relative `__DIR__` | ~40 | `__DIR__ . '/../Services/EstimatorService.php'` |
| Absolute constant | ~25 | `ROOT_PATH . '/config/app.php'` |
| Dynamic variable | ~15 | `$controllerPath = '../app/controllers/' . $name` |
| Autoloader PSR-4 | ~20 | `use App\Services\ContentService` |
| Autoloader fallback | ~10 | `new LeadRepository()` (namespace-fallback) |

## 11. BROKEN INCLUDE RISKS

| File | Include Path | Risk |
|------|-------------|------|
| `core/Model.php:10` | `require_once __DIR__ . '/../config/database.php'` | OK - but calls `connect()` which doesn't exist |
| `AuthController.php:1-3` | `require_once __DIR__ . '/../Core/SessionManager.php'` | OK - loads PSR-4 SessionManager |
| `routes/api_estimator.php:24` | `require_once HELPER_PATH . '/csrf.php'` | OK - constant defined |
| `middleware/admin.php:38` | `require_once HELPER_PATH . '/session.php'` | OK - but session.php uses $GLOBALS['conn'] |

## 12. CRITICAL INCLUDE ISSUES

| Issue | Impact | Files Affected |
|-------|--------|----------------|
| `$GLOBALS['conn']` type mismatch | **FATAL** - mysqli methods called on PDO or vice versa | helpers/session.php, helpers/security.php, helpers/rateLimiter.php, middleware/auth.php |
| `core/Model.php::connect()` | **FATAL** - method doesn't exist | core/Model.php (all model instances) |
| Raw `session_start()` in client pages | Session config bypassed | public/client/*.php (20 files) |
| Multiple autoloader definitions | Low - only one exists | config/app.php |

