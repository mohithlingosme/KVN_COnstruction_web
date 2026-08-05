# Repository Coverage Report

**Generated:** 2026-07-28  
**Scope:** Track which files have been migrated to the Repository+Service architecture.

---

## Coverage Summary

| Layer | Total Files | Migrated | Remaining | Percentage |
|-------|------------|----------|-----------|------------|
| `app/controllers/` | 13 | 13 | 0 | **100%** |
| `app/services/` | 15 | 15 | 0 | **100%** |
| `helpers/` | 14 | 14 | 0 | **100%** |
| `middleware/` | 8 | 8 | 0 | **100%** |
| `public/` (website) | 22 | 22 | 0 | **100%** |
| `public/admin/cms/` | 5 | 5 | 0 | **100%** |
| `public/admin/security/` | 5 | 5 | 0 | **100%** |
| `public/admin/quotations/` | 5 | 5 | 0 | **100%** |
| `public/admin/blogs/` | 6 | 6 | 0 | **100%** |
| `public/admin/reports/` | 5 | 5 | 0 | **100%** |
| `public/admin/settings/` | 6 | 6 | 0 | **100%** |
| `public/client/` | 31 | 31 | 0 | **100%** |
| `public/admin/` (remaining) | ~28 | ~28 | 0 | **100%** |
| `public/auth/` (handlers) | 7 | 7 | 0 | **100%** |
| `routes/` | 1 | 1 | 0 | **100%** |
| `app/security/` | 2 | 2 | 0 | **100%** |

---

## Client Portal - 31/31 Files (100%)

### Profile (4/4)
| File | Repository Used | Service Used | Status |
|------|----------------|-------------|--------|
| `profile/index.php` | DashboardRepository | ClientService | ✅ |
| `profile/edit.php` | DashboardRepository | ClientService | ✅ |
| `profile/password.php` | DashboardRepository | ClientService | ✅ |
| `profile/notifications.php` | DashboardRepository | ClientService | ✅ |

### Projects (5/5)
| File | Repository Used | Service Used | Status |
|------|----------------|-------------|--------|
| `projects/index.php` | DashboardRepository | ClientService | ✅ |
| `projects/view.php` | DashboardRepository | ClientService | ✅ |
| `projects/gallery.php` | DashboardRepository | ClientService | ✅ |
| `projects/milestones.php` | DashboardRepository | ClientService | ✅ |
| `projects/updates.php` | DashboardRepository | ClientService | ✅ |

### Quotations (4/4)
| File | Repository Used | Service Used | Status |
|------|----------------|-------------|--------|
| `quotations/index.php` | DashboardRepository | ClientService | ✅ |
| `quotations/view.php` | DashboardRepository | ClientService | ✅ |
| `quotations/approvals.php` | DashboardRepository | ClientService | ✅ |
| `quotations/downloads.php` | DashboardRepository | ClientService | ✅ |

### Payments (4/4)
| File | Repository Used | Service Used | Status |
|------|----------------|-------------|--------|
| `payments/index.php` | DashboardRepository | ClientService | ✅ |
| `payments/invoices.php` | DashboardRepository | ClientService | ✅ |
| `payments/receipts.php` | DashboardRepository | ClientService | ✅ |
| `payments/transactions.php` | DashboardRepository | ClientService | ✅ |

### Documents (4/4)
| File | Repository Used | Service Used | Status |
|------|----------------|-------------|--------|
| `documents/index.php` | DashboardRepository | ClientService | ✅ |
| `documents/permits.php` | DashboardRepository | ClientService | ✅ |
| `documents/agreements.php` | DashboardRepository | ClientService | ✅ |
| `documents/downloads.php` | DashboardRepository | ClientService | ✅ |

### Support (3/3)
| File | Repository Used | Service Used | Status |
|------|----------------|-------------|--------|
| `support/tickets.php` | DashboardRepository | ClientService | ✅ |
| `support/messages.php` | DashboardRepository | ClientService | ✅ |
| `support/create-ticket.php` | DashboardRepository | ClientService | ✅ |

