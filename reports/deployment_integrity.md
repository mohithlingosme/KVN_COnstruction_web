# Deployment Integrity Report

**Release:** v1.0.0-rc.1
**Status:** PASS (deployment tooling created/documented; docs corrected to match reality)

---

## Authoritative deployment mechanism

This is a **plain PHP** application (no Laravel, no Composer runtime autoloader,
no `artisan`). The authoritative deployment tooling is:

| Tool | Path | Role |
|------|------|------|
| Migration runner | `scripts/run_migrations.php` | Applies `database/schema.sql` + records in `schema_migrations`; `--seed` applies `database/seeders/001_defaults.sql`; `--fresh` drops/recreates DB (LOCAL ONLY). Idempotent. |
| Smoke test | `scripts/smoke_test.php` | Post-deploy verification (14 checks: DB, schema_migrations, triggers, OTP flow, auth, CMS pages, etc.). |
| OPcache clear | `scripts/clear_opcache.php` | Clears PHP OPcache after release. |
| Health endpoint | `public/health.php` | Live HTTP health check (DB connectivity + JSON status). |
| Deploy script | `deploy.sh` | Orchestrates the above in order. |

---

## Reference audit — every documented item checked

| Reference | Exists? | Works? | Action |
|-----------|---------|--------|--------|
| `scripts/run_migrations.php` | **YES** (created) | **YES** (verified `--fresh --seed`) | — |
| `scripts/clear_opcache.php` | **YES** (created) | **YES** | — |
| `scripts/smoke_test.php` | **YES** (created) | **YES** (13/14; only local APP_KEY placeholder) | document APP_KEY must be set in prod |
| `public/health.php` | **YES** (created) | **YES** | corrected doc to reference existing file |
| `deploy.sh` calls the above | **YES** | references now resolve | — |
| `artisan` (any command) | **NO** | N/A | removed from docs; replaced with project scripts |
| `database/triggers.sql` | **NO** | N/A | removed from docs; triggers live in `database/schema.sql` |
| `database/seeders/run.php` | **NO** | N/A | removed from docs; use `run_migrations.php --seed` |
| `scripts/run_migrations.php` in deploy | **YES** | OK | — |

---

## Documentation corrections applied

`PRODUCTION_CONFIGURATION.md`:
- **5.1 Cron Jobs**: removed `artisan schedule:run`, `artisan otp:cleanup`,
  `artisan reports:daily`, `artisan backup:database`. Replaced with plain-PHP
  guidance (`clear_opcache.php`, `run_migrations.php --seed`, `smoke_test.php`,
  `mysqldump` backups).
- **7.1 Health Endpoint**: changed "Create `public/health.php`" → references the
  existing shipped endpoint.
- **10.3 Database Setup**: removed `database/triggers.sql` and
  `database/seeders/run.php`; replaced with `php scripts/run_migrations.php`
  (+ `--seed`, `--fresh --seed` for local).

`deploy.sh` already correctly referenced the created `scripts/` tooling, so no
change was needed there. `README.md` references were already accurate
(`scripts/smoke_test.php`, `scripts/clear_opcache.php`).

---

## Clean-server procedure (validated)

```bash
cd /var/www/KVN_Construction
cp .env.example .env        # then set real production secrets (APP_KEY, DB, SMTP, SMS)

# 1. Apply schema (idempotent) + record in schema_migrations
php scripts/run_migrations.php

# 2. Apply required system seed (roles, permissions, settings, admin)
php scripts/run_migrations.php --seed

# 3. Clear OPcache
php scripts/clear_opcache.php

# 4. Health check
curl -s https://kvnconstruction.com/health.php

# 5. Smoke test
php scripts/smoke_test.php
```

This is executable from a clean server using only project tooling. No `artisan`,
no `database/triggers.sql`, no `database/seeders/run.php`.

---

## Conclusion

B7 is resolved. All deployment tooling referenced by docs/scripts now exists and
works. Documentation has been corrected to match the actual plain-PHP deployment
mechanism.

## Status: PASS

---
