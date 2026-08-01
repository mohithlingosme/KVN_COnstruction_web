# Security Analysis (Heuristic)

## Security Components

| Component | Status |
|---|---|
| helpers\csrf.php | Present |
| helpers\security.php | Present |
| helpers\auth.php | Present |
| helpers\session.php | Present |
| helpers\rateLimiter.php | Present |
| helpers\upload.php | Present |
| helpers\otp.php | Present |
| helpers\functions_security.php | Present |
| helpers\functions.php | Present |
| middleware\auth.php | Present |
| middleware\admin.php | Present |
| middleware\client.php | Present |
| middleware\guest.php | Present |
| middleware\admin-auth.php | Present |
| middleware\admin-guest.php | Present |
| middleware\clients.php | Present |
| config\app.php | Present |

## Potential Security Issues (97 matches)

| File | Issue Type |
|---|---|
| _debug.php | include/require with variable |
| _fix.php | file_get_contents with user input |
| app\controllers\admin\AdminController.php | Possible SQL injection (direct query) |
| app\models\Lead.php | Possible SQL injection (direct query) |
| config\app.php | include/require with variable |
| config\database.php | exec/system/shell_exec/passthru |
| core\Controller.php | include/require with variable |
| core\Controller.php | extract() usage |
| core\Model.php | Possible SQL injection (direct query) |
| core\Router.php | include/require with variable |
| core\Router.php | extract() usage |
| core\View.php | include/require with variable |
| core\View.php | extract() usage |
| helpers\functions.php | include/require with variable |
| helpers\security_audit.php | exec/system/shell_exec/passthru |
| helpers\security_audit.php | Possible SQL injection (direct query) |
| helpers\security_audit.php | extract() usage |
| public\estimator.php | exec/system/shell_exec/passthru |
| public\admin\blogs\categories.php | exec/system/shell_exec/passthru |
| public\admin\blogs\comments.php | exec/system/shell_exec/passthru |
| public\admin\blogs\tags.php | exec/system/shell_exec/passthru |
| public\admin\cms\about.php | Possible SQL injection (direct query) |
| public\admin\cms\contact.php | Possible SQL injection (direct query) |
| public\admin\cms\faq.php | Possible SQL injection (direct query) |
| public\admin\cms\homepage.php | Possible SQL injection (direct query) |
| public\admin\cms\seo.php | Possible SQL injection (direct query) |
| public\admin\estimators\packages.php | exec/system/shell_exec/passthru |
| public\admin\estimators\pricing.php | exec/system/shell_exec/passthru |
| public\admin\media\documents.php | Possible SQL injection (direct query) |
| public\admin\media\images.php | Possible SQL injection (direct query) |
| public\admin\media\index.php | Possible SQL injection (direct query) |
| public\admin\media\videos.php | Possible SQL injection (direct query) |
| public\admin\portfolio\featured.php | Possible SQL injection (direct query) |
| public\admin\portfolio\index.php | Possible SQL injection (direct query) |
| public\admin\quotations\pdf.php | include/require with variable |
| public\admin\reports\estimators.php | Possible SQL injection (direct query) |
| public\admin\reports\leads.php | Possible SQL injection (direct query) |
| public\admin\reports\projects.php | Possible SQL injection (direct query) |
| public\admin\reports\quotations.php | Possible SQL injection (direct query) |
| public\admin\reports\revenue.php | Possible SQL injection (direct query) |
| public\admin\security\audit-logs.php | Possible SQL injection (direct query) |
| public\admin\security\blocked-users.php | Possible SQL injection (direct query) |
| public\admin\security\login-attempts.php | Possible SQL injection (direct query) |
| public\admin\security\logs.php | Possible SQL injection (direct query) |
| public\admin\security\sessions.php | Possible SQL injection (direct query) |
| public\admin\settings\general.php | Possible SQL injection (direct query) |
| public\admin\settings\integrations.php | Possible SQL injection (direct query) |
| public\admin\settings\security.php | Possible SQL injection (direct query) |
| public\admin\settings\seo.php | Possible SQL injection (direct query) |
| public\admin\settings\sms.php | Possible SQL injection (direct query) |
| public\admin\testimonials\approvals.php | Possible SQL injection (direct query) |
| public\admin\testimonials\featured.php | Possible SQL injection (direct query) |
| public\admin\testimonials\index.php | Possible SQL injection (direct query) |
| public\admin\testimonials\videos.php | Possible SQL injection (direct query) |
| public\admin\videos\categories.php | Possible SQL injection (direct query) |
| public\admin\videos\index.php | Possible SQL injection (direct query) |
| public\client\dashboard.php | Possible SQL injection (direct query) |
| public\client\documents\agreements.php | Possible SQL injection (direct query) |
| public\client\documents\downloads.php | Possible SQL injection (direct query) |
| public\client\documents\index.php | Possible SQL injection (direct query) |
| public\client\documents\permits.php | Possible SQL injection (direct query) |
| public\client\payments\index.php | Possible SQL injection (direct query) |
| public\client\payments\invoices.php | Possible SQL injection (direct query) |
| public\client\payments\receipts.php | Possible SQL injection (direct query) |
| public\client\payments\transactions.php | Possible SQL injection (direct query) |
| public\client\profile\edit.php | Possible SQL injection (direct query) |
| public\client\profile\index.php | Possible SQL injection (direct query) |
| public\client\profile\notifications.php | Possible SQL injection (direct query) |
| public\client\profile\password.php | Possible SQL injection (direct query) |
| public\client\projects\gallery.php | Possible SQL injection (direct query) |
| public\client\projects\index.php | Possible SQL injection (direct query) |
| public\client\projects\milestones.php | Possible SQL injection (direct query) |
| public\client\projects\updates.php | Possible SQL injection (direct query) |
| public\client\projects\view.php | Possible SQL injection (direct query) |
| public\client\quotations\approvals.php | Possible SQL injection (direct query) |
| public\client\quotations\downloads.php | Possible SQL injection (direct query) |
| public\client\quotations\index.php | Possible SQL injection (direct query) |
| public\client\support\create-ticket.php | Possible SQL injection (direct query) |
| public\client\support\messages.php | Possible SQL injection (direct query) |
| public\client\support\tickets.php | Possible SQL injection (direct query) |
| public\client\timeline\index.php | Possible SQL injection (direct query) |
| public\client\timeline\schedules.php | Possible SQL injection (direct query) |
| public\client\uploads\feedback.php | Possible SQL injection (direct query) |
| public\client\uploads\images.php | Possible SQL injection (direct query) |
| public\client\uploads\testimonials.php | Possible SQL injection (direct query) |
| public\client\uploads\videos.php | Possible SQL injection (direct query) |
| tests\AdminTest.php | exec/system/shell_exec/passthru |
| tests\AuthOtpTest.php | exec/system/shell_exec/passthru |
| tests\AuthOtpTest.php | Possible SQL injection (direct query) |
| tests\debug_fixture_rows.php | Possible SQL injection (direct query) |
| tests\debug_otp_select.php | Possible SQL injection (direct query) |
| tests\run.php | include/require with variable |
| tests\run_api.php | exec/system/shell_exec/passthru |
| tests\run_minimal_test.php | exec/system/shell_exec/passthru |
| tests\run_minimal_test.php | Possible SQL injection (direct query) |
| tests\fixtures\otp_sqlite_fixture.php | exec/system/shell_exec/passthru |
| tests\fixtures\otp_sqlite_fixture.php | Possible SQL injection (direct query) |

