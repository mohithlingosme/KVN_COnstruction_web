# Query Validation

## Validation Method
- Reviewed repository SQL in:
  - `app/repositories/`
  - `app/services/`
  - `app/controllers/`
  - direct SQL pages under `public/`
- Mapped each `SELECT`, `INSERT`, `UPDATE`, `DELETE`, `JOIN`, and transaction flow to the rebuilt schema.

## Validation Result

| Area | Status |
|---|---|
| Auth and sessions | Compatible |
| Security logging and blocking | Compatible |
| CMS and SEO | Compatible |
| Blog, portfolio, services, testimonials, videos | Compatible |
| Leads and estimator pipeline | Compatible |
| Projects, milestones, updates, files, media | Compatible |
| Quotations and quotation items | Compatible |
| Client portal records | Compatible |
| Payments, invoices, receipts | Compatible |
| Support tickets and messages | Compatible |
| Reports | Compatible with compatibility views |

## Compatibility Notes
- The schema adds alias columns for the dashboard helper and mixed repository naming conventions.
- The schema uses views for legacy table names so current pages can keep querying them unchanged.

## Remaining Blockers
- `MediaService::upload()` calls `MediaRepository::save()`, but the repository only defines `insert()`.
- `ReportRepository::insertEstimatorReport()` targets the `estimators` compatibility view; it should work if the view remains updatable, but that should be exercised in a live MariaDB smoke test.

## Conclusion
- No repository query was found that requires changing the PHP application to fit the new schema, with the media upload method mismatch called out separately as an application-level issue.
