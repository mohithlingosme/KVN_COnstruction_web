# TODO.md — Final Repository Stabilization & Production Readiness

## P0 — Critical (Must Complete Before Any New Features)

### Routing & URL Resolution

* [ ] Fix all "URL Not Found" errors.
* [ ] Validate all App Router routes.
* [ ] Validate all dynamic routes.
* [ ] Verify route groups.
* [ ] Verify nested layouts.
* [ ] Verify route metadata generation.
* [ ] Verify sitemap generation.
* [ ] Verify robots.txt generation.
* [ ] Verify redirects.
* [ ] Remove broken links.
* [ ] Validate all navigation menus.

### API Validation

* [ ] Test every API endpoint.
* [ ] Verify request validation.
* [ ] Verify response schemas.
* [ ] Verify error handling.
* [ ] Verify authentication middleware.
* [ ] Verify authorization checks.
* [ ] Verify rate limiting.
* [ ] Remove unused endpoints.
* [ ] Fix all endpoint path mismatches.

### Build Stability

* [ ] Zero TypeScript errors.
* [ ] Zero ESLint errors.
* [ ] Zero build warnings.
* [ ] Production build succeeds.
* [ ] Development build succeeds.
* [ ] CI build succeeds.

### Authentication

* [ ] Verify Supabase authentication.
* [ ] Verify OTP flow.
* [ ] Verify session handling.
* [ ] Verify logout functionality.
* [ ] Verify protected routes.
* [ ] Verify role-based access.

---

# P1 — Database & Backend Hardening

### Supabase

* [ ] Validate schema.sql.
* [ ] Validate seed.sql.
* [ ] Verify migrations.
* [ ] Verify RLS policies.
* [ ] Verify storage buckets.
* [ ] Verify permissions.

### Data Integrity

* [ ] Fix foreign key issues.
* [ ] Fix nullable field issues.
* [ ] Remove duplicate records.
* [ ] Add missing indexes.
* [ ] Optimize slow queries.

### Backend Quality

* [ ] Centralize error handling.
* [ ] Centralize logging.
* [ ] Add request tracing.
* [ ] Add audit logging.
* [ ] Add monitoring hooks.

---

# P1 — Frontend Quality

### UI Validation

* [ ] Verify all public pages.
* [ ] Verify admin dashboard.
* [ ] Verify client portal.
* [ ] Verify estimator module.
* [ ] Verify forms.
* [ ] Verify uploads.
* [ ] Verify payment pages.

### Responsive Testing

* [ ] Mobile testing.
* [ ] Tablet testing.
* [ ] Desktop testing.
* [ ] Large-screen testing.

### Accessibility

* [ ] Fix accessibility violations.
* [ ] Verify keyboard navigation.
* [ ] Verify screen-reader support.
* [ ] Verify contrast compliance.

---

# P1 — Integrations

### Razorpay

* [ ] Verify payment flow.
* [ ] Verify callback handling.
* [ ] Verify webhook processing.
* [ ] Verify failed payment recovery.

### WhatsApp

* [ ] Verify message delivery.
* [ ] Verify webhook events.
* [ ] Verify template handling.

### Google Calendar

* [ ] Verify appointment creation.
* [ ] Verify updates.
* [ ] Verify cancellations.

---

# P2 — Testing

### Unit Testing

* [ ] Critical utilities.
* [ ] Business logic.
* [ ] Validation modules.
* [ ] API helpers.

### Integration Testing

* [ ] Authentication flow.
* [ ] Payment flow.
* [ ] Lead flow.
* [ ] Client portal flow.

### End-to-End Testing

* [ ] Lead submission.
* [ ] Appointment booking.
* [ ] Cost estimation.
* [ ] Login workflow.
* [ ] Dashboard workflow.
* [ ] Payment workflow.

### Route Testing

* [ ] Crawl all routes.
* [ ] Verify status codes.
* [ ] Verify redirects.
* [ ] Verify metadata.

---

# P2 — Performance

### Frontend

* [ ] Optimize images.
* [ ] Reduce bundle size.
* [ ] Remove unused packages.
* [ ] Enable lazy loading.
* [ ] Optimize hydration.

### Backend

* [ ] Optimize queries.
* [ ] Add caching.
* [ ] Reduce API latency.
* [ ] Optimize database access.

---

# P2 — Security

### Security Audit

* [ ] Verify environment variables.
* [ ] Verify secrets management.
* [ ] Verify CORS.
* [ ] Verify CSP headers.
* [ ] Verify XSS protection.
* [ ] Verify CSRF protection.
* [ ] Verify input sanitization.

### Production Hardening

* [ ] Security header validation.
* [ ] Dependency vulnerability scan.
* [ ] Remove exposed debug information.
* [ ] Remove development-only code.

---

# P3 — Production Deployment

### Infrastructure

* [ ] Production environment validation.
* [ ] Deployment script validation.
* [ ] Backup strategy.
* [ ] Recovery strategy.

### Monitoring

* [ ] Error tracking.
* [ ] Health checks.
* [ ] Uptime monitoring.
* [ ] Performance monitoring.

### Documentation

* [ ] Update deployment guide.
* [ ] Update environment guide.
* [ ] Update API documentation.
* [ ] Update architecture documentation.

---

# Final Exit Criteria

The project is considered COMPLETE only when:

* [ ] No 404 errors remain.
* [ ] No URL Not Found errors remain.
* [ ] All routes resolve correctly.
* [ ] All APIs respond successfully.
* [ ] Authentication works.
* [ ] Payments work.
* [ ] Database migrations succeed.
* [ ] Seed scripts succeed.
* [ ] TypeScript passes.
* [ ] ESLint passes.
* [ ] Tests pass.
* [ ] Production build succeeds.
* [ ] Deployment succeeds.
* [ ] Security review passes.
* [ ] Application is production-ready.
