# KVN Construction — v1.0.0-rc.1 Blocker Resolution TODO

Current release: **v1.0.0-rc.1** — NOT READY FOR PRODUCTION
Objective: Resolve P0/P1 blockers → Linux-compatible → OTP/auth working → DB migration verified → ready for full release audit.

## Phase 1 — Linux Case-Sensitivity Audit ✅
- [x] Scan entire repo for case-mismatched require/include/use/namespace/class paths
- [x] Fix all mismatches (controllers, repositories, services, Core, helpers, middleware, routes, public)
- [x] Create `reports/linux_case_sensitivity_audit.md`

## Phase 2 — OTP Service Normalization ✅
- [x] Confirm `OtpService.php`, `OtpRepository.php`, `AuthController.php` are orphaned (repo-wide ref scan)
- [x] Remove orphaned legacy files (Option A approved)
- [x] Update any active references to canonical `AuthService`/`OtpService`/`OtpRepository`
- [x] Verify all OTP handlers use canonical path
- [x] Create `reports/otp_validation.md`

## Phase 3 — Database Migration Integrity ✅
- [x] Confirm target is local dev DB (kvnc_platform), not production
- [x] Verify `database/schema.sql` is authoritative
- [x] Rebuild DB from schema.sql
- [x] Create migration runner (`scripts/run_migrations.php`) that records into schema_migrations
- [x] Create `reports/database_migration_integrity.md`

## Phase 4 — OTP Database Triggers ✅
- [x] Rebuild applies `tr_user_otps_sync_insert` + `tr_user_otps_sync_update`
- [x] Verify exactly 2 triggers, idempotent
- [x] Document trigger necessity

## Phase 5 — Required System Seed Data ✅
- [x] Seed only required system data (roles, permissions, statuses, admin, settings, base services)
- [x] Ensure optional content tables have graceful empty states
- [x] Do NOT insert fake customer/content data

## Phase 6 — Environment Hardening ✅
- [x] Fix `.env.example` → production, APP_DEBUG=false, secure APP_KEY pattern
- [x] Ensure all secrets via env vars, `.env` gitignored

## Phase 7 — Deployment Tooling ✅
- [x] Create authoritative migration/deploy mechanism (`scripts/run_migrations.php`, `scripts/clear_opcache.php`, `scripts/smoke_test.php`, `public/health.php`)
- [x] Correct PRODUCTION_CONFIGURATION.md to match reality (removed artisan/triggers.sql/seeders-run.php)
- [x] Create `reports/deployment_integrity.md`

## Phase 8 — HTTPS Readiness ✅ (documented; live SSL NOT TESTED)
- [x] Document secure cookies, HSTS, redirect, trusted proxies
- [ ] ⚠️ Live SSL cert validation on production host (out of scope for local env)

## Phase 9 — Validation ✅
- [x] PHP lint, case audit, composer/autoload, DB test, repo smoke, OTP tests, auth tests, migration test, seed test, public/admin/client smoke, deploy tooling test

## Phase 10 — Regression Protection ✅
- [x] Auth, OTP, password reset, sessions, admin/client login, CMS, projects, leads, quotations, reports, settings, uploads, public site

## Reports ✅
- [x] `reports/linux_case_sensitivity_audit.md`
- [x] `reports/otp_validation.md`
- [x] `reports/database_migration_integrity.md`
- [x] `reports/deployment_integrity.md`
- [x] `reports/production_blocker_resolution.md`
- [x] `reports/rc1_validation.md`

## STOP CONDITIONS (do NOT declare production ready)
- [x] Zero Linux case-sensitivity errors
- [x] One canonical OTP implementation
- [x] OTP auth tests pass
- [x] DB migration mechanism verified
- [x] schema_migrations populated through migrations
- [x] Required OTP DB behavior verified
- [x] Required system seed data verified
- [x] Optional content graceful empty states
- [x] Production env config documented
- [x] No production secrets committed
- [x] Deployment tooling works
- [x] Deployment docs match reality
- [x] HTTPS requirements verified/documented (⚠️ live SSL pending)
- [x] PHP lint passes
- [x] Regression tests pass

## Final Status
**v1.0.0-rc.1 → BLOCKERS RESOLVED → FULL RELEASE AUDIT REQUIRED**
Not production-ready until a separate full release audit (incl. live HTTPS) passes.
