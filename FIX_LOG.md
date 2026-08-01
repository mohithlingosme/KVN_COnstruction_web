# KVN Construction - Fix Log

## Fix 1: Hardcoded Windows Path in Navigation
**Date:** 2026-07-26
**Severity:** CRITICAL - Would cause 404 on any server except the original developer's machine
**File:** `app/views/layouts/header.php`
**Line:** 279
**Before:**
```php
<a class="nav-link" href="C:\xampp\htdocs\KVN_Construction\public\about-us.php">
```
**After:**
```php
<a class="nav-link <?php echo ($currentPage == 'about-us.php') ? 'active' : ''; ?>" href="<?php echo base_url('about-us.php'); ?>">
```
**Root Cause:** Developer hardcoded their local Windows absolute path instead of using the base_url() helper
**Impact:** The "About Us" navigation link would 404 on any server except the original development machine

---

## Fix 2: Missing Asset - og-image.jpg
**Date:** 2026-07-26
**Severity:** MEDIUM - Would cause 404 for Open Graph image
**File:** `app/views/layouts/header.php` (referenced at line 16)
**Path:** `public/assets/images/og-image.jpg`
**Fix:** Created placeholder image from favicon.png
**Root Cause:** Asset file was never created or was deleted

---

## Fix 3: Missing Asset - default-user.png
**Date:** 2026-07-26
**Severity:** LOW - Would show broken image on testimonials without client photo
**File:** `public/index.php` (referenced at line 405)
**Path:** `public/assets/images/default-user.png`
**Fix:** Created placeholder image from favicon.png
**Root Cause:** Asset file was never created or was deleted

---

## Fix 4: Session Destruction Ordering Bug in logout.php
**Date:** 2026-07-26
**Severity:** HIGH - Would cause PHP warning/notice and fail to display success message
**File:** `public/logout.php`
**Lines:** 19-27
**Before:**
```php
$controller = new AuthController($conn);
$controller->logout();  // Destroys session

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_name(SESSION_NAME);
    session_start();
}

$_SESSION['success'] = 'Logged out successfully.';  // Too late - session already destroyed
```
**After:**
```php
// Set success message BEFORE destroying session
$_SESSION['success'] = 'Logged out successfully.';

// Destroy the session via AuthController if available
$authControllerPath = ROOT_PATH . '/app/controllers/auth/AuthController.php';
if (file_exists($authControllerPath)) {
    require_once $authControllerPath;
    $controller = new AuthController($conn);
    $controller->logout();
} else {
    destroySession();
}
```
**Root Cause:** The logout() method destroys the session, but the code tried to set $_SESSION['success'] after the session was already destroyed. The subsequent session_start() would create a new empty session, losing the success message.
**Impact:** Users would be redirected to login page without seeing "Logged out successfully" message