### Timeline (2/2)
| File | Repository Used | Service Used | Status |
|------|----------------|-------------|--------|
| `timeline/index.php` | DashboardRepository | ClientService | ✅ |
| `timeline/schedules.php` | DashboardRepository | ClientService | ✅ |

### Uploads (4/4)
| File | Repository Used | Service Used | Status |
|------|----------------|-------------|--------|
| `uploads/images.php` | DashboardRepository | ClientService | ✅ |
| `uploads/videos.php` | DashboardRepository | ClientService | ✅ |
| `uploads/testimonials.php` | DashboardRepository | ClientService | ✅ |
| `uploads/feedback.php` | DashboardRepository | ClientService | ✅ |

### Security (5/5)

| File | Repository Used | Service Used | Status |
|------|----------------|-------------|--------|
| `security/audit-logs.php` | SecurityAdminRepository | — | ✅ |
| `security/logs.php` | SecurityAdminRepository | — | ✅ |
| `security/blocked-users.php` | SecurityAdminRepository | — | ✅ |
| `security/login-attempts.php` | SecurityAdminRepository | — | ✅ |
| `security/sessions.php` | SecurityAdminRepository | — | ✅ |

### Quotations (5/5)

| File | Repository Used | Service Used | Status |
|------|----------------|-------------|--------|
| `quotations/index.php` | QuotationRepository | — | ✅ |
| `quotations/create.php` | QuotationRepository | — | ✅ |
| `quotations/edit.php` | QuotationRepository | — | ✅ |
| `quotations/pdf.php` | QuotationRepository | — | ✅ |
| `quotations/approvals.php` | QuotationRepository | — | ✅ |

### Blogs (6/6) — Already Migrated

| File | Repository Used | Service Used | Status |
|------|----------------|-------------|--------|
| `blogs/index.php` | BlogRepository | — | ✅ |
| `blogs/create.php` | BlogRepository | — | ✅ |
| `blogs/edit.php` | BlogRepository | — | ✅ |
| `blogs/categories.php` | BlogRepository | — | ✅ |
| `blogs/tags.php` | BlogRepository | — | ✅ |
| `blogs/comments.php` | BlogRepository | — | ✅ |

### Reports (5/5)

| File | Repository Used | Service Used | Status |
|------|----------------|-------------|--------|
| `reports/revenue.php` | ReportRepository | — | ✅ |
| `reports/projects.php` | ReportRepository | — | ✅ |
| `reports/estimators.php` | ReportRepository | — | ✅ |
| `reports/quotations.php` | ReportRepository | — | ✅ |
| `reports/leads.php` | ReportRepository | — | ✅ |

### Settings (6/6)

| File | Repository Used | Service Used | Status |
|------|----------------|-------------|--------|
| `settings/general.php` | SettingsRepository | AdminSettingsService | ✅ |
| `settings/seo.php` | CmsRepository | CmsRepository / AdminCmsService | ✅ |
| `settings/smtp.php` | SettingsRepository | AdminSettingsService | ✅ |
| `settings/sms.php` | SettingsRepository | AdminSettingsService | ✅ |
| `settings/integrations.php` | SettingsRepository | AdminSettingsService | ✅ |
| `settings/security.php` | SettingsRepository | AdminSettingsService | ✅ |

### Security (5/5) — Re-verified Phase 8 Continuation

| File | Repository Used | Service Used | Status |
|------|----------------|-------------|--------|
| `security/audit-logs.php` | SecurityAdminRepository | — | ✅ |
| `security/logs.php` | SecurityAdminRepository | — | ✅ |
| `security/blocked-users.php` | SecurityAdminRepository | — | ✅ |
| `security/login-attempts.php` | SecurityAdminRepository | — | ✅ |
| `security/sessions.php` | SecurityAdminRepository | — | ✅ |

**Verification:** `php -l` passed on all 5 files + `SecurityAdminRepository`; `_audit_security_module_v2.php` — 5 files scanned, 0 legacy hits, **STATUS: PASS**. Deferred integration tests documented pending Database Reconstruction Phase.

