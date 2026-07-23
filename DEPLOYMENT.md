# KVN Construction - Deployment Guide

## Prerequisites
- PHP 8.2+
- MySQL/MariaDB 10.4+
- Apache with mod_rewrite
- Composer (optional, for dependencies)
- Node.js 18+ (for asset compilation)

## Local Development Setup

### Using Docker (Recommended)
```bash
# Start containers
docker-compose up -d

# Access
# - Application: http://localhost:8080
# - phpMyAdmin: http://localhost:8081
# - MailHog:    http://localhost:8025

# Run migrations
docker-compose exec web php scripts/migrate.php
```

### Manual Setup (XAMPP/WAMP)
1. Clone repo to `htdocs/KVN_Construction`
2. Create MySQL database `kvnc_platform`
3. Import `database/migration/index_migration.sql`
4. Import `database/migration/consolidate_duplicate_tables.sql`
5. Copy `.env.example` to `.env` and configure
6. Access via `http://localhost/KVN_Construction/public/`

## Production Deployment

### Step 1: Server Requirements
```bash
# PHP 8.2+ extensions required:
php -m | grep -E "pdo|mysql|mbstring|xml|curl|gd|intl|bcmath|openssl"
```

### Step 2: Upload Files
```bash
# Upload to web root (e.g., /var/www/html)
rsync -avz --exclude '.env' --exclude 'node_modules' \
  --exclude '.git' --exclude 'docker*' \
  ./ user@server:/var/www/html/kvn-construction/
```

### Step 3: Environment Configuration
```bash
# Create .env from template
cp .env.example .env

# EDIT .env with production values:
# - APP_URL=https://kvnconstruction.com
# - APP_ENV=production
# - APP_DEBUG=false
# - DB_HOST, DB_NAME, DB_USER, DB_PASS
# - MAIL_HOST, MAIL_USERNAME, MAIL_PASSWORD
# - SMS_API_KEY, SMS_API_SECRET
# - APP_KEY (generate: openssl rand -base64 32)
```

### Step 4: Database Setup
```bash
# Create database
mysql -u root -p -e "CREATE DATABASE kvnc_platform"

# Import schema
mysql -u root -p kvnc_platform < database/migration/index_migration.sql
mysql -u root -p kvnc_platform < database/migration/consolidate_duplicate_tables.sql

# Admin password: Change immediately after first login
```

### Step 5: File Permissions
```bash
chmod -R 775 storage/logs storage/cache uploads public/uploads
chmod 600 .env
chown -R www-data:www-data .
```

### Step 6: Apache Configuration
```apache
<VirtualHost *:80>
    ServerName kvnconstruction.com
    DocumentRoot /var/www/html/kvn-construction/public
    
    <Directory /var/www/html/kvn-construction/public>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
    
    ErrorLog ${APACHE_LOG_DIR}/kvn-error.log
    CustomLog ${APACHE_LOG_DIR}/kvn-access.log combined
</VirtualHost>
```

### Step 7: Enable HTTPS (SSL)
```bash
# Install Let's Encrypt
certbot --apache -d kvnconstruction.com

# Uncomment HTTPS redirect in .htaccess (lines 5-6)
# RewriteCond %{HTTPS} off
# RewriteRule ^(.*)$ https://%{HTTP_HOST}/$1 [R=301,L]
```

### Step 8: Security Checklist
- [ ] Change all default passwords
- [ ] Enable HTTPS redirect
- [ ] Configure CSP headers (already in .htaccess)
- [ ] Set `APP_DEBUG=false`
- [ ] Disable directory listing
- [ ] Configure SMTP for email delivery
- [ ] Configure SMS gateway (Twilio)
- [ ] Set up reCAPTCHA keys
- [ ] Regular security updates

### Step 9: Cron Jobs
```bash
# Add to crontab (crontab -e)
# Daily database backup
0 3 * * * /usr/bin/mysqldump -u root kvnc_platform > /backups/kvnc_$(date +\%Y\%m\%d).sql

# Cleanup old logs (daily)
0 4 * * * php /var/www/html/kvn-construction/scripts/cleanup_logs.php

# Session cleanup (hourly)
0 * * * * php /var/www/html/kvn-construction/scripts/cleanup_sessions.php
```

## Post-Deployment Verification

### Essential Checks
```bash
# 1. Check PHP errors
tail -f storage/logs/error.log

# 2. Test homepage
curl -I https://kvnconstruction.com

# 3. Test API
curl https://kvnconstruction.com/api/estimator?action=packages

# 4. Test database
php scripts/test_db_connection.php

# 5. Test email
php scripts/test_email.php

# 6. Test SMS OTP
php scripts/test_otp.php
```

### Page Checklist
- [ ] Homepage loads without errors
- [ ] About Us page
- [ ] Services page
- [ ] Projects/Portfolio
- [ ] Blog page
- [ ] Contact form submits
- [ ] Estimator calculates correctly
- [ ] Admin login works
- [ ] Client login (OTP) works
- [ ] 404 page displayed for missing routes
- [ ] All images load
- [ ] Mobile responsive

## Monitoring

### Log Files
```
storage/logs/error.log        - PHP errors
storage/logs/audit.log        - Audit trail
storage/logs/security.log     - Security events
```

### Uptime Monitoring
Set up external monitoring for:
- https://kvnconstruction.com
- https://kvnconstruction.com/admin/login.php

### Backup Strategy
- **Daily**: Database dump + uploads directory
- **Weekly**: Full codebase backup
- **Retention**: 30 days daily, 12 months weekly
- **Storage**: Separate server or cloud storage