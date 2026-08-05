# Database Design

## Design Goals
- Match the current PHP repositories without changing application logic.
- Preserve 3NF for core domain entities.
- Use compatibility views only where the codebase still references legacy table names.
- Add soft deletes, audit columns, and lookup tables where the application already expects them.

## Core Design Decisions

### Identity
- `users` is the primary authentication record.
- `clients` stores portal-specific client metadata.
- `roles`, `permissions`, `user_roles`, and `role_permissions` support RBAC.

### Security
- Session and token data lives in `user_sessions`, `remember_tokens`, and `user_otps`.
- Security telemetry is split into `security_logs`, `audit_logs`, `login_attempts`, `blocked_users`, and `admin_sessions`.
- `rate_limits`, `mail_logs`, and `sms_logs` support operational hardening.

### Content
- Blogs, portfolio, services, testimonials, videos, and FAQs are modeled as separate entities to keep each content type queryable and indexable.
- CMS pages use single-row tables for the static sections the admin screens edit.

### Operations
- Leads, estimators, projects, quotations, payments, invoices, and support tickets are separated by business function.
- Project assets are stored in `project_media`, `project_gallery`, `project_updates`, `project_files`, and `project_tasks`.

### Compatibility Layer
- Read-only or legacy names are exposed as views, including:
  - `blog_posts`
  - `portfolio_projects`
  - `client_projects`
  - `client_payments`
  - `project_schedules`
  - `client_schedules`
  - `media_library`
  - `client_files`
  - `estimators`
  - `estimator_leads`

## Normalization Status
- Core tables are designed to 3NF.
- Compatibility views intentionally break neither storage normalization nor application compatibility.
- A few alias columns exist where the codebase writes different names for the same concept.

## Audit Columns
- The schema uses `created_at`, `updated_at`, and `deleted_at` consistently on transactional and content tables.
- Additional ownership columns such as `created_by`, `updated_by`, and `approved_by` are included where the code already uses them.
