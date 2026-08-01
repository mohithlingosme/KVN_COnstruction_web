# Production Checklist

## Security

- [ ] All Critical and High issues resolved
- [ ] CSRF protection implemented on all forms
- [ ] Security headers configured (CSP, X-Frame-Options, etc.)
- [ ] HTTPS enabled with valid SSL certificate
- [ ] .env file excluded from version control
- [ ] Password hashing using bcrypt/argon2
- [ ] Rate limiting on authentication endpoints
- [ ] File upload validation and sanitization
- [ ] SQL injection prevention (prepared statements)
- [ ] XSS prevention (output escaping)

## Performance

- [ ] OPcache enabled
- [ ] Static assets minified and compressed
- [ ] Images optimized
- [ ] Database indexes added
- [ ] Caching headers configured
- [ ] CDN configured for static assets

## Monitoring

- [ ] Error logging configured
- [ ] Server monitoring set up
- [ ] Backup system operational
- [ ] Cron jobs configured
- [ ] Alert system configured
