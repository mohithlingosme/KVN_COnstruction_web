# SECURITY IMPLEMENTATION — REQUIRED CODEBASE & DATABASE CHANGES

Based on your current architecture and security roadmap 

This is the COMPLETE list of:

* files to create
* files to modify
* database changes
* middleware changes
* auth flow changes
* admin isolation changes
* brute force logic
* logging system

for production-grade security.

---

# 1. DATABASE CHANGES REQUIRED

# REQUIRED SQL CHANGES

Run this in phpMyAdmin/XAMPP.

---

## USERS TABLE SECURITY UPGRADE

```sql
ALTER TABLE users

ADD COLUMN failed_attempts INT DEFAULT 0 AFTER password,

ADD COLUMN locked_until DATETIME NULL AFTER failed_attempts,

ADD COLUMN last_login DATETIME NULL AFTER locked_until,

ADD COLUMN last_activity DATETIME NULL AFTER last_login,

ADD COLUMN last_ip VARCHAR(45) NULL AFTER last_activity,

ADD COLUMN session_token VARCHAR(255) NULL AFTER last_ip,

ADD COLUMN otp_code VARCHAR(10) NULL AFTER session_token,

ADD COLUMN otp_expires_at DATETIME NULL AFTER otp_code,

ADD COLUMN email_verified TINYINT(1) DEFAULT 0 AFTER otp_expires_at,

ADD COLUMN remember_token VARCHAR(255) NULL AFTER email_verified;
```

---

# CREATE SECURITY LOGS TABLE

```sql
CREATE TABLE security_logs (

    id INT PRIMARY KEY AUTO_INCREMENT,

    user_id INT NULL,

    event_type VARCHAR(100),

    event_level ENUM(
        'info',
        'warning',
        'critical'
    ) DEFAULT 'info',

    ip_address VARCHAR(45),

    user_agent TEXT,

    event_details TEXT,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    INDEX(user_id),

    INDEX(event_type),

    INDEX(created_at)
);
```

---

# CREATE LOGIN ATTEMPTS TABLE

```sql
CREATE TABLE login_attempts (

    id INT PRIMARY KEY AUTO_INCREMENT,

    email VARCHAR(255),

    ip_address VARCHAR(45),

    user_agent TEXT,

    status ENUM(
        'success',
        'failed',
        'blocked'
    ) DEFAULT 'failed',

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    INDEX(email),

    INDEX(ip_address),

    INDEX(created_at)
);
```

---

# CREATE PASSWORD RESET TABLE

```sql
CREATE TABLE password_resets (

    id INT PRIMARY KEY AUTO_INCREMENT,

    user_id INT,

    otp_code VARCHAR(10),

    expires_at DATETIME,

    verified TINYINT(1) DEFAULT 0,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id)
    REFERENCES users(id)
    ON DELETE CASCADE
);
```

---

# 2. NEW FILES TO CREATE

# CRITICAL SECURITY FILES

---

## SESSION SECURITY

```text
/helpers/session.php
```

Handles:

* secure session start
* timeout
* fingerprint validation
* regeneration
* logout destroy

---

## CSRF

```text
/helpers/csrf.php
```

Already exists.

Need:

* enforce validation globally

---

## RATE LIMITER

```text
/helpers/rateLimiter.php
```

Already exists.

Need:

* login-specific rules
* admin rules
* OTP rules

---

## SECURITY LOGGER

```text
/helpers/security.php
```

Need additions:

* DB logging
* suspicious activity logging
* admin audit logging

---

## MAIL SYSTEM

```text
/helpers/mail.php
```

Need additions:

* OTP emails
* suspicious login alerts
* admin login alerts

---

# MIDDLEWARE FILES

---

## AUTH

```text
/middleware/auth.php
```

Checks:

* logged in
* session timeout
* fingerprint

---

## ADMIN

```text
/ middleware/admin.php
```

Checks:

* admin role
* admin session
* isolated admin auth

---

## CLIENT

```text
/middleware/client.php
```

Checks:

* client role

---

## GUEST

```text
/middleware/guest.php
```

Blocks logged-in users from auth pages.

---

# AUTHENTICATION FILES

---

## PUBLIC LOGIN

```text
/public/login.php
```

CLIENT ONLY.

---

## ADMIN LOGIN

```text
/public/admin/login.php
```

ADMIN ONLY.

Critical isolation layer.

---

## LOGOUT

```text
/public/logout.php
```

Destroy secure session.

---

## OTP RESET FLOW

```text
/public/forgot-password.php
/public/verify-otp.php
/public/reset-password.php
```

---

# CONTROLLER CHANGES

---

## AUTH CONTROLLER

```text
/app/controllers/AuthController.php
```

Must handle:

* login
* logout
* session regenerate
* brute force
* OTP
* role auth
* lockouts
* logging

---

# MODEL CHANGES

---

## USER MODEL

```text
/app/models/User.php
```

Need:

* lock account methods
* failed attempt tracking
* OTP methods
* session token methods

---

# 3. SESSION SECURITY CHANGES

# REQUIRED IN `/helpers/session.php`

---

## SECURE SESSION START

```php
ini_set('session.cookie_httponly', 1);

ini_set('session.use_only_cookies', 1);

ini_set('session.use_strict_mode', 1);
```

---

## SESSION REGENERATION

After login:

```php
session_regenerate_id(true);
```

---

## SESSION TIMEOUT

```php
30 mins admin
60 mins client
```

---

## SESSION FINGERPRINT

Store:

```php
IP + USER AGENT HASH
```

Verify every request.

---

# 4. ADMIN LOGIN ISOLATION

# IMPORTANT CHANGE

---

# REMOVE ADMIN LOGIN FROM

```text
/public/login.php
```

---

# CREATE SEPARATE

