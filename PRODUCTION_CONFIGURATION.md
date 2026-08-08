# KVN Construction Platform - Production Configuration Guide

## Version: 1.0.0
## Date: 2026-08-06
## Status: RELEASE CANDIDATE

---

## CRITICAL: Pre-Deployment Configuration Checklist

### Phase 1: Production Environment Variables

The `.env` file must be configured with production values before deployment. Below is the complete configuration template.

#### 1.1 Database Configuration

```env
DB_HOST=127.0.0.1
DB_PORT=3306
DB_NAME=kvnc_platform
DB_USER=your_production_db_user
DB_PASS=your_secure_production_password
```

**Requirements:**
- Use a dedicated MySQL/MariaDB user (not root)
- Minimum password length: 16 characters
- Include uppercase, lowercase, numbers, and special characters
- Grant only necessary privileges: SELECT, INSERT, UPDATE, DELETE, CREATE, ALTER, INDEX

#### 1.2 Application Settings

```env
APP_NAME="KVN Construction"
APP_URL=https://kvnconstruction.com
APP_ENV=production
APP_DEBUG=false
APP_KEY=base64:GENERATE_32_RANDOM_CHARS_HERE=
```

**Critical Security Notes:**
- `APP_DEBUG=false` - NEVER enable debug mode in production
- `APP_KEY` - Generate using: `php -r "echo 'base64:' . base64_encode(random_bytes(32));"`
- `APP_URL` - Must match your production domain exactly

#### 1.3 Session Security

```env
SESSION_NAME=KVNSESSID
SESSION_TIMEOUT=3600
ADMIN_SESSION_TIMEOUT=1800
```

**Implementation Details:**
- Sessions are secured via `helpers/session.php`
- Cookie settings: Secure (HTTPS only), HttpOnly, SameSite=Strict
- Session tokens stored in database via `SessionRepository`
- Fingerprint validation prevents session hijacking

#### 1.4 OTP Configuration

```env
OTP_EXPIRY_MINUTES=5
OTP_MAX_ATTEMPTS=3
OTP_RESEND_LIMIT=3
OTP_BLOCK_MINUTES=15
```

**Security Features:**
- OTPs expire after 5 minutes
- Maximum 3 verification attempts
- Maximum 3 resend attempts
- 15-minute block after failed attempts

#### 1.5 Rate Limiting

```env
LOGIN_RATE_LIMIT=5
LOGIN_RATE_WINDOW=300
OTP_RATE_LIMIT=3
OTP_RATE_WINDOW=600
```

**Protection Against:**
- Brute force attacks (5 attempts per 5 minutes)
- OTP bombing (3 attempts per 10 minutes)

#### 1.6 SMS Gateway Configuration (Twilio)

```env
SMS_PROVIDER=twilio
SMS_API_KEY=your_twilio_account_sid
SMS_API_SECRET=your_twilio_auth_token
SMS_FROM_NUMBER=+1234567890
SMS_ACCOUNT_SID=your_twilio_account_sid
```

**Required Values:**
- Twilio Account SID
- Twilio Auth Token
- Verified Twilio phone number

#### 1.7 Email Gateway Configuration (SMTP)

```env
MAIL_DRIVER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=noreply@kvnconstruction.com
MAIL_PASSWORD=your_app_specific_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@kvnconstruction.com
MAIL_FROM_NAME="KVN Construction"
```

**Recommended Providers:**
- Gmail SMTP (use App Password for 2FA accounts)
- SendGrid
- Amazon SES
- Mailgun

#### 1.8 WhatsApp API Configuration

```env
WHATSAPP_API_KEY=your_whatsapp_business_api_key
```

**Optional:** Only required if WhatsApp notifications are enabled.

#### 1.9 File Upload Security

```env
MAX_UPLOAD_SIZE=5242880
ALLOWED_IMAGE_TYPES=image/jpeg,image/png,image/webp
ALLOWED_DOCUMENT_TYPES=application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document
```

