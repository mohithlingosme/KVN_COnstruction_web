# Database Refactoring Roadmap: KVN Constructions Platform

## Phase 1: Infrastructure & Standardization
- [ ] Implement `created_at`, `updated_at`, `deleted_at` on ALL tables.
- [ ] Replace `ENUM` status columns with normalized lookup tables (e.g., `lead_statuses`, `project_phases`).
- [ ] Standardize timestamp column naming across the schema.
- [ ] Implement soft-delete logic (Global `WHERE deleted_at IS NULL` scope).

## Phase 2: Security & Identity
- [ ] Refactor `users` table: add `failed_login_count`, `last_login_at`, `is_suspended`, `password_reset_token`.
- [ ] Create `user_sessions` (IP, device_fingerprint, token, expiry).
- [ ] Create `otp_logs` (purpose, attempt_count, expiry).
- [ ] Create `security_logs` (severity, event_type, ip, payload).
- [ ] Create `admin_audit_logs` (action_type, entity_id, old_values, new_values).

## Phase 3: RBAC & Tenant Isolation
- [ ] Implement `roles` and `permissions` (many-to-many).
- [ ] Add `tenant_id` (or `company_id`) to all core tables for future multi-tenancy.
- [ ] Create `user_roles` linking table.

## Phase 4: CRM & Lead Refactor
- [ ] Standardize `leads` schema with source tracking and assignment.
- [ ] Create `lead_followups` with activity timeline.
- [ ] Split `clients` profile from `users` authentication.

## Phase 5: Project Management & BOQ
- [ ] Create `project_milestones` and `task_dependencies`.
- [ ] Refactor `boq_master` and `boq_items` for dynamic versioning.
- [ ] Implement `project_finance` (invoices, GST/TDS tax tracking).

## Phase 6: Dynamic Estimator Engine
- [ ] Build `package_engine` tables (Categories, Specifications).
- [ ] Implement `price_modifiers` (Location, City, Luxury, Automation).
- [ ] Create `estimator_rules` (Logic storage for area-based calculations).

## Phase 7: CMS & Media
- [ ] Standardize `cms_blocks` and `seo_metadata` (canonical, og_tags).
- [ ] Implement `media_library` (folder_id, optimization_metadata).

## Phase 8: Hardening & Performance
- [ ] Apply indexes: Composite indexes on high-query tables (`leads`, `projects`, `sessions`).
- [ ] Apply strict Foreign Key constraints (`ON DELETE RESTRICT`/`SET NULL`).
- [ ] Run integrity validation scripts.