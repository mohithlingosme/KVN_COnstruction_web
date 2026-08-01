# Apache Environment

| Property | Value |
|---|---|
| Apache Version | XAMPP httpd detected (httpd.exe present) |
| Virtual Host Config | docker/apache/vhost.conf |

## Virtual Host Configuration
```apache
<VirtualHost *:80>
    ServerAdmin admin@kvnconstruction.com
    DocumentRoot /var/www/html/public

    <Directory /var/www/html/public>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    <Directory /var/www/html/uploads>
        Options -Indexes
        AllowOverride None
        Require all granted
    </Directory>

    # Deny access to sensitive directories
    <DirectoryMatch ^/var/www/html/(config|core|database|bootstrap|helpers|middleware|app|routes|storage|scripts|docker|\.git)>
        Require all denied
    </DirectoryMatch>

    ErrorLog ${APACHE_LOG_DIR}/error.log
    CustomLog ${APACHE_LOG_DIR}/access.log combined

    # Security Headers
    Header always set X-Frame-Options "SAMEORIGIN"
    Header always set X-Content-Type-Options "nosniff"
    Header always set X-XSS-Protection "1; mode=block"
    Header always set Referrer-Policy "strict-origin-when-cross-origin"
</VirtualHost>
```
