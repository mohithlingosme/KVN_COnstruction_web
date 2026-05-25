# SECURITY SYSTEM — FILE LEVEL CHANGES REQUIRED

## Before Database Modifications

Based on your current build state and implemented auth architecture, these are the EXACT file changes required to fully complete the security system.

Roadmap reviewed from your uploaded implementation plan. 

---

# 1️⃣ `/config/app.php`

# REQUIRED CHANGES

## ADD

| Feature                 | Purpose             |
| ----------------------- | ------------------- |
| security headers        | anti-clickjacking   |
| session helper include  | secure sessions     |
| csrf helper include     | CSRF validation     |
| security helper include | sanitization        |
| timezone                | session consistency |
| secure constants        | global config       |

---

# REQUIRED ADDITIONS

```php id="jlwm191"
require_once ROOT_PATH . '/helpers/session.php';

require_once ROOT_PATH . '/helpers/security.php';

require_once ROOT_PATH . '/helpers/csrf.php';
```

---

# ADD SECURITY HEADERS

```php id="jlwm192"
securityHeaders();
```

---

# ADD CONSTANTS

```php id="jlwm193"
define('SESSION_TIMEOUT', 3600);

define('ADMIN_SESSION_TIMEOUT', 1800);

define('OTP_EXPIRY_MINUTES', 5);
```

---

# 2️⃣ `/helpers/session.php`

# STATUS

✅ Mostly completed

---

# STILL NEEDS

| Feature                    | Status  |
| -------------------------- | ------- |
| session DB persistence     | pending |
| remember me support        | pending |
| concurrent session control | pending |
| admin device tracking      | pending |

---

# REQUIRED ADDITIONS

## STORE SESSION TOKEN

```php id="jlwm194"
$_SESSION['session_token']
```

---

# UPDATE `user_sessions`

Track:

* device
* IP
* last activity

---

# 3️⃣ `/helpers/security.php`

# NEEDS MAJOR UPGRADE

---

# REQUIRED FEATURES

| Feature              | Required |
| -------------------- | -------- |
| sanitize()           | YES      |
| escape()             | YES      |
| safeRichText()       | YES      |
| securityHeaders()    | YES      |
| logSecurityEvent()   | YES      |
| suspiciousActivity() | YES      |
| validateFileMime()   | YES      |
| secureFilename()     | YES      |

---

# ADD

## CSP HEADERS

```php id="jlwm195"
Content-Security-Policy
```

---

# ADD

## RICH TEXT SANITIZER

```php id="jlwm196"
safeRichText()
```

---

# ADD

## ADMIN AUDIT LOGGER

```php id="jlwm197"
logAdminAction()
```

---

# 4️⃣ `/helpers/csrf.php`

# CURRENT STATUS

🟡 Partial

---

# REQUIRED FEATURES

| Feature            | Required |
| ------------------ | -------- |
| token generation   | YES      |
| token regeneration | YES      |
| expiration         | YES      |
| validation         | YES      |
| one-time token     | YES      |

---

# REQUIRED FUNCTIONS

```php id="jlwm198"
generateCsrfToken()

validateCsrf()

csrfField()

refreshCsrf()
```

---

# ADD TOKEN EXPIRY

```php id="jlwm199"
30 minutes
```

---

# 5️⃣ `/helpers/rateLimiter.php`

# CURRENT STATUS

🟡 Partial

---

# REQUIRED FEATURES

| Feature            | Required |
| ------------------ | -------- |
| IP based limits    | YES      |
| route based limits | YES      |
| DB persistence     | YES      |
| OTP limits         | YES      |
| login limits       | YES      |
| admin limits       | YES      |

---

# REQUIRED LIMITS

| Action       | Limit   |
| ------------ | ------- |
| admin login  | 3/10min |
| client OTP   | 3/10min |
| contact form | 5/hour  |
| estimator    | 20/hour |

---

# REQUIRED FUNCTIONS

```php id="jlwm200"
checkRateLimit()

incrementRateLimit()

clearRateLimit()
```

---

# 6️⃣ `/helpers/upload.php`

# NEEDS SECURITY HARDENING

---

# REQUIRED FEATURES

| Feature              | Required |
| -------------------- | -------- |
| MIME validation      | YES      |
| extension whitelist  | YES      |
| image validation     | YES      |
| random filenames     | YES      |
| max size             | YES      |
| dangerous file block | YES      |

---

# BLOCK

```text id="jlwm201"
.php
.phtml
.js
.exe
.sh
```

---

# REQUIRED FUNCTIONS

```php id="jlwm202"
validateUpload()

secureUpload()

generateSecureFilename()
```

---

# 7️⃣ `/helpers/mail.php`

# REQUIRED CHANGES

---

# ADD

| Feature                    | Required |
| -------------------------- | -------- |
| OTP email                  | YES      |
| admin login alerts         | YES      |
| suspicious activity alerts | YES      |
| password reset             | YES      |

---

# REQUIRED FUNCTIONS

```php id="jlwm203"
sendOtpEmail()

sendAdminLoginAlert()

sendSecurityAlert()
```

---

# 8️⃣ `/helpers/otp.php`

# STATUS

✅ Mostly done

---

# STILL NEEDS

| Feature             | Required |
| ------------------- | -------- |
| OTP resend cooldown | YES      |
| OTP expiry cleanup  | YES      |
| OTP attempt lock    | YES      |
| resend counter      | YES      |

---

# ADD

```php id="jlwm204"
resendOtp()

expireOtp()

cleanupExpiredOtps()
```

---

# 9️⃣ `/helpers/sms.php`

# STATUS