```text
/public/admin/login.php
```

---

# ADMIN SESSION VARIABLES

```php
$_SESSION['is_admin'] = true;

$_SESSION['admin_id'];

$_SESSION['admin_fingerprint'];
```

---

# ADMIN ROUTES MUST USE

```php
require '../middleware/admin.php';
```

---

# 5. BRUTE FORCE PROTECTION

# REQUIRED CHANGES

---

# LOGIN FLOW

## BEFORE LOGIN

Check:

* failed_attempts
* locked_until

---

## AFTER FAILED LOGIN

Increment:

```php
failed_attempts + 1
```

---

## AFTER 5 FAILURES

```php
locked_until = NOW() + 15 MINUTES
```

---

## AFTER SUCCESS LOGIN

Reset:

```php
failed_attempts = 0
locked_until = NULL
```

---

# ALSO IMPLEMENT ON

| Feature        | Required |
| -------------- | -------- |
| admin login    | YES      |
| OTP verify     | YES      |
| password reset | YES      |

---

# 6. RATE LIMITING CHANGES

# APPLY TO

| Endpoint    | Limit   |
| ----------- | ------- |
| login       | 5/5min  |
| admin login | 3/10min |
| OTP         | 3/10min |
| contact     | 5/hour  |
| estimator   | 20/hour |

---

# IMPLEMENT IN

```text
/helpers/rateLimiter.php
```

AND

```text
/app/controllers/AuthController.php
```

---

# 7. SECURITY LOGGING CHANGES

# LOG THESE EVENTS

| Event             | Severity |
| ----------------- | -------- |
| failed login      | warning  |
| successful login  | info     |
| admin login       | critical |
| session mismatch  | critical |
| brute force       | critical |
| OTP verify        | info     |
| password reset    | warning  |
| suspicious upload | critical |

---

# STORE IN

```text
security_logs
```

table.

---

# 8. XSS PROTECTION CHANGES

# MUST CHANGE ACROSS ENTIRE CODEBASE

---

# NEVER

```php
echo $user['name'];
```

---

# ALWAYS

```php
echo escape($user['name']);
```

---

# RICH TEXT AREAS

Need whitelist:

```php
strip_tags(
    $content,
    '<p><b><strong><ul><li><br>'
);
```

---

# 9. SQL INJECTION PROTECTION

# ALREADY GOOD

Because you use:

* PDO
* prepared statements

---

# BUT VERIFY ALL FILES

Search for:

```php
$query = "SELECT ...
```

with direct interpolation.

---

# NEVER USE

```php
$id = $_GET['id'];

$sql = "SELECT * FROM users WHERE id = $id";
```

---

# 10. FILE UPLOAD SECURITY CHANGES

# UPDATE

```text
/helpers/upload.php
```

---

# ADD

| Protection                | Required |
| ------------------------- | -------- |
| MIME validation           | YES      |
| extension whitelist       | YES      |
| random filenames          | YES      |
| file size limit           | YES      |
| image-only validation     | YES      |
| dangerous extension block | YES      |

---

# BLOCK

```text
.php
.phtml
.exe
.js
.sh
```

---

# 11. GLOBAL APP SECURITY CHANGES

# UPDATE

```text
/config/app.php
```

---

# ADD

```php
securityHeaders();
```

AND

```php
require_once '../helpers/security.php';
```

---

# 12. ADMIN PANEL SECURITY

# APPLY TO ALL

```text
/public/admin/*
```

---

# REQUIRE

```php
require '../../middleware/admin.php';
```

---

# 13. CLIENT PANEL SECURITY

# APPLY TO ALL

```text
/public/client/*
```

---

# REQUIRE

```php
require '../../middleware/client.php';
```

---

# 14. OTP SYSTEM CHANGES

# REQUIRED TABLES

Already listed above.

---

# OTP FLOW

```text
forgot-password.php
↓
send OTP
↓
verify-otp.php
↓
reset-password.php
```

---

# OTP RULES

| Rule         | Value    |
| ------------ | -------- |
| OTP expiry   | 10 min   |
| OTP attempts | 3        |
| OTP length   | 6 digits |

---

# 15. FILES TO MODIFY IMMEDIATELY

# HIGH PRIORITY

| File                                  | Action  |
| ------------------------------------- | ------- |
| `/helpers/session.php`                | CREATE  |
| `/middleware/auth.php`                | CREATE  |
| `/middleware/admin.php`               | CREATE  |
| `/middleware/client.php`              | CREATE  |
| `/middleware/guest.php`               | CREATE  |
| `/app/controllers/AuthController.php` | UPDATE  |
| `/app/models/User.php`                | UPDATE  |
| `/config/app.php`                     | UPDATE  |
| `/public/login.php`                   | REBUILD |
| `/public/admin/login.php`             | CREATE  |
| `/public/logout.php`                  | CREATE  |

---

# CURRENT SECURITY STATUS

| Layer           | Status     |
| --------------- | ---------- |
| SQL Injection   | ✅ Strong   |
| CSRF            | 🟡 Partial |
| XSS             | 🟡 Partial |
| Sessions        | ❌ Pending  |
| Fingerprinting  | ❌ Pending  |
| Admin Isolation | ❌ Pending  |
| Rate Limiting   | 🟡 Partial |
| Brute Force     | ❌ Pending  |
| Logging         | 🟡 Partial |
| OTP Security    | ❌ Pending  |

---

# NEXT IMPLEMENTATION ORDER

## 1️⃣

```text
/helpers/session.php
```

## 2️⃣

```text
/middleware/auth.php
```

## 3️⃣

```text
/middleware/admin.php
```

## 4️⃣

```text
/app/controllers/AuthController.php
```

## 5️⃣

```text
/public/admin/login.php
```
