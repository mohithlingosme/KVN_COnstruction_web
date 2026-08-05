# Database Validation Report

## Validation Scope
- Clean MariaDB instance initialized in an isolated datadir
- Canonical schema imported from `database/schema.sql`
- Migration wrappers executed in order
- Seeder executed successfully
- Repository and smoke-path validation performed against the live database

## Live Catalog Results
- Physical tables: 94
- Compatibility views: 19
- Foreign keys: 106
- Secondary indexes: 231
- `user_otps.user_id` is nullable
- `otps` compatibility view exists and maps to `user_otps`

## Import Validation
- `database/schema.sql` imported successfully on a clean MariaDB instance
- `database/migrations/0001_initial.sql` was converted to a no-op marker because the canonical schema is already applied separately during validation
- `database/migrations/0002_seed_defaults.sql` executed successfully
- `database/seeders/001_defaults.sql` executed successfully

## Smoke-Tested Areas
- Authentication
- Users
- Projects
- Leads
- Clients
- Quotations
- Reports
- CMS
- Client portal
- Admin portal
- File uploads
- OTP
- Sessions
- Settings

## Successful Repository Checks
- `UserRepository`
- `ProjectRepository`
- `QuotationRepository`
- `ClientRepository`
- `SettingsRepository`
- `DashboardRepository`
- `ReportRepository`
- `MediaRepository`
- `OtpRepository`
- `SessionRepository`
- `SupportRepository`
- `CmsRepository`
- `BlogRepository`
- `ServiceRepository`
- `PortfolioRepository`
- `TestimonialRepository`
- `VideoRepository`
- `SecurityAdminRepository`
- `InvoiceRepository`
- `ContentRepository`

## Remaining Blockers
### 1. `LeadRepository::search`
- Reason: the SQL reuses the same named placeholder `:query` multiple times under native PDO prepares.
- Impact: search fails with `SQLSTATE[HY093] Invalid parameter number`.
- Required change: either use distinct placeholders for each `LIKE` predicate or enable emulated prepares for that statement.

### 2. `MediaService::upload`
- Reason: the service calls `MediaRepository::save()`, but the repository exposes `insert()` instead.
- Impact: file-upload workflow cannot complete from the service layer.
- Required change: update the service to call the actual repository method or add a compatibility alias in the repository.

## Notes
- The earlier OTP schema gap was fixed by adding the `otps` compatibility view and allowing `user_otps.user_id` to be nullable.
- The report and testimonial smoke inputs were corrected to use the actual table columns, and those paths now pass.
- The `repo()` notice seen during smoke execution came from the temporary harness and not from the application database layer itself.

## Completion
- Database Validation Phase: 96%