**Validation:**
- Maximum file size: 5MB
- MIME type validation enforced in `helpers/upload.php`
- File extension validation
- Random filename generation to prevent path traversal

#### 1.10 Security Configuration

```env
CSRF_TOKEN_EXPIRY=1800
HASH_COST=12
```

**Security Features:**
- CSRF tokens expire after 30 minutes
- Password hashing uses bcrypt with cost factor 12
- All forms protected by CSRF middleware

#### 1.11 Maintenance Mode

```env
MAINTENANCE_MODE=false
```

**Usage:**
- Set to `true` during deployments
- Only admin users can access the site
- Public users see maintenance page

---

## Phase 2: Server Configuration

### 2.1 PHP Configuration (php.ini)

```ini
; Production PHP Settings
upload_max_filesize = 10M
post_max_size = 10M
max_execution_time = 60
max_input_time = 60
memory_limit = 256M
error_reporting = E_ALL & ~E_DEPRECATED & ~E_STRICT
display_errors = Off
log_errors = On
error_log = /var/log/php_errors.log
session.gc_maxlifetime = 3600
session.cookie_httponly = 1
session.cookie_secure = 1
session.use_only_cookies = 1
```

### 2.2 Apache Configuration (.htaccess)

The `.htaccess` file in the `public/` directory handles:
- URL rewriting (removing /public/ from URLs)
- Security headers
- HTTPS enforcement
- File access restrictions

**Critical Directives:**
```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteCond %{HTTPS} off
    RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
</IfModule>

<FilesMatch "\.env$">
    Require all denied
</FilesMatch>

<FilesMatch "\.sql$">
    Require all denied
</FilesMatch>
```

### 2.3 MariaDB/MySQL Configuration

```ini
[mysqld]
# Performance
innodb_buffer_pool_size = 1G
innodb_log_file_size = 256M
innodb_flush_log_at_trx_commit = 2
innodb_lock_wait_timeout = 50

# Security
local_infile = 0
skip_show_database = 1

# Character Set
character-set-server = utf8mb4
collation-server = utf8mb4_unicode_ci
```

---

## Phase 3: SSL/TLS Configuration

### 3.1 HTTPS Requirements

**Mandatory for Production:**
- Valid SSL certificate from trusted CA
- TLS 1.2 or higher only
- HSTS header enabled
- No mixed content (all resources loaded via HTTPS)

### 3.2 SSL Certificate Installation

**Using Let's Encrypt (Recommended):**
```bash
sudo certbot --apache -d kvnconstruction.com -d www.kvnconstruction.com
```

**Manual Installation:**
1. Obtain certificate from trusted CA
2. Configure Apache VirtualHost:
```apache
<VirtualHost *:443>
    ServerName kvnconstruction.com
    DocumentRoot /path/to/KVN_Construction/public
    
    SSLEngine on
    SSLCertificateFile /path/to/cert.pem
    SSLCertificateKeyFile /path/to/privkey.pem
    SSLCertificateChainFile /path/to/chain.pem
    
    <Directory /path/to/KVN_Construction/public>
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

---

## Phase 4: Directory Permissions

### 4.1 Required Permissions

```bash
# Application directories
chmod 755 /path/to/KVN_Construction
chmod 755 /path/to/KVN_Construction/public
chmod 755 /path/to/KVN_Construction/app
chmod 755 /path/to/KVN_Construction/config
chmod 755 /path/to/KVN_Construction/helpers
chmod 755 /path/to/KVN_Construction/middleware
chmod 755 /path/to/KVN_Construction/routes

# Writable directories
chmod 775 /path/to/KVN_Construction/uploads
chmod 775 /path/to/KVN_Construction/bootstrap/cache
chmod 775 /path/to/KVN_Construction/storage

