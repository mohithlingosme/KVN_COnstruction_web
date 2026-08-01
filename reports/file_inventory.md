# FILE INVENTORY REPORT

> Generated: Complete repository analysis
> Total files inventoried: ~280 PHP, 8 JS, 4 CSS, 5 SQL, 4 config, 3 Docker, 16 test

---

## 1. CONTROLLERS (10 files)

| # | Path | Namespace | Class | Parent | Responsibility | Dependencies |
|---|------|-----------|-------|--------|---------------|--------------|
| 1 | `app/controllers/PublicController.php` | `App\Controllers` | `PublicController` | - | Public page data | ContentService |
| 2 | `app/controllers/ClientController.php` | `App\Controllers` | `ClientController` | - | Client portal data | InvoiceService, SupportService, UserService |
| 3 | `app/controllers/EstimatorController.php` | `App\Controllers` | `EstimatorController` | - | Cost estimation API | EstimatorService |
| 4 | `app/controllers/AuthController.php` | (global) | `AuthController` | - | OTP login/verify | UserRepository, OTPService, SessionManager |
| 5 | `app/controllers/auth/AdminAuthController.php` | (global) | `AdminAuthController` | AuthController | Admin login | AuthController |
| 6 | `app/controllers/admin/AdminController.php` | (global) | `AdminController` | - | Dashboard | UserRepository, LeadRepository, ProjectRepository, ServiceProvider |
| 7 | `app/controllers/admin/LeadController.php` | (global) | `LeadController` | - | Lead management | LeadService |
| 8 | `app/controllers/admin/MediaController.php` | (global) | `MediaController` | - | Media uploads | MediaService |
| 9 | `app/controllers/admin/ProjectController.php` | (global) | `ProjectController` | - | Project management | ProjectService |
| 10 | `app/controllers/ClientController.php` | `App\Controllers` | `ClientController` | - | Client portal | InvoiceService, SupportService, UserService |

---

## 2. SERVICES (12 files)

| # | Path | Namespace | Class | Parent | Responsibility | Dependencies |
|---|------|-----------|-------|--------|---------------|--------------|
| 1 | `app/services/AuthService.php` | (global) | `AuthService` | `\Service` | Auth business logic | UserRepository, PDO |
| 2 | `app/services/ContentService.php` | `App\Services` | `ContentService` | - | CMS content | ContentRepository |
| 3 | `app/services/EstimatorService.php` | `App\Services` | `EstimatorService` | `Service` | Estimator logic | EstimatorRepository |
| 4 | `app/services/InvoiceService.php` | `App\Services` | `InvoiceService` | `Service` | Invoice biz logic | InvoiceRepository |
| 5 | `app/services/LeadService.php` | `App\Services` | `LeadService` | `Service` | Lead mgmt | LeadRepository |
| 6 | `app/services/MediaService.php` | `App\Services` | `MediaService` | `Service` | Media upload | MediaRepository, PDO |
| 7 | `app/services/OtpService.php` | (global) | `OTPService` | - | OTP generation | PDO |
| 8 | `app/services/ProjectService.php` | `App\services` | `Projiceservice` | `\Service` |Project logic | ProjectRepository |
|9 9  | `app/services/QuotationService.p` | `App\Services` | QuotationService | Service` | Quatation biz | QuotationRepository |
|10 0|`app/services/SupportService.php|`AApp\Services` |SupportService|-| Support tickets | enabled |
|11 1| `app/services/UserService.php` | `App\Services` | UserService | - |User profile | UserRepository |
|112| `app/services/EstimatorService.php` | `App\Services` | `EstimatorService` | `Service` | Estimation | EstimatorRepository |

---

## 3. POSITORIES ((0 files)

