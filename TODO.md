# KVN Construction - Phase 6 Migration TODO

## Completed

### ✅ Repository Infrastructure (100%)
- All SQL centralized in Repository classes
- 25+ repositories covering all database tables

### ✅ Service Infrastructure (100%)
- Services contain business logic only
- AuthService, InvoiceService, ClientService, AdminCmsService, OtpService

### ✅ Public Website (100%)
- contact.php, about-us.php, project-details.php, blog-details.php migrated
- Zero SQL, uses Service/Repository pattern

### ✅ Admin CMS (100%)
- about.php, contact.php, homepage.php, seo.php, faq.php migrated
- Uses AdminCmsService → CmsRepository

### ✅ Middleware (100% SQL-free)
- admin.php, auth.php, clients.php — all SQL migrated to repositories

### ✅ Client Portal (100% - 32/32 files)
- dashboard.php
- payments/index.php, invoices.php, receipts.php, transactions.php
- documents/index.php, permits.php, agreements.php, downloads.php
- profile/index.php, edit.php, password.php, notifications.php
- projects/index.php, view.php, gallery.php, milestones.php, updates.php
- quotations/index.php, view.php, approvals.php, downloads.php
- support/tickets.php, messages.php, create-ticket.php
- timeline/index.php, schedules.php, progress.php
- uploads/images.php, videos.php, testimonials.php, feedback.php

## In Progress

### 🔄 Phase 6: Admin Modules Migration (29 files remaining)

#### Phase A - Repository extensions
- [ ] Make BlogRepository, QuotationRepository, ProjectRepository, LeadRepository constructors accept nullable PDO
- [ ] Add admin CRUD methods to BlogRepository (blogs, categories, tags, comments)
- [ ] Add status/items methods to QuotationRepository
- [ ] Add unblockUser to SecurityAdminRepository

#### Module 5 - Quotations (5 files)
- [ ] admin/quotations/index.php
- [ ] admin/quotations/approvals.php
- [ ] admin/quotations/create.php
- [ ] admin/quotations/edit.php
- [ ] admin/quotations/pdf.php

#### Module 6 - Reports (5) + Dashboard
- [ ] admin/reports/revenue.php
- [ ] admin/reports/estimators.php
- [ ] admin/reports/leads.php
- [ ] admin/reports/projects.php
- [ ] admin/reports/quotations.php
- [ ] admin/dashboard.php (remove $conn)

#### Module 7 - Settings (6) + Security (5)
- [ ] admin/settings/general.php
- [ ] admin/settings/integrations.php
- [ ] admin/settings/security.php
- [ ] admin/settings/seo.php
- [ ] admin/settings/sms.php
- [ ] admin/settings/smtp.php
- [ ] admin/security/audit-logs.php
- [ ] admin/security/blocked-users.php
- [ ] admin/security/login-attempts.php
- [ ] admin/security/logs.php
- [ ] admin/security/sessions.php

#### Blogs (6 files)
- [ ] admin/blogs/index.php
- [ ] admin/blogs/create.php
- [ ] admin/blogs/edit.php
- [ ] admin/blogs/categories.php
- [ ] admin/blogs/tags.php
- [ ] admin/blogs/comments.php

#### Root (1 file)
- [ ] admin/logout.php

### Validation & Reports
- [ ] PHP lint all modified files
- [ ] Re-run _scan_admin_sql.php → expect 0 files with SQL
- [ ] Update reports/repository_migration.md
- [ ] Update reports/repository_coverage.md
- [ ] Update reports/legacy_elimination.md

## Next Steps
1. Complete Admin modules (Phase 6)
2. Final SQL audit across entire codebase

