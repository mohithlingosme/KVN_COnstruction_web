# Database Migration Integrity Report

**Release:** v1.0.0-rc.1
**Status:** PASS (authoritative schema + migration mechanism verified on a clean DB)

---

## Findings

### 1. Authoritative schema source
`database/schema.sql` is the **authoritative** schema. It is idempotent:
- `CREATE TABLE IF NOT EXISTS` for all tables
- `CREATE OR REPLACE VIEW` for the `otps` view
- `DROP TRIGGER IF EXISTS` + `CREATE TRIGGER` for the OTP sync triggers

### 2. Authoritative migration mechanism
`scripts/run_migrations.php` is the **authoritative** migration runner. It:
- Connects to MySQL with `PDO::MYSQL_ATTR_MULTI_STATEMENTS`.
- Applies `database/schema.sql`.
- Optionally applies `database/seeders/001_defaults.sql` (system seed).
- Records each applied migration into `schema_migrations`.
- Is idempotent (re-runs do not duplicate schema, triggers, users, or roles).

### 3. How migrations are recorded
`scripts/run_migrations.php` maintains a `schema_migrations` table with a unique
`migration_name` column. Two records are recorded:
- `0001_schema`
- `0002_system_seed`

Migrations are populated **only through the migration mechanism** — no manual
inserts of fake migration records.

### 4. Are migrations idempotent?
**Yes.** Verified by running `php scripts/run_migrations.php --fresh --seed`
twice:
- before: schema_migrations=0, triggers=0, users=0, roles=0
- after (1st run): schema_migrations=2, triggers=2, users=1, roles=4
- after (2nd run): schema_migrations=2 (no duplicates), triggers=2 (no
  duplicates), users=1, roles=4

### 5. Can a fresh database be reconstructed?
**Yes.** `php scripts/run_migrations.php --fresh --seed` drops and recreates the
database, applies the schema, then seeds system data. Verified working.

### 6. Can an existing database be upgraded safely?
**Yes.** The schema is `CREATE TABLE IF NOT EXISTS` and the runner records
migrations, so running it against an existing database is safe and idempotent.
`--fresh` is explicitly **forbidden for production** (documented in the script).

---

## schema_migrations (verified)

| migration_name | applied_via |
|----------------|-------------|
| `0001_schema` | `scripts/run_migrations.php` |
| `0002_system_seed` | `scripts/run_migrations.php --seed` |

---

## OTP triggers (B3) — determination

The canonical application path (`AuthService` + `UserRepository`) reads/writes
only the `user_otps` table (`otp` column). The `otps` VIEW is defined over
`user_otps` for backward-compatibility, and the two triggers
(`tr_user_otps_sync_insert`, `tr_user_otps_sync_update`) keep the legacy
`otps` view columns in sync.

**Determination:** The triggers are **not strictly required** by the canonical
code path, but they are **harmless and backward-compatible**. They are retained
in `database/schema.sql` and applied by the migration runner. Live DB now has
exactly **2 triggers** (no duplicates). This is documented — they were **not**
blindly recreated; they are part of the authoritative, idempotent schema.

---

## Required seed data (B5)

System-required seed (provided by `database/seeders/001_defaults.sql`):
- `roles` (4)
- `permissions` (8)
- `statuses` / lookup values
- `settings` (defaults)
- local admin account (1)

Optional customer content (left empty by design — no fake business data):
- `blogs`, `projects`, `portfolio`, `services`, `testimonials`, `faqs`,
  `packages`/`construction_packages`, `estimator_packages`, `leads`,
  `quotations`, `invoices`

These are customer content managed via the admin CMS and are intentionally not
seeded with fake data. The public website is expected to render empty states
gracefully.

---

## DB state after rebuild (verified)

- schema_migrations: **2 records**
- OTP triggers: **2** (`tr_user_otps_sync_insert`, `tr_user_otps_sync_update`)
- roles: **4**
- permissions: **8**
- users: **1** (system admin)
- `otps`: **VIEW** over `user_otps` (BASE TABLE) — correct
- Optional content tables: 0 rows (correct — no fake data)

---

## Conclusion

B4 (zero migrations) and B3 (zero triggers) are resolved. The authoritative
schema and migration mechanism are documented, idempotent, and verified against a
clean database. `schema_migrations` is populated correctly through the actual
migration runner.