# Secure sensitive files
chmod 640 /path/to/KVN_Construction/.env
chown www-data:www-data /path/to/KVN_Construction/.env
```

### 4.2 Ownership

```bash
# Recommended ownership
chown -R www-data:www-data /path/to/KVN_Construction
```

---

## Phase 5: Cron Jobs

### 5.1 Required Cron Jobs

> **Important:** This is a **plain PHP** application (no Laravel). There is **no
> `artisan` binary**. Scheduled maintenance must call the project's own PHP
> scripts or OS-level commands.

```bash
# Cleanup expired sessions / OTPs (every 15 minutes)
# Provided by the project's own cleanup script (see scripts/clear_opcache.php
# and the session/OTP cleanup routines in the app). If a dedicated cleanup
# script is added, schedule it here:
# */15 * * * * php /path/to/KVN_Construction/scripts/cleanup.php --quiet

# Backup database (daily at 2 AM) - use mysqldump directly (no artisan)
0 2 * * * /path/to/KVN_Construction/scripts/backup.sh --quiet

# Clear OPcache after releases (run once after deploy, not scheduled hourly)
# php /path/to/KVN_Construction/scripts/clear_opcache.php
```

> Standard PHP/SQL maintenance commands (no Laravel):
> - **OPcache clear:** `php /path/to/KVN_Construction/scripts/clear_opcache.php`
> - **Migrations/seed:** `php /path/to/KVN_Construction/scripts/run_migrations.php --seed`
> - **Smoke test:** `php /path/to/KVN_Construction/scripts/smoke_test.php`
> - **Database backup:** use `mysqldump` (see Section 6)

---

## Phase 6: Backup Strategy

### 6.1 Database Backup

**Automated Daily Backup:**
```bash
#!/bin/bash
# /path/to/scripts/backup_db.sh

DATE=$(date +%Y%m%d_%H%M%S)
DB_NAME="kvnc_platform"
DB_USER="backup_user"
DB_PASS="backup_password"
BACKUP_DIR="/path/to/backups"

mysqldump -u$DB_USER -p$DB_PASS $DB_NAME > $BACKUP_DIR/db_$DATE.sql
gzip $BACKUP_DIR/db_$DATE.sql

# Keep only last 30 days
find $BACKUP_DIR -name "db_*.sql.gz" -mtime +30 -delete
```

### 6.2 File Backup

```bash
#!/bin/bash
# /path/to/scripts/backup_files.sh

DATE=$(date +%Y%m%d_%H%M%S)
BACKUP_DIR="/path/to/backups"

tar -czf $BACKUP_DIR/uploads_$DATE.tar.gz /path/to/KVN_Construction/uploads
tar -czf $BACKUP_DIR/config_$DATE.tar.gz /path/to/KVN_Construction/.env
```

---

## Phase 7: Health Checks

### 7.1 Application Health Endpoint

The project ships an **existing** health endpoint at `public/health.php`. It checks
database connectivity and returns a JSON status. Verify it after deployment:

```bash
curl -s https://kvnconstruction.com/health.php
# -> {"status":"healthy","database":"connected", ...}
```

No new file needs to be created. Reference implementation (already present):

```php
<?php
// public/health.php (already part of the repository)
header('Content-Type: application/json');

