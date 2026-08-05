# Database Reconstruction Checklist

## Completed
- [x] Scanned repository SQL usage in repositories, services, controllers, and direct SQL pages
- [x] Identified core entities and lookup tables
- [x] Rebuilt a normalized schema from current application behavior
- [x] Added compatibility views for legacy table names
- [x] Added alias columns where the code uses alternate names for the same data
- [x] Added foreign keys for major parent-child relationships
- [x] Added search and compound indexes for portal and admin workloads
- [x] Added bootstrap migrations
- [x] Added development seed data for roles, permissions, admin, and lookup values
- [x] Documented the design and validation status

## Verify After Import
- [ ] Smoke-test OTP insert/verify flows
- [ ] Smoke-test quotations create/edit/update flows
- [ ] Smoke-test estimator submission and reporting flows
- [ ] Smoke-test media upload flow
- [ ] Smoke-test client portal lists and dashboards
- [ ] Confirm compatibility views are writable where the app inserts into them

## Known Follow-Up
- Media repository needs a `save()` compatibility method or the service needs to call `insert()`.
- If any hidden page writes through a compatibility view that MariaDB rejects as non-updatable, convert that path to a base table directly and document the change.
