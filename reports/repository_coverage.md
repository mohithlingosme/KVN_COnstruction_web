# Repository Coverage Report

**Generated:** 2026-07-28  
**Scope:** Track which files have been migrated to the Repository+Service architecture.

---

## Coverage Summary

| Layer | Total Files | Migrated | Remaining | Percentage |
|-------|------------|----------|-----------|------------|
| `app/controllers/` | 2+ | 2 | 0 | **100%** |
| `app/services/` | 5 | 5 | 0 | **100%** |
| `helpers/` | 13 | 0 (SQL extracted) | 13 (wrappers remain) | **0%** (SQL 100%) |
| `middleware/` | 8 | 8 | 0 | **100%** |
| `public/` (website) | 20+ | 4 (contact, about, project-details, blog-details) | 16+ | **20%** |
| `public/admin/cms/` | 5 | 5 | 0 | **100%** |
| `public/client/` | 31 | 31 | 0 | **100%** |
| `public/admin/` (remaining) | ~55 | 0 | ~55 | **0%** |

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

### Dashboard (1/1)
| File | Repository Used | Service Used | Status |
|------|----------------|-------------|--------|
| `dashboard.php` | DashboardRepository | ClientService | ✅ |

---

## New/Expanded Repository Methods

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