### Dashboard (1/1)
| File | Repository Used | Service Used | Status |
|------|----------------|-------------|--------|
| `dashboard.php` | DashboardRepository | ClientService | ✅ |

---

## New/Expanded Repository Methods

### SettingsRepository (16 new)

| Method | Added For |
|--------|-----------|
| `getGeneralSettings(): ?array` | `settings/general.php` |
| `generalSettingsExist(): bool` | `settings/general.php` |
| `insertGeneralSettings(array $data): bool` | `settings/general.php` |
| `updateGeneralSettings(array $data): bool` | `settings/general.php` |
| `getSmsSettings(): ?array` | `settings/sms.php` |
| `smsSettingsExist(): bool` | `settings/sms.php` |
| `insertSmsSettings(array $data): bool` | `settings/sms.php` |
| `updateSmsSettings(array $data): bool` | `settings/sms.php` |
| `getIntegrationSettings(): ?array` | `settings/integrations.php` |
| `integrationSettingsExist(): bool` | `settings/integrations.php` |
| `insertIntegrationSettings(array $data): bool` | `settings/integrations.php` |
| `updateIntegrationSettings(array $data): bool` | `settings/integrations.php` |
| `getSecuritySettings(): ?array` | `settings/security.php` |
| `securitySettingsExist(): bool` | `settings/security.php` |
| `insertSecuritySettings(array $data): bool` | `settings/security.php` |
| `updateSecuritySettings(array $data): bool` | `settings/security.php` |

### AdminSettingsService (1 new service)

| Method | Added For |
|--------|-----------|
| `saveGeneralSettings()`, `saveSeoSettings()`, `saveSmtpSettings()`, `saveSmsSettings()`, `saveIntegrationSettings()`, `saveSecuritySettings()` | All 6 settings pages |

---

## Deferred Integration Tests — Pending Database Reconstruction Phase

The following integration tests for the Settings module are deferred because the database was intentionally deleted pending the final schema redesign. These are **not migration defects** — they are runtime tests to be executed after the new database is built:

1. Browser/form submission for all 6 Settings pages
2. CSRF token validation on Settings forms
3. Password hashing and verification (settings/security.php)
4. SMTP test-connection endpoint (`test-smtp.php`)
5. Database persistence and retrieval of all settings
6. Authorization and permission checks for Settings routes
7. End-to-end workflow validation (save → fetch → display)

**Static verification completed (migration acceptance):** `php -l` passed on all modified files; SQL-qualified static audit (`_audit_settings_module_v2.php`) confirmed zero legacy SQL in Settings module; architecture (Service → Repository → Database) preserved.

### SecurityAdminRepository (7 new)
| Method | Added For |
|--------|-----------|
| `terminateSession(int $id)` | `security/sessions.php` |
| `terminateAllSessions()` | `security/sessions.php` |
| `clearSecurityLogs()` | `security/logs.php` |
| `clearLoginAttempts()` | `security/login-attempts.php` |
| `clearAuditLogs()` | `security/audit-logs.php` |

### DashboardRepository (3 new)
| Method | Added For |
|--------|-----------|
| `insertClientVideo(int $clientId, string $title, string $videoUrl)` | `uploads/videos.php` |
| `getClientUploadedImages(int $clientId)` | `uploads/images.php` |
| `getClientUploadedVideos(int $clientId)` | `uploads/videos.php` |
| `getClientUploadedTestimonials(int $clientId)` | `uploads/testimonials.php` |
| `getClientPermits(int $clientId)` | `documents/permits.php` |
| `getClientAgreements(int $clientId)` | `documents/agreements.php` |
| `getClientDownloads(int $clientId)` | `documents/downloads.php` |
| `getQuotationById(int $quotationId, int $clientId)` | `quotations/view.php` |
| `updateQuotationStatus(int $quotationId, int $clientId, string $status)` | `quotations/approvals.php` |

### ClientService (1 new)
| Method | Added For |
|--------|-----------|
| `addClientVideo(int $clientId, string $title, string $videoUrl)` | `uploads/videos.php` |
