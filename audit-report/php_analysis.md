# PHP Analysis

## Summary

| Metric | Value |
|---|---|
| PHP Files Scanned | 216 |
| Syntax Errors | 0 |
| Duplicate Functions | 8 |
| Duplicate Classes | 0 |
| Namespaces Found | 2 |
| Deprecated Function Usages | 73 |
| Missing Includes/Requires | 0 |

## Duplicate Functions

| Function | Files |
|---|---|
| validateCsrf | helpers\csrf.php; tests\bootstrap.php |
| sendAdminLoginAlert | helpers\mail.php; tests\bootstrap.php |
| sendOtpSms | helpers\sms.php; tests\bootstrap.php |
| sanitize | helpers\security.php; tests\bootstrap.php |
| sendOtpEmail | helpers\mail.php; tests\bootstrap.php |
| incrementRateLimit | helpers\rateLimiter.php; tests\bootstrap.php |
| destroySession | helpers\session.php; tests\bootstrap.php |
| logAdminAction | helpers\security.php; tests\bootstrap.php |

## Namespaces

| Namespace | Files |
|---|---|
| App\Security | 1 file(s) |
| App\Models | 1 file(s) |

## Deprecated Function Usage

| File | Function |
|---|---|
| app\controllers\admin\ProjectController.php | each |
| config\app.php | each |
| core\Controller.php | each |
| helpers\rateLimiter.php | each |
| helpers\security_audit.php | each |
| helpers\upload.php | each |
| public\about-us.php | each |
| public\blog-details.php | each |
| public\blogs.php | each |
| public\contact.php | each |
| public\estimator.php | each |
| public\index.php | each |
| public\project-details.php | each |
| public\projects.php | each |
| public\admin\dashboard.php | each |
| public\admin\blogs\categories.php | each |
| public\admin\blogs\comments.php | each |
| public\admin\blogs\edit.php | each |
| public\admin\blogs\index.php | each |
| public\admin\blogs\tags.php | each |
| public\admin\clients\feedback.php | each |
| public\admin\clients\index.php | each |
| public\admin\clients\payments.php | split |
| public\admin\clients\payments.php | each |
| public\admin\clients\projects.php | each |
| public\admin\clients\view.php | each |
| public\admin\cms\faq.php | each |
| public\admin\cms\seo.php | each |
| public\admin\estimators\index.php | each |
| public\admin\estimators\materials.php | each |
| public\admin\estimators\packages.php | each |
| public\admin\estimators\pricing.php | each |
| public\admin\estimators\requests.php | each |
| public\admin\leads\edit.php | each |
| public\admin\leads\index.php | split |
| public\admin\leads\index.php | each |
| public\admin\leads\pipeline.php | split |
| public\admin\leads\pipeline.php | each |
| public\admin\leads\view.php | each |
| public\admin\media\documents.php | each |
| public\admin\media\images.php | each |
| public\admin\media\index.php | each |
| public\admin\media\videos.php | each |
| public\admin\portfolio\featured.php | each |
| public\admin\portfolio\index.php | each |
| public\admin\projects\create.php | each |
| public\admin\projects\edit.php | each |
| public\admin\projects\gallery.php | each |
| public\admin\projects\index.php | split |
| public\admin\projects\index.php | each |
| public\admin\projects\milestones.php | each |
| public\admin\projects\view.php | each |
| public\admin\quotations\approvals.php | each |
| public\admin\quotations\create.php | each |
| public\admin\quotations\edit.php | each |
| public\admin\quotations\index.php | each |
| public\admin\quotations\pdf.php | each |
| public\admin\settings\smtp.php | each |
| public\admin\testimonials\approvals.php | each |
| public\admin\testimonials\featured.php | each |
| public\admin\testimonials\index.php | each |
| public\admin\testimonials\videos.php | each |
| public\admin\users\index.php | each |
| public\admin\users\view.php | each |
| public\admin\videos\categories.php | each |
| public\admin\videos\index.php | each |
| public\client\timeline\progress.php | each |
| routes\api_estimator.php | each |
| tests\ApiEstimatorTest.php | each |
| tests\debug_fixture_rows.php | each |
| tests\debug_otp_select.php | each |
| tests\run.php | each |
| tests\run_minimal_test.php | each |

## Recommendations
1. Fix all syntax errors before deployment
2. Remove duplicate function and class definitions
3. Replace deprecated functions with modern alternatives
4. Resolve all missing include/require statements
5. Adopt PSR-4 autoloading for better namespace management