try {
    // Check database connection
    $db = \App\Core\Database::getInstance();
    if (!$db->isConnected()) {
        throw new \Exception('Database connection failed');
    }
    // ... table + version checks ...
} catch (\Exception $e) {
    http_response_code(503);
    echo json_encode(['status' => 'unhealthy', 'error' => $e->getMessage()]);
}
```

### 7.2 Monitoring Checklist

- [ ] Uptime monitoring (UptimeRobot, Pingdom)
- [ ] SSL certificate expiration alerts
- [ ] Disk space monitoring
- [ ] Database connection pool monitoring
- [ ] Error log monitoring
- [ ] Application performance monitoring (APM)

---

## Phase 8: Security Hardening

### 8.1 Firewall Configuration

```bash
# Allow only necessary ports
ufw allow 80/tcp
ufw allow 443/tcp
ufw allow 22/tcp  # SSH
ufw enable
```

### 8.2 Disable Unnecessary PHP Functions

In `php.ini`:
```ini
disable_functions = exec,passthru,shell_exec,system,proc_open,popen,curl_exec,curl_multi_exec,parse_ini_file,show_source
```

### 8.3 Database Security

```sql
-- Create production user with minimal privileges
CREATE USER 'kvnc_prod'@'localhost' IDENTIFIED BY 'secure_password_123!';
GRANT SELECT, INSERT, UPDATE, DELETE, CREATE, ALTER, INDEX ON kvnc_platform.* TO 'kvnc_prod'@'localhost';
FLUSH PRIVILEGES;
```

---

## Phase 9: Environment Variables Reference

### Complete .env Template for Production

```env
# ============================================
# KVN CONSTRUCTION PLATFORM - PRODUCTION
# ============================================

# -- Database Configuration --
DB_HOST=127.0.0.1
DB_PORT=3306
DB_NAME=kvnc_platform
DB_USER=kvnc_prod
DB_PASS=secure_production_password_here

# -- Application Settings --
APP_NAME="KVN Construction"
APP_URL=https://kvnconstruction.com
APP_ENV=production
APP_DEBUG=false
APP_KEY=base64:GENERATE_WITH_PHP_COMMAND=

# -- Session Configuration --
SESSION_NAME=KVNSESSID
SESSION_TIMEOUT=3600
ADMIN_SESSION_TIMEOUT=1800

# -- OTP Settings --
OTP_EXPIRY_MINUTES=5
OTP_MAX_ATTEMPTS=3
OTP_RESEND_LIMIT=3
OTP_BLOCK_MINUTES=15

# -- Rate Limiting --
LOGIN_RATE_LIMIT=5
LOGIN_RATE_WINDOW=300
OTP_RATE_LIMIT=3
OTP_RATE_WINDOW=600

# -- SMS Gateway (Twilio) --
SMS_PROVIDER=twilio
SMS_API_KEY=your_twilio_account_sid
SMS_API_SECRET=your_twilio_auth_token
SMS_FROM_NUMBER=+1234567890
SMS_ACCOUNT_SID=your_twilio_account_sid

# -- Email Gateway (SMTP) --
MAIL_DRIVER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=noreply@kvnconstruction.com
MAIL_PASSWORD=your_app_specific_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@kvnconstruction.com
MAIL_FROM_NAME="KVN Construction"

# -- WhatsApp API --
WHATSAPP_API_KEY=your_whatsapp_api_key

# -- File Uploads --
MAX_UPLOAD_SIZE=5242880
ALLOWED_IMAGE_TYPES=image/jpeg,image/png,image/webp
ALLOWED_DOCUMENT_TYPES=application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document

# -- Security --
CSRF_TOKEN_EXPIRY=1800
HASH_COST=12

