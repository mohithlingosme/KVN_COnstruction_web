# Index Strategy

## Strategy
- Index all foreign key columns.
- Support client-scoped filters with compound indexes.
- Support admin listing queries with `status`, `created_at`, and `updated_at`.
- Add fulltext indexes only where the code searches textual content.

## High-Value Indexes

| Table | Index Purpose |
|---|---|
| `users` | unique email/phone, role/status lookups, last login reporting |
| `leads` | status timeline, assignment lookups, fulltext search |
| `projects` | client/status filters, date range reporting, fulltext search |
| `quotations` | client/project filters, status reporting, unique quote number |
| `quotation_items` | parent quotation fetches |
| `support_tickets` | client/status queue views |
| `support_messages` | ticket message timelines |
| `blog_categories` | slug uniqueness |
| `blog_tags` | slug uniqueness |
| `blogs` | status/published ordering, slug lookup, fulltext search |
| `portfolio` | slug uniqueness, featured listing, status listing |
| `testimonials` | status/featured listing |
| `construction_packages` | slug uniqueness, active package listing |
| `estimator_requests` | package/status queue views |
| `rate_limits` | key lookup and expiry cleanup |

## Compound Index Themes
- `client_id, status` for client dashboards and portals.
- `status, created_at` for admin tables and dashboards.
- `project_id, created_at` for project asset lists.
- `lead_id, created_at` for lead follow-up chronology.

## Fulltext Coverage
- `blogs`
- `leads`
- `projects`
- `portfolio`
- `construction_packages`

## Duplicate Index Review
- The schema intentionally avoids duplicate storage indexes on the same column combination.
- Compatibility views do not carry their own storage indexes because they inherit the underlying table structure.