## .env Exposure

- .env file present: Yes (WARNING: ensure it is excluded from version control)
- .env in .gitignore: Yes

## CSRF Protection

- CSRF implementation found: Yes

## Session Security

- Session usage found: Yes
- Session security configuration: Yes

## Password Hashing

- Modern password hashing (bcrypt/argon2): Yes
- OTP implementation: Yes
- Rate limiting: Yes

## Security Headers

| Header | Status |
|---|---|
| X-Frame-Options | Present |
| X-Content-Type-Options | Present |
| X-XSS-Protection | Present |
| Strict-Transport-Security | Present |
| Content-Security-Policy | Present |
| Referrer-Policy | Present |
| Permissions-Policy | Present |

## Upload Security

- Upload directory: Present
- Files in uploads: 1
- PHP files in uploads: 0 

## Directory Traversal

- Checking for path traversal patterns in PHP files...
- Files with path manipulation: 159

## Recommendations
1. Implement CSRF tokens on all forms
2. Use prepared statements for all database queries
3. Add Content-Security-Policy header
4. Ensure .env is in .gitignore
5. Use password_hash()/password_verify() for all password operations
6. Implement rate limiting on login/OTP endpoints
7. Remove any hardcoded credentials from source code
8. Prevent PHP file execution in uploads directory
9. Add security headers via .htaccess or PHP
10. Validate and sanitize all user inputs
