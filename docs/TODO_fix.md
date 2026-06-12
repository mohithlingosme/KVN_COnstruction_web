🚨 PRIORITY 1 — CRITICAL AUTH FIXES
PASSWORD RESET FLOW
 Create /public/verify-reset-otp.php
 Add OTP verification step before password reset
 Store reset OTP securely in database
 Add OTP expiry validation
 Add OTP resend cooldown
 Add reset attempt limit
 Clear reset OTP after successful password reset
 Destroy all old sessions after password reset
 Force re-login after password reset
🚨 PRIORITY 2 — SESSION SECURITY
SESSION HARDENING
 Add session invalidation system
 Invalidate all sessions except current
 Add session activity tracking
 Add device fingerprint validation
 Add IP tracking
 Update user_sessions.last_activity_at
 Add session timeout enforcement
 Add admin concurrent session control
🚨 PRIORITY 3 — MIDDLEWARE FIXES
MIDDLEWARE STRUCTURE
 Rename:
middleware/clients.php
→
middleware/client.php
 Apply admin middleware globally:
require '../../middleware/admin.php';

to ALL:

/public/admin/*
 Apply client middleware globally:
require '../../middleware/client.php';

to ALL:

/public/client/*
 Verify guest middleware on:
login
forgot-password
register
phone-login
🚨 PRIORITY 4 — DATABASE SECURITY TABLES
REQUIRED SECURITY TABLES
 Create security_logs
 Create login_attempts
 Create user_sessions
 Create rate_limits
 Create blocked_ips
 Create password_reset_tokens
 Create otp_logs
 Add indexes to security tables
 Add cleanup cron strategy
🚨 PRIORITY 5 — FILE UPLOAD SECURITY
UPLOAD HARDENING
 Block PHP execution in /uploads
 Add .htaccess upload protection
 Disable dangerous extensions:
.php
.phtml
.js
.exe
.sh
 Validate MIME types
 Validate real image signatures
 Add upload size limits
 Add filename randomization
 Add malware scanning hook (future)
🚨 PRIORITY 6 — CSP & SECURITY HEADERS
CSP VALIDATION

Verify CSP works with:

 Swiper CDN
 Google Fonts
 Bootstrap Icons
 FontAwesome
 AJAX endpoints
 inline scripts
 inline styles
VERIFY HEADERS
 X-Frame-Options
 X-Content-Type-Options
 Referrer-Policy
 Permissions-Policy
 Strict-Transport-Security
🚨 PRIORITY 7 — COOKIE SECURITY
COOKIE HARDENING

Verify:

 session.cookie_secure
 session.cookie_httponly
 session.cookie_samesite
 HTTPS-only cookies
 Session regeneration after login
 Session regeneration after OTP verify
🚨 PRIORITY 8 — AUTH SYSTEM FINALIZATION
AUTH FLOW
 Verify admin login isolation
 Verify phone OTP login flow
 Verify logout destroys sessions
 Verify CSRF on all auth forms
 Add brute force detection
 Add login attempt locking
 Add suspicious activity alerts
 Add admin login email alerts
🚨 PRIORITY 9 — SECURITY LOGGING
AUDIT SYSTEM
 Implement logAdminAction()
 Implement logSecurityEvent()
 Log:
admin logins
failed logins
password resets
OTP failures
suspicious requests
uploads
admin CRUD actions
 Add audit viewer optimization
 Add pagination to logs
🚨 PRIORITY 10 — RATE LIMITING
RATE LIMITS

Verify:

 Admin login → 3 / 10 min
 OTP requests → 3 / 10 min
 Contact form → 5 / hour
 Estimator → 20 / hour
🚨 PRIORITY 11 — FRONTEND SECURITY
FORM HARDENING
 Add CSRF to ALL forms
 Escape ALL outputs
 Remove unsafe echoes
 Validate all POST inputs
 Add honeypot spam protection
 Add CAPTCHA (optional)
🚨 PRIORITY 12 — ADMIN PANEL HARDENING
ADMIN SECURITY
 Add admin role validation
 Add permission system
 Add admin activity logs
 Add route access logs
 Add admin IP restrictions (optional)
 Add admin device verification (optional)
🚨 PRIORITY 13 — CLIENT PORTAL SECURITY
CLIENT PORTAL
 Verify ownership checks
 Protect document downloads
 Prevent IDOR vulnerabilities
 Add signed file URLs
 Add upload validation
🚨 PRIORITY 14 — CODEBASE CLEANUP
CLEANUP
 Remove duplicate auth views
 Remove dead code
 Remove inline SQL
 Replace unsafe echoes
 Standardize helper usage
 Add centralized validation helper
🚨 PRIORITY 15 — PRODUCTION READINESS
BEFORE LIVE DEPLOYMENT
 Force HTTPS
 Disable debug errors
 Disable directory listing
 Secure .env
 Secure database credentials
 Configure backups
 Configure server firewall
 Configure fail2ban (optional)
 Enable SSL auto-renewal
 Add monitoring/log rotation