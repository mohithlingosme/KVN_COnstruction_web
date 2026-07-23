#!/bin/bash
# ============================================
# KVN Construction Deployment Script
# ============================================
# Usage: bash deploy.sh [environment]
#   environment: production|staging (default: production)
# ============================================

set -euo pipefail

DEPLOY_ENV="${1:-production}"
DEPLOY_DIR="/var/www/kvn_construction"
REPO_URL="https://github.com/mohithlingosme/KVN_COnstruction_web.git"
BRANCH="${2:-main}"
TIMESTAMP=$(date +"%Y%m%d_%H%M%S")
BACKUP_DIR="/var/backups/kvn_construction/${TIMESTAMP}"

echo "============================================"
echo "KVN Construction Deployment"
echo "Environment: ${DEPLOY_ENV}"
echo "Timestamp: ${TIMESTAMP}"
echo "============================================"

# ============================================
# Pre-deployment checks
# ============================================
echo "[1/6] Running pre-deployment checks..."

if [ "${DEPLOY_ENV}" = "production" ]; then
    # Verify git status is clean
    if ! git diff --quiet HEAD; then
        echo "ERROR: Working directory has uncommitted changes. Commit or stash before deploying."
        exit 1
    fi
    
    # Verify PHP version (must be 8.x)
    PHP_VERSION=$(php -r "echo PHP_MAJOR_VERSION;")
    if [ "$PHP_VERSION" -lt 8 ]; then
        echo "ERROR: PHP 8.x+ required. Current: $(php -v | head -n1)"
        exit 1
    fi
    echo "PHP version check passed: $(php -v | head -n1)"
    
    # Check required extensions
    for ext in pdo mysql mbstring json openssl curl gd; do
        if ! php -m | grep -qi "$ext"; then
            echo "WARNING: PHP extension '$ext' not found"
        fi
    done
fi

# ============================================
# Backup
# ============================================
echo "[2/6] Creating backup..."
mkdir -p "${BACKUP_DIR}"

if [ -d "${DEPLOY_DIR}" ]; then
    cp -r "${DEPLOY_DIR}" "${BACKUP_DIR}/current"
    mysqldump -u root -p'' kvnc_platform > "${BACKUP_DIR}/database.sql" 2>/dev/null || \
        echo "Warning: Database backup skipped (ensure mysqldump is configured)"
    echo "Backup saved to: ${BACKUP_DIR}"
fi

# ============================================
# Pull latest code
# ============================================
echo "[3/6] Pulling latest code from ${BRANCH}..."

if [ -d "${DEPLOY_DIR}" ]; then
    cd "${DEPLOY_DIR}"
    git fetch origin
    git reset --hard "origin/${BRANCH}"
else
    git clone --branch "${BRANCH}" "${REPO_URL}" "${DEPLOY_DIR}"
    cd "${DEPLOY_DIR}"
fi

# ============================================
# Environment configuration
# ============================================
echo "[4/6] Configuring environment..."

if [ ! -f ".env" ]; then
    cp .env.example .env
    echo "Created .env from .env.example - please update credentials"
fi

if [ "${DEPLOY_ENV}" = "production" ]; then
    # Production-specific .env updates
    sed -i 's/APP_ENV=development/APP_ENV=production/' .env
    sed -i 's/APP_DEBUG=true/APP_DEBUG=false/' .env
    sed -i 's|APP_URL=http://localhost/KVN_Construction/public|APP_URL=https://kvnconstruction.com|' .env
    
    # Update config/app.php for production
    if [ -f "config/app.php" ]; then
        sed -i "s/define('APP_ENV', 'development');/define('APP_ENV', 'production');/" config/app.php
        sed -i "s/define('APP_DEBUG', true);/define('APP_DEBUG', false);/" config/app.php
        sed -i "s|define('APP_URL', 'http://localhost/KVN_Construction/public');|define('APP_URL', 'https://kvnconstruction.com');|" config/app.php
    fi
    
    echo "Environment configured for production."
fi

