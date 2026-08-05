# Database Analysis

## Scope
I scanned the current PHP codebase, with primary focus on repository classes, service classes, controllers, and the direct SQL entrypoints in `public/`.

## Summary

| Metric | Value |
|---|---:|
| Physical tables in the new schema | 94 |
| Compatibility views | 18 |
| Foreign key constraints | 106 |
| Index definitions | 347 |

## Repository Map

### Core identity and security
- `UserRepository`
- `SessionRepository`
- `OtpRepository`
- `AuditRepository`
- `SecurityAdminRepository`
- `RateLimitRepository`
- `MailRepository`
- `SmsRepository`

### Content and CMS
- `BlogRepository`
- `ContentRepository`
- `CmsRepository`
- `ServiceRepository`
- `PortfolioRepository`
- `TestimonialRepository`
- `VideoRepository`

### Lead, project, quotation, and support workflows
- `LeadRepository`
- `ProjectRepository`
- `QuotationRepository`
- `SupportRepository`
- `InvoiceRepository`
- `MediaRepository`
- `DashboardRepository`

### Estimator and reporting
- `EstimatorRepository`
- `ReportRepository`

## Dominant Query Patterns
- Read-heavy `SELECT *` flows for admin listing pages.
- Client-scoped lookups by `client_id` across documents, payments, quotations, projects, and support.
- Text-search and listing queries on `blogs`, `portfolio`, `services`, and `leads`.
- Transactional inserts for quotations with line items and estimator submissions.
- Soft-delete aware reads via `deleted_at IS NULL` on core domain tables.
- Generic dashboard queries that expect `title`, `name`, `status`, `created_at`, and `updated_at` to exist on multiple tables.

## Compatibility Findings
- The application expects both canonical and alias-style column names in a few places:
  - `users.password` and `users.password_hash`
  - `user_otps.otp` and `user_otps.otp_hash`
  - `leads.full_name`, `leads.name`, and `leads.title`
  - `quotations.quotation_number`, `quotations.quotation_no`, `quotations.valid_till`, and `quotations.valid_until`
- The dashboard helper reads `title` and `name` from several tables, so those alias columns are now present on the corresponding entities.

## Notable Risk
- `MediaService::upload()` calls `MediaRepository::save()`, but the repository currently exposes `insert()` instead. This is a code-level compatibility issue, not a schema issue, and it remains the main blocker for the media upload path.

## Outcome
- The schema has been rebuilt from the current codebase, not from prior SQL dumps.
- Query coverage is strong for the current repository layer, with compatibility views added where the code still refers to legacy table names.