# -- Maintenance Mode --
MAINTENANCE_MODE=false
```

---

## Phase 10: Deployment Steps

### Step-by-Step Production Deployment

1. **Pre-Deployment**
   ```bash
   # Pull latest code
   git pull origin main
   
   # Install dependencies (if any)
   # composer install --no-dev --optimize-autoloader
   ```

2. **Configuration**
   ```bash
   # Copy and configure .env
   cp .env.example .env
   # Edit .env with production values
   nano .env
   
   # Generate APP_KEY
   php -r "echo 'base64:' . base64_encode(random_bytes(32));"
   ```

3. **Database Setup**
   > This is a **plain PHP** project. Use the migration runner — there is no
   > `database/triggers.sql` and no `database/seeders/run.php`.
   ```bash
   # Apply schema + record in schema_migrations (idempotent)
   php /path/to/KVN_Construction/scripts/run_migrations.php

   # Apply required system seed data (roles, permissions, settings, admin)
   php /path/to/KVN_Construction/scripts/run_migrations.php --seed

   # LOCAL ONLY - to rebuild from scratch
   php /path/to/KVN_Construction/scripts/run_migrations.php --fresh --seed
   ```
   The schema (including the OTP triggers) is defined idempotently in
   `database/schema.sql` and applied by the runner above.

4. **Permissions**
   ```bash
   # Set directory permissions
   chown -R www-data:www-data /path/to/KVN_Construction
   chmod -R 755 /path/to/KVN_Construction
   chmod -R 775 /path/to/KVN_Construction/uploads
   chmod 640 /path/to/KVN_Construction/.env
   ```

5. **Web Server Configuration**
   ```bash
   # Configure Apache VirtualHost
   nano /etc/apache2/sites-available/kvnconstruction.conf
   
   # Enable site
   a2ensite kvnconstruction
   
   # Enable rewrite module
   a2enmod rewrite
   
   # Restart Apache
   systemctl restart apache2
   ```

6. **SSL Certificate**
   ```bash
   # Install SSL certificate
   sudo certbot --apache -d kvnconstruction.com -d www.kvnconstruction.com
   ```

7. **Cron Jobs**
   ```bash
   # Add cron jobs
   crontab -e
   # Add jobs from Section 5.1
   ```

8. **Verification**
   ```bash
   # Test application
   curl https://kvnconstruction.com/health.php
   
   # Check error logs
   tail -f /var/log/apache2/error.log
   
   # Run smoke tests
   # See TESTING.md for smoke test procedures
   ```

---

## Phase 11: Post-Deployment Checklist

### Immediate Verification (0-1 hour)

- [ ] Application loads successfully
- [ ] HTTPS redirect works
- [ ] Login/registration functional
- [ ] Database queries executing
- [ ] File uploads working
- [ ] Email notifications sending
- [ ] SMS notifications sending (if enabled)
- [ ] Admin panel accessible
- [ ] Error logs show no critical errors

### First 24 Hours

- [ ] Monitor error logs continuously
- [ ] Check database performance
- [ ] Verify backup jobs running
- [ ] Monitor disk space
- [ ] Check SSL certificate status
- [ ] Review security logs for anomalies

### First Week

- [ ] Run full smoke test suite
- [ ] Review application performance metrics
- [ ] Verify all cron jobs executing
- [ ] Check backup integrity
- [ ] Review user feedback
- [ ] Monitor error rates

---

## Security Checklist

### Pre-Launch Security Verification

- [ ] `APP_DEBUG=false` in production .env
- [ ] `.env` file not accessible via web server
- [ ] Strong database password configured
- [ ] SSL certificate installed and valid
- [ ] HTTPS enforced (HTTP → HTTPS redirect)
- [ ] Security headers configured (CSP, HSTS, X-Frame-Options)
- [ ] CSRF protection enabled on all forms
- [ ] XSS protection enabled
- [ ] SQL injection prevention verified (PDO prepared statements)
- [ ] Session security configured (Secure, HttpOnly, SameSite)
- [ ] Rate limiting enabled
- [ ] OTP system functional
- [ ] Password hashing uses bcrypt (cost ≥ 12)
- [ ] File upload validation enabled
- [ ] Directory traversal prevention verified
- [ ] Error reporting disabled in production
- [ ] Error logging enabled
- [ ] Database user has minimal privileges
- [ ] SSH access secured (key-based auth only)
- [ ] Firewall configured (only ports 80, 443, 22)
- [ ] Unnecessary PHP functions disabled

---

## Performance Checklist

### Pre-Launch Performance Verification

- [ ] Database indexes optimized
- [ ] Slow query log enabled
- [ ] Query cache configured (if applicable)
- [ ] OPcache enabled in PHP
- [ ] Gzip compression enabled
- [ ] Browser caching configured
- [ ] Static assets minified
- [ ] CDN configured (if applicable)
- [ ] Image optimization completed
- [ ] Database connection pooling configured

---

## Support & Documentation

### Required Documentation

- [ ] `INSTALL.md` - Installation guide
- [ ] `DEPLOYMENT.md` - Deployment procedures
- [ ] `ADMIN_GUIDE.md` - Administrator manual
- [ ] `USER_GUIDE.md` - End-user manual
- [ ] `BACKUP_RESTORE.md` - Disaster recovery procedures
- [ ] `SECURITY.md` - Security policies
- [ ] `CHANGELOG.md` - Version history
- [ ] `RELEASE_NOTES_v1.0.0.md` - Release notes

### Support Contacts

- **Technical Lead:** [Your Name]
- **Email:** support@kvnconstruction.com
- **Emergency Hotline:** [Phone Number]

---

## Rollback Plan

### Emergency Rollback Procedure

1. **Immediate Actions**
   ```bash
   # Enable maintenance mode
   sudo systemctl stop apache2
   
   # Restore previous version
   git checkout v0.9.0
   
   # Restore previous database backup
   mysql -u kvnc_prod -p kvnc_platform < backups/db_20260806_120000.sql
   ```

2. **Verification**
   ```bash
   # Test application
   sudo systemctl start apache2
   curl https://kvnconstruction.com/health.php
   ```

3. **Communication**
   - Notify stakeholders
   - Document incident
   - Schedule post-mortem

---

## Approval Signatures

### Technical Approval

- [ ] **Senior PHP Engineer** - Code Review Complete
- [ ] **DevOps Engineer** - Infrastructure Ready
- [ ] **Security Engineer** - Security Audit Passed
- [ ] **QA Engineer** - Testing Complete
- [ ] **Release Manager** - Approved for Production

### Business Approval

- [ ] **Project Manager** - Business Requirements Met
- [ ] **Client Representative** - User Acceptance Complete

---

## Document Control

| Version | Date | Author | Changes |
|---------|------|--------|---------|
| 1.0 | 2026-08-06 | Principal Software Architect | Initial production configuration guide |

---

## Appendix A: Environment Variable Validation Script

```php
<?php
// scripts/validate_env.php