# ============================================
# Build assets (minify CSS/JS)
# ============================================
echo "[4b/6] Building assets..."
if [ -f "package.json" ]; then
    if command -v npm &> /dev/null; then
        npm install --production 2>/dev/null || true
        npm run build 2>/dev/null || echo "Warning: Asset build failed, using unminified versions"
    fi
fi

# ============================================
# File permissions
# ============================================
echo "[5/6] Setting file permissions..."

# Base directory permissions
find "${DEPLOY_DIR}" -type d -exec chmod 755 {} \;
find "${DEPLOY_DIR}" -type f -exec chmod 644 {} \;

# Writable directories (storage, uploads, cache)
chmod -R 775 "${DEPLOY_DIR}/storage"
chmod -R 775 "${DEPLOY_DIR}/uploads"
chmod -R 775 "${DEPLOY_DIR}/storage/cache"
chmod -R 775 "${DEPLOY_DIR}/storage/logs"

# Protected directories - read-only for web server
chmod -R 750 "${DEPLOY_DIR}/config"
chmod -R 750 "${DEPLOY_DIR}/core"
chmod -R 750 "${DEPLOY_DIR}/helpers"
chmod -R 750 "${DEPLOY_DIR}/routes"
chmod -R 750 "${DEPLOY_DIR}/middleware"

# Sensitive files
chmod 640 "${DEPLOY_DIR}/.env"
chmod 640 "${DEPLOY_DIR}/.env.example"

# Make scripts executable
find "${DEPLOY_DIR}/scripts" -name "*.php" -exec chmod 750 {} \;
chmod 750 "${DEPLOY_DIR}/deploy.sh"

echo "File permissions set."

# ============================================
# Database migrations
# ============================================
echo "[6/6] Running database migrations..."
if [ -f "${DEPLOY_DIR}/scripts/run_migrations.php" ]; then
    php "${DEPLOY_DIR}/scripts/run_migrations.php"
fi

# Run index migration for performance
if [ -f "${DEPLOY_DIR}/database/migration/index_migration.sql" ]; then
    echo "Running database index migration..."
    mysql -u root -p'' kvnc_platform < "${DEPLOY_DIR}/database/migration/index_migration.sql" 2>/dev/null || \
        echo "Warning: Index migration skipped or some indexes already exist"
fi

# ============================================
# Cache clear
# ============================================
echo "Clearing cache..."
rm -rf "${DEPLOY_DIR}/storage/cache/*" 2>/dev/null || true

# Clear OPcache if possible
if [ -f "${DEPLOY_DIR}/scripts/clear_opcache.php" ]; then
    php "${DEPLOY_DIR}/scripts/clear_opcache.php" 2>/dev/null || true
fi

# ============================================
# Restart services (if applicable)
# ============================================
if [ "${DEPLOY_ENV}" = "production" ]; then
    echo "Restarting PHP-FPM..."
    sudo systemctl restart php8.2-fpm 2>/dev/null || \
    sudo systemctl restart php8.1-fpm 2>/dev/null || \
    sudo systemctl restart php-fpm 2>/dev/null || \
        echo "Warning: Could not restart PHP-FPM (manual restart may be required)"
    
    echo "Reloading Apache..."
    sudo systemctl reload apache2 2>/dev/null || \
    sudo systemctl reload httpd 2>/dev/null || \
        echo "Warning: Could not reload Apache (manual reload may be required)"
fi

echo ""
echo "============================================"
echo "Deployment completed successfully!"
echo "Environment: ${DEPLOY_ENV}"
echo "Timestamp: ${TIMESTAMP}"
echo "============================================"

# Post-deployment reminders
if [ "${DEPLOY_ENV}" = "production" ]; then
    echo ""
    echo "=== POST-DEPLOYMENT CHECKLIST ==="
    echo "1. Verify SSL certificate is valid"
    echo "2. Test HTTPS redirect: https://kvnconstruction.com"
    echo "3. Check error log: storage/logs/error.log"
    echo "4. Verify database connection"
    echo "5. Test SMS/Email delivery"
    echo "6. Run smoke test: php scripts/smoke_test.php"
    echo "=================================="
fi