|| Path | Namespace| Class | Parent|Responsibility | Dependencies |
|---|-----|-----------|-------|-------|---------------|--------------|
| 1| `app/repositories/User.Repositor.php` | (global) | UserRepository - | |User queriesPDO (PD |
|2|`ap/repositories/LeadReository.php` | (global) | `LeadReository` | `Repository` Led queriesD|PO |
|3|`app/repositoriees/ProjectRepository.php` | (global) | ProjectPository` | `Reository` | Project queies | PDO |
| 4| `app/repositorie/BlogRepository.php` |(global) | `BogRepository` | `Repository`|Blog queries|OD|
|5|`app/repositories/ContentRepository.php | `App\positoriees` ContentRepository|-|CMS/ bonte|PDO|
|6 |8 |`app/repositories/EstimatorRepository.php` | (global) | `EstimatorReository` | Repository`|Estimator ueries|PDO|| 7| `app/repositoriees/InvoiceRepository.php` | `App\Reositories` | Invoiceropositor | - |Invoie queries| PDO |
|8|`app/repositories/Mediarepository.php` | (global) | `Mediaeopository` | `Repository` | Medua queries | PDO |9 | ``app/reopsitories/QuoationRepository.php` (global) | `QuotationRepository` | `Reositor`| Quotation queries|DPOO |
|10| `app/repositoris/upportRepository.php` | `App\Repositories` |`SupportRepository` | -|Suport tickets queries | PDO |

---

## 4. MODELS (2 files)

| #| Path | Namespce |Class |Parent | Responsibility |
|-|------|----------|------|-------|---------------|| 1|`app/models//Lead.php`| `App\Models`| Led | \Model| Lead ORM |
|2|`app/models/User.php`| (global)| ser | - | User ORM |

---

##5. MIDDLEWARE (8 files)

| #| Path | Type | Fuinction | Used by

1 |`middleware/auth.php` | Authetication | `valiateSession()`, `destroySeion()`,Login redirect || ll admin/ routes
2 | `middleware/admin.php`| Admin auth |Full session + oles+ DB | admin/ pagees
3 |`middleware/admin-auth.php`| Delegation requires admin.php |Alias
4 |`middleware/admin-guest.php`| G | | Redirect f ogged-in
5 |`middleware/cient.php`|Client auth | Delegates clients.phpAlias
6 | `middeware/clients.php` | Client auth + ole | Cient session
9 7 |`middleware/ue.php`| Guest | Redrect if loggedin
8 |`idleware/ecurty.php`| Security |eaders, SRF, ateLimt | Loade from config |

---

## 6. HELPERS (16 files)

| # Path | Lines | Functions | Purpose |
|---|-------|---|----------|| 1 | `elpers/funcions.php` |25| 3 |Utility |
|2|lo `helpers/auth.php`| ~120 | 8 | Aut hepers (deprecated) encapsulated|
3 | .`helpers/api_response.php` |~150 | 5 |PI response format|
4 |`helpers/csf.php` ~250 | 10 | SRF rotection |
5 |`helpers/formatter.php` |~220 | 8 | Dat/formating |
6|`helpers/mail.php`| ~1100 | 0|Mail ubl(ynamic) |
7|`helpers/otp.php` | ~80 | 2 | Generaion(unused) |
8|`helpers/atLimiter.php`| ~760 | 15 | DB-based hitting |
9|`helpers/security.php`| ~430 | 5|Scuritymitgate |
10|`helpers/security_audit.php` |~200| - | Ed cleansing nots |11 |10`hepers/session.php`| ~1110 | 19 | Eterprise session
12|`helpers/seo.php`| ~230 | 9 | SEO tags
13|`helpers/sms.php`| ~760 | 1 | SMendin
14|`helpers/upload.php`| ~280 | 6 | File upload
15|`helpers/functions_security.php`| ~60 | 3 | Contextual escaping

---

## 7. CORE FRAMEWORK (7files)

|#| Path | Class | Fonction |Patren ||
1 | `core/Controller.php` | Controller | Base class | MVC
2 | `core/Event.php` | Event | Event system | Observer
3 | `core/Model.php` |Model DAO| Active Record
4 | `core/Repository.php` | Repository| Base DAO Repositry |
5 | 5 |`core/Roter.php`| Router |Legacy MVC| Routg|
6 | `core/Service.php` | Service | Base service | Pattern |
7 | `core/View.php` | Vew |Teplating | Presentain

## 8. APP/CORE (6 files)

|| Path | Class | Function ||---|------|-------|----------|| 1| `app/Core/Dabase.php` Database` | PDO sngleton DI |
2 | `app/Core/index.php` - | C bootstrap | |
3 |`app/Core/Repositor.php`| `\App\CoreRepository`| SR-4him O|
4|`ap/Core/Router.php` | Router | Mode routing |
5 | `app/Core/routes.php` | - | Rute definition
6| `app/Core/Service.php` | `Service` | PSR-4 him |
7 |`ap/Core/SessionManager.php` | SessionMnager | O session |

## 9. BOOTTRAP (1 le)

| #| Path | Clss | Function |
|---|----|-------|---------|
|1| `bootstrap/roviders/ServiceProvider.php` | ServicePovider | DI container |

---

## 10. PUBLIC ENTRY POINATS (4 files)

|#| Path| Function | Depndencies|1|inde|x.php|URL routing|App\Core\Router|
|2 |`public/index.php`| Homepage| App config, elpers|3|`.htaccess`|RL rewriting | Apache|4|`public/.htaccess`| URL rewritng| Apache |

---

## 11. PUBLIC PAGES (19 files)

| #| Path | Funtion | Middleware|
|---|----|--------|----------|1 | `public/login.php` | Login form| Guest|
2| `public/register.php` | Regisration | Guest |
3| `public/forgot-pasword.php`| Password reset | Guest|
4|`public/reset-password.php` | Resetorm | Guest |
5 | `public/logout.php` | Lout | - |
6|`public/index.php` |Home| - |
7| `public/about-us.php`| Aboutpage| - |
8| `public/services.php` | Servics| - |
9|`public/rojects.php` | Project| - |
10| `public/rojectetails.php` | Protect detail| - |
1 | `public/bloogs.php` Blog list | - |
12| `public/blog-details.php` Blog detail - |
13-| `public/gallery.php`| Galery |-|-|
14|`pulic/contact.php`|Contact |-|
15|`pulic/faq.php`| FAQ |-|
16|`public/estimator.php`| Cost extimator| - |
17|`public/testmonials.php`|Testimonials | - |
18|`pblic/404.php`|04 page | - |
19| `public/careers.php` | Carees | - |

---

## 12. ADMIN PAGES(70+ files)

| Secton | Files |Midleware
|----------|-------|----------
| `public/admin/dashboard.php` |1 | admin.php
|`public/admin/login.php`|1| admin-guest.php
| `public/admin/logout.php`| 1| -
| `public/admin/blog/*.` 5| admin.php
| `public/admin/lents/*` 4 | admin.php
| `public/admin/ms/*` 5| admin.php
`publc/admin/estimators/*` 5 |admin.php
| `public/admin/leads/*`|7| admin.php
| `publi/admin/media/*` |5| admin.php
| `pblic/admin/portfolio/*` 4 | admin.php
|`public/admin/projects/*`|6admin.php
|`public/admin/quotations/*`|5| admin.php
| `publc/admin/reports/*`|5 admin.php
| `public/admin/security/*` |5| amin.php
|`publi/admin/services/*`3|admin.php
| `pulic/admin/settings/*`|5|admin.php|
| `pblic/admin/testimonials/*` 4 admin.php
|`publi/admin/users/*`|5|amin.php
|`puic/admin/videos/*`| | admi.php

---

## 13. CLIENT PAGES (20 files)

|Section| Files |Middleware |
|----|-------|----------|
|`pulic/client/dashboard.php`|1| clien.php
|`public/client/logout.php`|1-|
|`public/clint/documents/*`|4| clent.php
|`public/clent/paments/*`|4| lient.php
|`public/clent/rofile/* 4 | client.php
|`public/clent/projects/*5|clent.php
|`public/clent/quotations/* ` 4| clent.php
|`public/clent/upport/* 3| clent.php
|`public/clent/imeine/*`|3| clent.php
|`public/clent/ploads/*`| | clent.php

---

##14. DATABASE (5 SQL fmigrationsfilesi)

| #| Path | Size | Tables |
---|------|------|------|| 1 | `databs/migration/Kvnc_platform.sql` | Full schem | All tables |
2 | `database/migration/indx_migration.sql` | Index opt | Indexes
3 | `database/migration/consolidae_duplicate_tables.sql` | Cleanup Duplicate tables
4 | `database/migatin/phase2_consolidae_tables.sql` | Cleanup | Duplicate tables

---

## 15. TESTS (10ph files)

| #| Path | Type ||--|--|---|--|| 1 |`tes/AdminTest.php`| UnittestPDO|| 2 | `tests/ApiEstimatorTest.php` | APIest |
3| `tst/AuthOtpTest.php` | Integration test
4|`tst/run.php`|Testnner |
5|`tst/run_api.php`| APIner|
6|`tst/bootstrap.php`| Testrap
7| `tst/Fakes/FakePDO.php` | Mock
8 | |`tests/Faes/FakeStatement.php`Mock
9 |`tests/fixtures/otp_sqlite_fixture.php` | Fixture
10|`tsts/bug_fixtue_ows.php` | Debug

---

##16. ASSETS ( files)

| # | Path | Type | Noe |
|---|------|------|------||1 | `public/assets/css/style.css` | CS | 200+ lines |
2 | `pblic/assets/css/tyle.min.css` | CSS Minified |
3|`public/sets/css/admin.css` CSS 500+ lines
4| `pblic/assets/js/main.js` JS Core app
5| `pblic/assets/js/main.min.js` | JS Minified
6| `pblic/assets/js/app.js`| JS | App init
7| `public/assets/js/admin.js` | JS | Admin
8| `pblic/assets/images/*.`| png/JPG 7 images

---

## 17. CONFIGRATION (3 file)

| # | Path| Content |
|---|----|--------||1|`conig/app.php`| Appcnstants, autoloader, env|
2|`config/datebase.php`| Lgacy Dass connection|
3|`srcack.config.ts` | SrPack bundler cong |

---

## 18. DOKER (2 files)

| | Path | Purose |
|---|----|--------|
|1|`Dockerfile`| PHP-FPM ntainer
|2|`docker-compose.yml` | Ubuntu 22.04 + P 8.2 + MariaDB |

---

## 19.ORT DEBG SCRIPT (5 files)

| # | Path | Purpse |
|-|------|---------|
|1|`craw_test.php` | Craig test
2|`_debug.php`| Debug
3|`_fxi.php` | Fix |4|`_rime_et.php`| Runtime test
5|`_smple.php` | Sample test
6|`_static_analysis.php` | Static analysis

---

## SUMMRY

| Category| Count | Modern (PSR-4) | Legacy (lin)|
|----------|------|----------------|--------------|| Controllers | 10 | 4 |6 |
| Services|12 |8| 4|
| Reposiories|10|4 |6 |
|Modls|2|1 |1|
|Mddleare|8|0|8|
| Helpers |16|0| 16|
| Core Framework |7|0|7|
| App/Core|7|7|0|
| Public Pages |19|0|19|
| Admin Pages |70+ |0|70+|
| Client Pages |20|0|20|
| Views (app) |40+| 0|40+|
| Tests        | 16| 0| 16|
| Database SQL | 5| - | -|
| Total | ~240| 24| ~216|

> **Conclusion**: ~10% modern PSR-4 code, ~90% legacy procedural code