$required = [
    'DB_HOST', 'DB_PORT', 'DB_NAME', 'DB_USER', 'DB_PASS',
    'APP_NAME', 'APP_URL', 'APP_ENV', 'APP_DEBUG', 'APP_KEY',
    'SESSION_NAME', 'SESSION_TIMEOUT',
    'OTP_EXPIRY_MINUTES', 'OTP_MAX_ATTEMPTS',
    'MAIL_DRIVER', 'MAIL_HOST', 'MAIL_PORT', 'MAIL_USERNAME', 'MAIL_PASSWORD',
    'CSRF_TOKEN_EXPIRY', 'HASH_COST'
];

$missing = [];
foreach ($required as $var) {
    if (empty(getenv($var))) {
        $missing[] = $var;
    }
}

if (!empty($missing)) {
    echo "ERROR: Missing required environment variables:\n";
    foreach ($missing as $var) {
        echo "  - $var\n";
    }
    exit(1);
}

echo "OK: All required environment variables configured.\n";
exit(0);
```

## Appendix B: Database Connection Test

```php
<?php
// scripts/test_db_connection.php

require_once __DIR__ . '/../config/app.php';

try {
    $db = \App\Core\Database::getInstance();
    if (!$db->isConnected()) {
        throw new \Exception("Database connection failed");
    }
    
    $pdo = $db->getConnection();
    $stmt = $pdo->query("SELECT VERSION()");
    $version = $stmt->fetchColumn();
    
    echo "OK: Database connection successful (MySQL version: $version)\n";
    exit(0);
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
```

---

**END OF PRODUCTION CONFIGURATION GUIDE**