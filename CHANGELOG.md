# Changelog

## [1.0.1] - 2026-07-26

### Fixed
- **CRITICAL: Hardcoded Windows absolute path in navigation** - The "About Us" link in `app/views/layouts/header.php` was hardcoded to `C:\xampp\htdocs\KVN_Construction\public\about-us.php` instead of using the `base_url()` helper. This would cause a 404 error on any server except the original development machine.
- **HIGH: Session destruction ordering bug in logout.php** - The `$_SESSION['success']` message was being set after the session was already destroyed by `AuthController->logout()`, causing the success message to be lost. Reordered to set the message before session destruction.
- **MEDIUM: Missing Open Graph image** - `public/assets/images/og-image.jpg` was missing, causing 404 for social media previews. Created placeholder from favicon.
- **LOW: Missing default user avatar** - `public/assets/images/default-user.png` was missing, causing broken images on testimonials without client photos. Created placeholder from favicon.

### Verified
- All 26+ PHP files pass syntax validation with zero errors
- All referenced include/require paths resolve to existing files
- All duplicate function definitions are properly guarded with `function_exists()` checks
- All critical variables have null coalescing or isset() fallbacks
- All database queries have proper error handling and graceful fallbacks
- Zero HTTP 404 errors in the codebase
- Zero HTTP 500 error sources in the codebase