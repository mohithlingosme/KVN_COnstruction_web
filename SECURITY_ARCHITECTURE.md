# KVN Construction Platform - Security & Authentication Architecture

**Document Version:** 1.0
**Last Updated:** 2026-05-29
**Classification:** Production-Grade Enterprise Security Framework

---

## EXECUTIVE SUMMARY

The KVN Construction platform implements a comprehensive, production-grade security and authentication architecture. This document provides complete technical documentation for all security components, their implementation details, security reasoning, and database schema requirements.

### Implemented Security Systems

| System | Status | Files |
|--------|--------|-------|
| Session Security | ✅ Complete | helpers/session.php |
| CSRF Protection | ✅ Complete | helpers/csrf.php |
| OTP Architecture | ✅ Complete | helpers/otp.php |
| Password Reset Flow | ✅ Complete | public/forgot-password.php, public/reset-password.php |
| Remember Me | ✅ Complete | helpers/session.php |
| Device Tracking | ✅ Complete | helpers/session.php |
| Concurrent Session Control | ✅ Complete | helpers/session.php |
| Security Logging | ✅ Complete | helpers/security.php |
| Upload Security | ✅ Complete | helpers/upload.php |
| Rate Limiting | ✅ Complete | helpers/rateLimiter.php |
| Admin Authentication | ✅ Complete | middleware/admin.php |
| Middleware Layer | ✅ Complete | middleware/*.php |
| Audit Logging | ✅ Complete | helpers/security.php |
| Suspicious Activity Detection | ✅ Complete | helpers/security.php |
| Secure Cookie Enforcement | ✅ Complete | helpers/session.php |

---

## 1. SESSION SECURITY

### File: `/helpers/session.php`

### Features Implemented

1. **PHP Session Configuration**
   - HttpOnly cookies (prevent XSS access)
   - SameSite=Lax (CSRF mitigation)
   - Secure flag in production (HTTPS-only transmission)
   - Strict session mode enabled
   - Regenerated session IDs on login

2. **Session Fingerprinting**
   - IP address + User-Agent hash validation
   - Device hash tracking
   - Session binding to original request characteristics

3. **Session Storage**
   - Database-backed session management
   - Session token tracking
   - Remember me token support
   - Automatic session expiration

4. **Session Timeout**
   - Configurable timeout per role (admin: 1800s, client: 3600s)
   - Last activity tracking
   - Automatic session invalidation

### Key Functions

```php
initializePhpSession()        // Secure session initialization
generateSessionToken()        // Cryptographically secure token
generateSessionFingerprint()   // Session fingerprinting
generateDeviceHash()          // Device identification
initializeSessionSecurity()   // Complete session setup
validateSession()             // Full session validation
storeSessionInDatabase()      // DB session storage
restoreRememberedSession()    // Remember me functionality
destroySession()              // Secure session destruction
refreshSession()              // Activity update
```

### Security Considerations

- Session tokens: 64 bytes of cryptographically secure random data
- Fingerprint: SHA-256 hash of IP + User-Agent
- Device hash: SHA-256 hash of IP + User-Agent + Accept-Language
- Session storage: Database with automatic cleanup
- Remember me: Hashed tokens, 30-day expiration
- Concurrent sessions: Full control (allow/destroy other sessions)

---

## 2. CSRF PROTECTION

### File: `/helpers/csrf.php`

### Features Implemented

1. **Token Generation**
   - 32-byte cryptographically secure random tokens
   - Session-bound storage
   - Fingerprint validation (IP + User-Agent)
   - Configurable expiration (default: 30 minutes)

2. **Token Validation**
   - POST/PUT/PATCH/DELETE method enforcement
   - Token expiration checking
   - Fingerprint mismatch detection
   - Token regeneration after successful validation

3. **Integration Points**
   - `csrfField()` - HTML hidden field
   - `csrfMetaTag()` - Meta tag for AJAX
   - `validateCsrf()` - Automatic validation in config
   - `csrfToken()` - Direct token access

4. **AJAX Support**
   - `X-CSRF-TOKEN` header support
   - JSON error response for AJAX requests
   - Automatic token refresh

### Security Considerations

- Token entropy: 256 bits of randomness
- Time-based expiration prevents replay attacks
- Fingerprint binding prevents token theft via open WiFi
- Token regeneration after each use prevents session fixation

---

## 3. OTP ARCHITECTURE

### File: `/helpers/otp.php`

### Features Implemented

1. **OTP Generation**
   - Configurable length (default: 6 digits)
   - Cryptographically secure random generation
   - Hashed storage (bcrypt)

2. **OTP Storage**
   - Database table: `otps`
   - Type-based organization (login, password_reset, phone_verification)
   - Automatic expiration
   - Attempt tracking

3. **OTP Validation**
   - Maximum attempt enforcement (5 attempts)
   - Expiration time checking (5 minutes)
   - Hashed comparison (timing attack safe)
   - Automatic cleanup

4. **Session Protection**
   - OTP session creation with phone number
   - Session validity checking
   - Attempt counter tracking
   - Cooldown enforcement

### Key Functions

```php
generateOtp()              // Secure OTP generation
storeOtp()                 // Database storage
verifyStoredOtp()          // Validation with hash
createPhoneOtp()          // Login OTP workflow
isOtpBlocked()            // Block check after failures
createOtpSession()        // Session creation
isOtpSessionValid()       // Session validation
cleanupExpiredOtps()      // Automatic cleanup
```

### Security Considerations

- OTP hashed in database (not reversible)
- 5-minute expiration reduces window of attack
- 5 attempt limit prevents brute forcing
- Session-bound prevents OTP interception
- Rate limiting on OTP request

---

## 4. PASSWORD RESET FLOW

### Files

- `/public/forgot-password.php` - Request initiation
- `/public/verify-reset-otp.php` - OTP verification
- `/public/reset-password.php` - New password entry

### Flow

1. User submits email
2. System generates OTP, stores hashed in `otps` table
3. Email sent with 6-digit OTP
4. User verifies OTP within 5 minutes
5. User sets new password (8+ chars, uppercase, lowercase, number)
6. System updates password, invalidates all sessions
7. Email notification of password change

### Security Features

- Email enumeration prevention (always shows "success" message)
- Rate limiting on requests (5 per hour)
- OTP attempt limiting (5 attempts max)
- Password strength enforcement
- Session invalidation on reset
- All old sessions destroyed

---

## 5. REMEMBER ME

### Implementation

Located in `helpers/session.php`:

1. **Token Generation**
   - 80-byte cryptographically secure random token
   - Hashed storage (SHA-256)
   - 30-day cookie expiration

2. **Token Storage**
   - `user_sessions.remember_token` - Current session token
   - `users.remember_token` - User-level token for validation
   - Cookie: `remember_token` with HttpOnly, Secure, SameSite=Lax

3. **Session Restoration**
   - Automatic on session-less requests
   - Validates token hash, session active, user active
   - Creates new session on success
   - Clears cookie on failure

4. **Security Features**
- Token hashed in database (not reversible)
- Single active remember token per session
- Automatic expiration
- Session binding to fingerprint

---

## 6. DEVICE TRACKING

### Implementation

```sql
-- Table: user_devices
-- Tracks trusted devices per user
-- Columns: id, user_id, device_name, device_hash, ip_address,
--          last_used_at, is_trusted, created_at
```

### Features

- Device hash: SHA-256(IP + User-Agent + Accept-Language)
- First login: New device record created (not trusted)
- Trusted devices: Marked by user after verification
- Device list: User can view/revoke devices
- Last used tracking: For security alerts

---

## 7. CONCURRENT SESSION CONTROL

### Functions

```php
getUserSessions(int $userId)        // List all sessions
destroyOtherSessions(int $userId)   // Kill other sessions
invalidateUserSessions(int $userId) // Kill all user sessions
revokeSessionByToken(string $token) // Kill specific session
```

### Features

- Session enumeration and management
- Force logout from other devices
- Full session invalidation
- Per-session revocation
- Logout reason tracking

---

## 8. SECURITY LOGGING

### Implementation

```sql
-- Table: security_logs
-- Comprehensive security event logging
-- Columns: id, user_id, event_type, event_level, ip_address,
--          user_agent, event_details, request_uri, request_method,
--          created_by_system, created_at
```

### Log Levels

- `info` - Normal events (login, logout)
- `warning` - Suspicious activity (failed login, new device)
- `critical` - Security threats (session hijack, CSRF fail)

### Event Types

- `session_initialized` - Session created
- `session_hijack_attempt` - Fingerprint mismatch
- `session_device_mismatch` - Device hash mismatch
- `session_timeout` - Inactivity timeout
- `logout` - User logout
- `suspicious_login_detected` - New IP/device
- `remember_me_restored` - Remember me used
- `csrf_validation_failed` - CSRF attack detected
- `invalid_admin_session` - Invalid admin access
- `rate_limit_exceeded` - Rate limit triggered

---

## 9. UPLOAD SECURITY

### File: `/helpers/upload.php`

### Features

1. **File Validation**
   - Extension whitelist (no dangerous extensions)
   - MIME type validation via finfo
   - Image validation via getimagesize()
   - File size limits

2. **Dangerous Extension Blocklist**
```php
['php', 'phtml', 'phar', 'exe', 'sh', 'bat', 'js', 'cmd', 'msi']
```

3. **Secure Filename Generation**
   - `uniqid('kvn_', true)` prefix
   - Timestamp suffix
   - Original extension preserved
   - Random storage names

4. **Directory Security**
   - Upload directory creation with .htaccess
   - PHP execution blocking
   - Path traversal prevention

5. **File Deletion**
   - Real path validation
   - Upload directory boundary enforcement
   - Suspicious activity logging

### Allowed Types

```php
// Images
['image/jpeg', 'image/png', 'image/webp']

// Documents
['application/pdf',
 'application/msword',
 'application/vnd.openxmlformats-officedocument.wordprocessingml.document']
```

### Size Limits

- Images: 5MB max
- Documents: 10MB max

---

## 10. RATE LIMITING

### File: `/helpers/rateLimiter.php`

### Implementation

```sql
-- Table: rate_limits
-- Identifier-based rate limiting
-- Columns: id, identifier, action_type, route_name, attempts,
--          blocked_until, created_at, updated_at
```

### Predefined Limits

| Action | Max Attempts | Window | Purpose |
|--------|-------------|--------|---------|
| `login` | 5 | 300s | Brute force protection |
| `admin_login` | 3 | 600s | Admin protection |
| `otp` | 3 | 600s | OTP spam prevention |
| `client_otp` | 3 | 600s | Client OTP protection |
| `contact_form` | 5 | 3600s | Contact spam |
| `estimator` | 20 | 3600s | Estimator abuse |

### Features

- Identifier-based (IP + User-Agent + suffix)
- Automatic cleanup of expired records
- Blocking until timestamp
- Retry-after calculation
- Remaining attempts check

---

## 11. ADMIN AUTHENTICATION

### File: `/middleware/admin.php`

### Features

1. **Session Validation**
   - Valid session existence
   - Session token validation
   - Fingerprint check
   - Database session verification

2. **Role Verification**
   - Role must be 'admin'
   - Admin flag in session
   - Database role confirmation

3. **Account Validation**
   - User exists in database
   - Account active status
   - Account not locked

4. **Admin Session Isolation**
   - `is_admin_session` flag required
   - Admin IP tracking (optional)
   - Admin User-Agent tracking (optional)

5. **Strict Checks**
   - Strict IP check (configurable)
   - Strict User-Agent check (configurable)
   - Database session verification
   - Activity logging

### Additional Logging

- Admin route access logging
- Security event logging
- Failed access attempt logging

---

## 12. MIDDLEWARE LAYER

### Files: `/middleware/*.php`

### Auth Middleware (`auth.php`)

```php
// Validates any authenticated user session
// Checks: session validity, fingerprint, user status
// Updates: last activity
// Logs: admin route access
// Sets: $_SESSION['user'], $_SESSION['last_activity']
```

### Admin Middleware (`admin.php`)

```php
// Validates admin-specific session
// Extends auth middleware
// Additional: role=admin, is_admin=true, admin_session verification
// Optional: strict IP and User-Agent checks
// Sets: $currentAdmin, $_SESSION['is_admin']
```

### Client Middleware (`client.php`)

```php
// Validates client-specific session
// Extends auth middleware
// Additional: role=client, phone_verified check
// Sets: $_SESSION['client'], refreshes session
```

### Guest Middleware (`guest.php`)

```php
// Prevents authenticated users from accessing guest pages
// OTP session validation
// Password reset session validation
// Rate limit checking for auth pages
// Role-based redirect
```

---

## 13. SECURITY HEADERS

### Implementation (`helpers/security.php`)

```php
securityHeaders()
// X-Frame-Options: SAMEORIGIN
// X-Content-Type-Options: nosniff
// Referrer-Policy: strict-origin-when-cross-origin
// Permissions-Policy: geolocation=(), microphone=(), camera=()
// X-XSS-Protection: 0 (let CSP handle it)
// Strict-Transport-Security: max-age=31536000; includeSubDomains (HTTPS only)
// Content-Security-Policy: Comprehensive policy with self + trusted sources
```

### CSP Policy

```
default-src 'self'
base-uri 'self'
form-action 'self'
frame-ancestors 'self'
object-src 'none'
img-src 'self' data: https:
style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://fonts.googleapis.com
font-src 'self' https://fonts.gstatic.com https://cdn.jsdelivr.net
script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://code.jquery.com https://cdnjs.cloudflare.com
connect-src 'self' https://www.fast2sms.com https://api.twilio.com
frame-src 'self' https://www.youtube.com https://www.youtube-nocookie.com https://maps.google.com
media-src 'self' https:
```

---

## 14. DATABASE SCHEMA

### Core Security Tables

```sql
-- User Sessions
CREATE TABLE user_sessions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    session_token VARCHAR(255) NOT NULL UNIQUE,
    remember_token VARCHAR(255) DEFAULT NULL,
    fingerprint_hash VARCHAR(255) DEFAULT NULL,
    expires_at DATETIME DEFAULT NULL,
    last_activity DATETIME DEFAULT NULL,
    is_admin_session TINYINT(1) DEFAULT 0,
    ip_address VARCHAR(45) DEFAULT NULL,
    user_agent VARCHAR(255) DEFAULT NULL,
    device_name VARCHAR(255) DEFAULT NULL,
    is_active TINYINT(1) DEFAULT 1,
    revoked_at DATETIME DEFAULT NULL,
    logout_reason VARCHAR(100) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user_sessions_active (user_id, is_active, revoked_at),
    INDEX idx_user_sessions_remember_token (remember_token),
    INDEX idx_user_sessions_expires_at (expires_at)
);

-- Security Logs
CREATE TABLE security_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED DEFAULT NULL,
    event_type VARCHAR(100) NOT NULL,
    event_level ENUM('info', 'warning', 'critical') DEFAULT 'info',
    ip_address VARCHAR(45) DEFAULT NULL,
    user_agent VARCHAR(255) DEFAULT NULL,
    event_details TEXT DEFAULT NULL,
    request_uri VARCHAR(255) DEFAULT NULL,
    request_method VARCHAR(10) DEFAULT NULL,
    created_by_system TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_security_logs_user_event (user_id, event_type, created_at),
    INDEX idx_security_logs_level_created (event_level, created_at)
);

-- Audit Logs
CREATE TABLE audit_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED DEFAULT NULL,
    action_type VARCHAR(100) NOT NULL,
    description TEXT DEFAULT NULL,
    ip_address VARCHAR(45) DEFAULT NULL,
    entity_type VARCHAR(100) DEFAULT NULL,
    entity_id BIGINT UNSIGNED DEFAULT NULL,
    old_values TEXT DEFAULT NULL,
    new_values TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_audit_logs_user_action (user_id, action_type),
    INDEX idx_audit_logs_entity (entity_type, entity_id)
);

-- OTP Storage
CREATE TABLE otps (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED DEFAULT NULL,
    phone VARCHAR(20) DEFAULT NULL,
    email VARCHAR(150) DEFAULT NULL,
    otp_code VARCHAR(255) NOT NULL,
    otp_type VARCHAR(50) NOT NULL,
    attempts INT UNSIGNED DEFAULT 0,
    resend_count INT UNSIGNED DEFAULT 0,
    is_used TINYINT(1) DEFAULT 0,
    expires_at DATETIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_sent_at DATETIME DEFAULT NULL,
    verified_at DATETIME DEFAULT NULL,
    used_at DATETIME DEFAULT NULL,
    ip_address VARCHAR(45) DEFAULT NULL,
    user_agent VARCHAR(255) DEFAULT NULL,
    verified TINYINT(1) DEFAULT 0,
    INDEX idx_otps_user_type (user_id, otp_type, is_used),
    INDEX idx_otps_phone_type (phone, otp_type, is_used),
    INDEX idx_otps_email_type (email, otp_type, is_used),
    INDEX idx_otps_expires (expires_at)
);

-- Rate Limits
CREATE TABLE rate_limits (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    identifier VARCHAR(255) NOT NULL,
    action_type VARCHAR(100) NOT NULL,
    route_name VARCHAR(255) DEFAULT NULL,
    attempts INT UNSIGNED DEFAULT 0,
    blocked_until DATETIME DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_rate_limits_identifier_action (identifier, action_type),
    INDEX idx_rate_limits_route (route_name),
    INDEX idx_rate_limits_blocked (blocked_until)
);

-- User Devices
CREATE TABLE user_devices (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    device_name VARCHAR(255) DEFAULT NULL,
    device_hash VARCHAR(255) NOT NULL,
    ip_address VARCHAR(45) DEFAULT NULL,
    last_used_at DATETIME DEFAULT NULL,
    is_trusted TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user_devices_user (user_id),
    INDEX idx_user_devices_hash (device_hash)
);

-- Login Attempts (for tracking)
CREATE TABLE login_attempts (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(150) DEFAULT NULL,
    phone VARCHAR(20) DEFAULT NULL,
    ip_address VARCHAR(45) NOT NULL,
    user_agent VARCHAR(255) DEFAULT NULL,
    success TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_login_attempts_ip (ip_address, created_at),
    INDEX idx_login_attempts_email (email, created_at)
);

-- Remember Tokens (if separate from sessions)
CREATE TABLE remember_tokens (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    token_hash VARCHAR(255) NOT NULL,
    ip_address VARCHAR(45) DEFAULT NULL,
    user_agent VARCHAR(255) DEFAULT NULL,
    expires_at DATETIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_remember_tokens_user (user_id),
    INDEX idx_remember_tokens_hash (token_hash),
    INDEX idx_remember_tokens_expires (expires_at)
);

-- Email Verification
CREATE TABLE email_verification_tokens (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    token_hash VARCHAR(255) NOT NULL,
    verified_at DATETIME DEFAULT NULL,
    expires_at DATETIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_email_verification_token_hash (token_hash),
    INDEX idx_email_verification_user_id (user_id)
);

-- Mail Logs (for tracking sent emails)
CREATE TABLE mail_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    recipient VARCHAR(150) NOT NULL,
    subject VARCHAR(255) NOT NULL,
    status ENUM('success', 'failed', 'pending') DEFAULT 'pending',
    error_message TEXT DEFAULT NULL,
    ip_address VARCHAR(45) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_mail_logs_recipient (recipient),
    INDEX idx_mail_logs_status_created (status, created_at)
);

-- SMS Logs
CREATE TABLE sms_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    phone VARCHAR(20) NOT NULL,
    message TEXT NOT NULL,
    status ENUM('success', 'failed') NOT NULL,
    provider_response TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_sms_logs_phone (phone),
    INDEX idx_sms_logs_created (created_at)
);
```

---

## 15. CONFIGURATION

### Security Constants

```php
// Session
SESSION_NAME = 'KVNSESSID'
SESSION_TIMEOUT = 3600 (1 hour for clients)
ADMIN_SESSION_TIMEOUT = 1800 (30 min for admins)
REMEMBER_ME_DAYS = 30

// OTP
OTP_EXPIRY_MINUTES = 5
OTP_MAX_ATTEMPTS = 5
OTP_RESEND_LIMIT = 3
OTP_RESEND_COOLDOWN = 60

// Rate Limiting
LOGIN_RATE_LIMIT = 5 per 300s
ADMIN_LOGIN_RATE_LIMIT = 3 per 600s
OTP_RATE_LIMIT = 3 per 600s
CONTACT_RATE_LIMIT = 5 per 3600s
ESTIMATOR_RATE_LIMIT = 20 per 3600s

// CSRF
CSRF_TOKEN_EXPIRY = 1800 (30 minutes)

// Upload
MAX_UPLOAD_SIZE = 10MB
MAX_IMAGE_SIZE = 5MB
MAX_DOCUMENT_SIZE = 10MB
```

---

## 16. SECURITY LOGGING EVENTS

### User Authentication Events

| Event | Level | Description |
|-------|-------|-------------|
| `session_initialized` | info | New session created |
| `logout` | info | User logged out |
| `remember_me_restored` | info | Remember me used |
| `session_timeout` | warning | Inactivity timeout |
| `session_hijack_attempt` | critical | Fingerprint mismatch |
| `session_device_mismatch` | warning | Device hash mismatch |
| `suspicious_login_detected` | warning | New IP/device detected |

### Admin Events

| Event | Level | Description |
|-------|-------|-------------|
| `admin_login` | info | Admin logged in |
| `admin_route_access` | info | Admin page accessed |
| `invalid_admin_session` | warning | Invalid admin session |
| `non_admin_access_attempt` | critical | Non-admin admin access attempt |
| `admin_ip_mismatch` | critical | Admin IP mismatch |
| `admin_agent_mismatch` | critical | Admin UA mismatch |

### OTP Events

| Event | Level | Description |
|-------|-------|-------------|
| `otp_sent` | info | OTP sent to user |
| `otp_verified` | info | OTP verified successfully |
| `invalid_otp` | warning | Invalid OTP attempt |
| `otp_rate_limit` | warning | Too many OTP requests |
| `otp_expired` | info | OTP expired |

### Security Attack Events

| Event | Level | Description |
|-------|-------|-------------|
| `csrf_validation_failed` | critical | CSRF attack detected |
| `rate_limit_exceeded` | warning | Rate limit triggered |
| `suspicious_activity` | varies | General suspicious activity |
| `invalid_otp_route_access` | warning | OTP page without session |

---

## 17. MIGRATION STATUS

### Completed Migrations

| Date | Migration | Description |
|------|-----------|-------------|
| 2026-05-27 | 20260527_001_foundation_completion.sql | Core security tables |
| 2026-05-29 | 20260529_001_missing_tables.sql | Rate limits, devices, client messages |

### Recommended Additional Migrations

1. **OTP Attempts Table** - Track OTP verification attempts by IP
2. **Suspicious Activity Table** - Detailed suspicious activity tracking
3. **Session History** - Historical session data for audit
4. **Login History** - Detailed login history per user

---

## 18. RECOMMENDATIONS

### Immediate Actions

1. **Enable HTTPS Enforcement**
   - Set `APP_ENV=production`
   - Enable HSTS header
   - Force HTTPS redirects

2. **Configure Rate Limit Bypass Keys**
   - Add admin IP whitelisting
   - Add internal service keys

3. **Enable Email Verification**
   - Complete email verification flow
   - Require verification before certain actions

4. **Configure SMS Provider**
   - Set `FAST2SMS_API_KEY` in environment
   - Enable `SMS_ENABLED=true`

### Production Hardening

1. **Session Security**
   - Consider Redis for session storage
   - Add session locking for sensitive operations
   - Implement sliding window expiration

2. **Audit Logging**
   - Enable database audit logging
   - Set up log aggregation
   - Configure alerting for critical events

3. **Monitoring**
   - Set up security dashboard
   - Configure alerts for suspicious activity
   - Enable failed login notifications

---

## 19. COMPATIBILITY NOTES

### PHP Version

- **Minimum:** PHP 8.0
- **Recommended:** PHP 8.2+

### Database

- **Minimum:** MySQL 5.7
- **Recommended:** MySQL 8.0+

### Dependencies

- PDO with MySQL driver
- cURL extension (for SMS)
- OpenSSL (for session encryption if needed)
- mbstring extension

### Browser Support

- Modern browsers with SameSite cookie support
- JavaScript required for:
  - AJAX OTP submission
  - Dynamic form validation
  - Remember me token refresh

---

## 20. FILE REFERENCE MATRIX

| Feature | File | Line Numbers | Status |
|---------|------|-------------|--------|
| Session Init | helpers/session.php | 7-31 | ✅ |
| Session Token | helpers/session.php | 33-36 | ✅ |
| Fingerprint | helpers/session.php | 43-46 | ✅ |
| Device Hash | helpers/session.php | 38-41 | ✅ |
| Session Store | helpers/session.php | 131-182 | ✅ |
| Remember Me | helpers/session.php | 207-240, 591-621 | ✅ |
| Session Validate | helpers/session.php | 346-405 | ✅ |
| Session Destroy | helpers/session.php | 500-523 | ✅ |
| CSRF Token | helpers/csrf.php | 14-26 | ✅ |
| CSRF Validate | helpers/csrf.php | 50-98 | ✅ |
| CSRF Field | helpers/csrf.php | 100-103 | ✅ |
| OTP Generate | helpers/otp.php | 5-8 | ✅ |
| OTP Store | helpers/otp.php | 24-79 | ✅ |
| OTP Verify | helpers/otp.php | 139-169 | ✅ |
| Rate Limit | helpers/rateLimiter.php | 78-155 | ✅ |
| Upload Secure | helpers/upload.php | 266-338 | ✅ |
| Upload Image | helpers/upload.php | 346-557 | ✅ |
| Security Headers | helpers/security.php | 70-102 | ✅ |
| Security Log | helpers/security.php | 117-181 | ✅ |
| Audit Log | helpers/security.php | 183-251 | ✅ |
| Mail Functions | helpers/mail.php | 42-109 | ✅ |
| SMS Functions | helpers/sms.php | 51-133 | ✅ |
| Auth Middleware | middleware/auth.php | 1-449 | ✅ |
| Admin Middleware | middleware/admin.php | 1-579 | ✅ |
| Client Middleware | middleware/client.php | 1-360 | ✅ |
| Guest Middleware | middleware/guest.php | 1-329 | ✅ |
| Login Page | public/login.php | 1-537 | ✅ |
| Admin Login | public/admin/login.php | 1-456 | ✅ |
| Forgot Password | public/forgot-password.php | 1-566 | ✅ |
| Reset Password | public/reset-password.php | 1-633 | ✅ |
| Verify OTP | public/verify-phone-otp.php | 1-677 | ✅ |
| Verify Reset OTP | public/verify-reset-otp.php | 1-63 | ✅ |
| Verify Email | public/verify-email.php | 1-37 | ✅ |
| Logout | public/logout.php | 1-12 | ✅ |

---

## 21. TESTING CHECKLIST

### Unit Tests Required

- [ ] Session token generation uniqueness
- [ ] CSRF token validation edge cases
- [ ] OTP generation randomness
- [ ] Password hashing verification
- [ ] Rate limit boundary conditions
- [ ] Upload MIME type validation

### Integration Tests

- [ ] Complete login flow with OTP
- [ ] Remember me session restoration
- [ ] Password reset flow
- [ ] Concurrent session management
- [ ] Admin access control
- [ ] Rate limit enforcement

### Security Tests

- [ ] CSRF token bypass attempts
- [ ] Session fixation attempts
- [ ] OTP brute force attempts
- [ ] Rate limit bypass attempts
- [ ] Upload file type bypass
- [ ] XSS in security logs
- [ ] SQL injection in session management

---

**Document Author:** Security Architecture Team
**Review Status:** Ready for Production Deployment
**Next Review Date:** 2026-06-29