✅ Good

---

# STILL NEEDS

| Feature           | Required |
| ----------------- | -------- |
| delivery logging  | YES      |
| provider failover | optional |
| resend cooldown   | YES      |

---

# 🔟 `/middleware/auth.php`

# CREATE

---

# PURPOSE

Protect:

```text id="jlwm205"
/public/client/*
```

---

# MUST CHECK

| Validation      | Required |
| --------------- | -------- |
| logged in       | YES      |
| session timeout | YES      |
| fingerprint     | YES      |
| role exists     | YES      |

---

# 1️⃣1️⃣ `/middleware/client.php`

# CREATE

---

# MUST CHECK

| Validation    | Required |
| ------------- | -------- |
| client role   | YES      |
| valid session | YES      |
| timeout       | YES      |

---

# 1️⃣2️⃣ `/middleware/guest.php`

# CREATE

---

# PURPOSE

Prevent logged-in users from:

* login
* register
* forgot password

---

# 1️⃣3️⃣ `/middleware/admin.php`

# STATUS

✅ Mostly completed

---

# STILL NEEDS

| Feature              | Required |
| -------------------- | -------- |
| admin audit logs     | YES      |
| device validation    | optional |
| route access logging | optional |

---

# 1️⃣4️⃣ `/app/controllers/AuthController.php`

# STATUS

✅ Strong

---

# STILL NEEDS

| Feature            | Required |
| ------------------ | -------- |
| forgot password    | YES      |
| reset password     | YES      |
| resend OTP         | YES      |
| remember me        | optional |
| email verification | optional |

---

# ADD METHODS

```php id="jlwm206"
forgotPassword()

verifyResetOtp()

resetPassword()

resendOtp()
```

---

# 1️⃣5️⃣ `/app/models/User.php`

# NEEDS SECURITY METHODS

---

# REQUIRED METHODS

```php id="jlwm207"
findByPhone()

incrementFailedAttempts()

lockAccount()

resetAttempts()

updateSession()

saveOtp()
```

---

# 1️⃣6️⃣ `/public/login.php`

# CHANGE ARCHITECTURE

---

# CLIENT LOGIN ONLY

```text id="jlwm208"
PHONE OTP LOGIN
```

---

# REMOVE

```text id="jlwm209"
email/password login
```

for public users.

---

# 1️⃣7️⃣ `/public/admin/login.php`

# CREATE

---

# ADMIN ONLY

```text id="jlwm210"
EMAIL + PASSWORD
```

---

# MUST INCLUDE

| Feature         | Required |
| --------------- | -------- |
| CSRF            | YES      |
| brute force     | YES      |
| rate limit      | YES      |
| admin isolation | YES      |

---

# 1️⃣8️⃣ `/public/logout.php`

# CREATE

---

# MUST

```php id="jlwm211"
destroySession()
```

---

# 1️⃣9️⃣ `/public/verify-phone-otp.php`

# STATUS

✅ Good

---

# STILL NEEDS

| Feature         | Required |
| --------------- | -------- |
| resend OTP      | YES      |
| countdown timer | YES      |
| attempt counter | YES      |

---

# 2️⃣0️⃣ `/public/admin/*`

# APPLY MIDDLEWARE

Add to ALL admin files:

```php id="jlwm212"
require '../../middleware/admin.php';
```

---

# 2️⃣1️⃣ `/public/client/*`

# APPLY MIDDLEWARE

```php id="jlwm213"
require '../../middleware/client.php';
```

---

# 2️⃣2️⃣ `/public/contact.php`

# ADD

| Protection      | Required |
| --------------- | -------- |
| CSRF            | YES      |
| rate limiting   | YES      |
| spam validation | YES      |

---

# 2️⃣3️⃣ `/public/estimator.php`

# ADD

| Protection      | Required |
| --------------- | -------- |
| rate limiting   | YES      |
| request logging | YES      |

---

# 2️⃣4️⃣ `/assets/js/app.js`

# ADD

| Feature                 | Required |
| ----------------------- | -------- |
| OTP timer               | YES      |
| resend countdown        | YES      |
| session timeout warning | optional |

---

# 2️⃣5️⃣ GLOBAL CODEBASE CHANGES

# REQUIRED EVERYWHERE

---

# NEVER

```php id="jlwm214"
echo $_POST['name'];
```

---

# ALWAYS

```php id="jlwm215"
echo escape($value);
```

---

# NEVER

```php id="jlwm216"
SELECT * FROM users WHERE id = $id
```

---

# ALWAYS

```php id="jlwm217"
$stmt = $conn->prepare(...)
```

---

# SECURITY IMPLEMENTATION ORDER

# PHASE 1

| File                       | Priority |
| -------------------------- | -------- |
| `/helpers/security.php`    | HIGH     |
| `/helpers/csrf.php`        | HIGH     |
| `/helpers/rateLimiter.php` | HIGH     |

---

# PHASE 2

| File                     | Priority |
| ------------------------ | -------- |
| `/middleware/auth.php`   | HIGH     |
| `/middleware/client.php` | HIGH     |
| `/middleware/guest.php`  | HIGH     |

---

# PHASE 3

| File                      | Priority |
| ------------------------- | -------- |
| `/public/admin/login.php` | HIGH     |
| `/public/logout.php`      | HIGH     |
| `/public/login.php`       | HIGH     |

---

# PHASE 4

| File                   | Priority |
| ---------------------- | -------- |
| `/helpers/upload.php`  | MEDIUM   |
| `/helpers/mail.php`    | MEDIUM   |
| `/app/models/User.php` | MEDIUM   |

---

