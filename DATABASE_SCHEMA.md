# KVN Construction - Database Schema Reference

## Overview
- **Database**: `kvnc_platform`
- **Engine**: InnoDB
- **Charset**: utf8mb4_unicode_ci
- **PHP Version**: 8.2+

## Core Tables

### Users & Authentication
| Table | Purpose | Key Columns |
|-------|---------|-------------|
| `users` | All user accounts (admin, client, employee) | id, email, phone, password, role, status |
| `user_roles` | User-role assignments | user_id, role_id |
| `roles` | Role definitions | id, name, guard_name |
| `permissions` | Permission definitions | id, name, guard_name |
| `role_permissions` | Role-permission mapping | role_id, permission_id |
| `user_sessions` | Active user sessions | id, user_id, session_token, fingerprint_hash, expires_at |
| `user_devices` | Trusted user devices | id, user_id, device_hash, user_agent |
| `password_history` | Password change tracking | id, user_id, password_hash, created_at |
| `remember_tokens` | "Remember me" tokens | id, user_id, token, expires_at |

### Security & Logging
| Table | Purpose | Key Columns |
|-------|---------|-------------|
| `security_logs` | Security event log | id, user_id, event_type, severity, ip_address, created_at |
| `audit_logs` | Audit trail | id, user_id, action_type, description, created_at |
| `admin_action_logs` | Admin action tracking | id, admin_id, action, details, created_at |
| `login_attempts` | Login attempt tracking | id, email, phone, ip_address, success, created_at |
| `otps` | OTP storage | id, user_id, phone, otp_hash, purpose, expires_at, is_used |
| `otp_attempts` | OTP attempt tracking | id, otp_id, ip_address, created_at |
| `rate_limits` | Rate limiting | id, identifier, action_type, attempts, blocked_until |
| `blocked_users` | Blocked users/IPs | id, user_id, ip_address, reason, blocked_at |
| `suspicious_activity` | Suspicious activity log | id, user_id, activity_type, severity, details, created_at |

### Leads & Estimator
| Table | Purpose | Key Columns |
|-------|---------|-------------|
| `leads` | Lead management | id, full_name, email, phone, project_type, budget, status_id |
| `lead_statuses` | Lead status definitions | id, name, color, sort_order |
| `lead_activities` | Lead activity log | id, lead_id, activity_type, description, created_at |
| `lead_followups` | Lead follow-up tracking | id, lead_id, followup_date, note, created_by |
| `estimator_requests` | **Canonical** estimator submissions | id, full_name, phone, email, plot_area, floors, package_id, estimated_cost |
| `estimator_packages` | Construction packages | id, package_name, base_price, features, status |
| `estimator_pricing` | Package pricing data | id, package_name, price_per_sqft, description, status |
| `estimator_materials` | Material pricing | id, material_name, unit, rate, category |
| `estimator_calculation_log` | Calculation audit | id, request_id, input_data, result, created_at |

### Projects & Portfolio
| Table | Purpose | Key Columns |
|-------|---------|-------------|
| `projects` | **Canonical** project management | id, client_id, project_name, budget, status, start_date |
| `project_statuses` | Project status definitions | id, name, color, sort_order |
| `project_milestones` | Project milestones | id, project_id, title, expected_date, status |
| `project_updates` | Progress updates | id, project_id, description, created_by, created_at |
| `project_files` | File attachments | id, project_id, file_name, file_path, uploaded_by |
| `project_gallery` | Project images | id, project_id, image_path, caption, sort_order |
| `project_tasks` | Task management | id, project_id, task_name, assigned_to, due_date, status |
| `project_payments` | Project payments | id, project_id, amount, payment_date, payment_status |
| `portfolio` | **Canonical** portfolio projects | id, title, slug, description, project_type, location, featured_image |

### Client Portal
| Table | Purpose | Key Columns |
|-------|---------|-------------|
| `clients` | Client information | id, user_id, company_name, gst_number, address |
| `client_agreements` | Client agreements | id, client_id, agreement_type, file_path, signed_at |
| `client_documents` | Client documents | id, client_id, document_type, file_path, uploaded_at |
| `client_invoices` | Client invoices | id, client_id, invoice_number, amount, due_date, payment_status |
| `client_messages` | Client messages | id, client_id, subject, message, created_at |
| `client_notifications` | Client notifications | id, client_id, title, message, is_read, created_at |
| `client_permits` | Client permits | id, client_id, permit_type, permit_number, expiry_date |
| `client_quotations` | Client quotations | id, client_id, quotation_number, amount, status |

### Content Management
| Table | Purpose | Key Columns |
|-------|---------|-------------|
| `blogs` | **Canonical** blog posts | id, title, slug, excerpt, content, featured_image, status, published_at |
| `blog_categories` | Blog categories | id, category_name, slug, description |
| `blog_tags` | Blog tags | id, tag_name, slug |
| `blog_comments` | Blog comments | id, blog_id, name, email, comment, is_approved, created_at |
| `services` | Service offerings | id, service_name, title, description, icon, status |
| `testimonials` | Client testimonials | id, client_name, client_image, review, rating, status |
| `testimonial_videos` | Video testimonials | id, client_name, youtube_url, description, status |
| `videos` | Video library | id, title, youtube_url, description, category_id, status |
| `video_categories` | Video categories | id, name, slug, description |
| `faqs` | FAQ entries | id, question, answer, category, sort_order, status |
| `construction_packages` | Construction packages | id, package_name, base_price, description, features, status |
| `package_features` | Package features | id, package_id, feature_name, description |
| `package_specifications` | Package specifications | id, package_id, spec_name, spec_value |

### CMS Pages
| Table | Purpose | Key Columns |
|-------|---------|-------------|
| `about_page` | About page content | id, hero_title, hero_description, mission, vision |
| `about_advantages` | About page advantages | id, title, description, icon, sort_order |
| `about_process_steps` | Process steps | id, step_number, title, description, icon |
| `about_specifications` | Specifications | id, title, value, icon |
| `contact_page` | Contact page content | id, phone, email, address, google_map_link |
| `contact_page_features` | Contact features | id, title, description, icon |
| `homepage_content` | Homepage content | id, hero_title, hero_subtitle, cta_text |
| `homepage_sections` | Homepage sections | id, section_name, title, content, sort_order |
| `homepage_slides` | Hero slides | id, title, subtitle, image_path, cta_link, sort_order |
| `cta_blocks` | Call-to-action blocks | id, title, description, button_text, button_link |

### Settings & Integrations
| Table | Purpose | Key Columns |
|-------|---------|-------------|
| `general_settings` | General site settings | id, setting_key, setting_value |
| `site_settings` | Key-value settings store | id, key, value, group |
| `seo_settings` | SEO configuration | id, page, meta_title, meta_description, meta_keywords |
| `route_seo_meta` | Per-route SEO metadata | id, route, meta_title, meta_description, og_image |
| `integration_settings` | Third-party integrations | id, service, api_key, api_secret, status |
| `smtp_settings` | SMTP email config | id, host, port, username, password, encryption |
| `sms_settings` | SMS gateway config | id, provider, api_key, api_secret, from_number |
| `media` | Media library | id, file_name, file_path, mime_type, size, uploaded_by |
| `media_derivatives` | Media thumbnails | id, media_id, derivative_type, file_path, width, height |

### Financial
| Table | Purpose | Key Columns |
|-------|---------|-------------|
| `quotations` | Quotation management | id, client_id, quotation_number, amount, status |
| `quotation_items` | Quotation line items | id, quotation_id, description, quantity, rate, amount |
| `quotation_versions` | Quotation version history | id, quotation_id, version_number, data, created_at |
| `payment_receipts` | Payment receipts | id, payment_id, receipt_number, file_path, created_at |
| `payment_transactions` | Payment transactions | id, payment_id, transaction_id, gateway, amount, status |
| `revenue_reports` | Revenue reporting | id, period_start, period_end, total_revenue, expenses, profit |

### Logs
| Table | Purpose | Key Columns |
|-------|---------|-------------|
| `mail_logs` | Email delivery logs | id, recipient, subject, status, sent_at |
| `sms_logs` | SMS delivery logs | id, phone, message, status, sent_at |
| `email_verification_tokens` | Email verification | id, user_id, token, expires_at, verified_at |

### Support
| Table | Purpose | Key Columns |
|-------|---------|-------------|
| `support_tickets` | Support tickets | id, client_id, subject, priority, status, created_at |
| `support_messages` | Ticket messages | id, ticket_id, sender_id, message, created_at |

### Analytics
| Table | Purpose | Key Columns |
|-------|---------|-------------|
| `analytics_events` | Analytics event tracking | id, event_name, source, data, ip_address, created_at |

### Migration Tracking
| Table | Purpose |
|-------|---------|
| `schema_migrations` | Tracks applied database migrations |

## Duplicate Tables (Consolidated via Views)
These tables have identical schemas to their canonical counterparts and are consolidated via VIEWs:

| Duplicate Table | Canonical Table | Migration Status |
|----------------|-----------------|------------------|
| `blog_posts` | `blogs` | ✅ VIEW created |
| `portfolio_projects` | `portfolio` | ✅ VIEW created |
| `estimators` | `estimator_requests` | ✅ VIEW created |
| `estimator_leads` | `estimator_requests` | ✅ VIEW created |
| `client_projects` | `projects` | ✅ VIEW created |
| `client_payments` | `payments` (unified) | Pending |
| `client_feedback` | `testimonials` | Pending |
| `client_testimonials` | `testimonials` | Pending |
| `client_downloads` | `quotation_downloads` | Pending |
| `quotation_downloads` | Unified `downloads` | Pending |
| `client_files` | `media` | Pending |
| `media_library` | `media` | Pending |
| `project_schedules` | `project_timelines` | Pending |

## Views
| View | Purpose |
|------|---------|
| `active_sessions_view` | Active user sessions for dashboard |
| `failed_login_attempts_view` | Failed login monitoring |
| `security_overview` | Security dashboard metrics |
| `suspicious_activity_view` | Unresolved suspicious activity |
| `blogs_view` | Unified blogs + blog_posts |
| `portfolio_view` | Unified portfolio + portfolio_projects |
| `estimators_view` | Unified estimator_requests + estimators + estimator_leads |
| `projects_view` | Unified projects + client_projects |

## Key Indexes
| Table | Index | Columns |
|-------|-------|---------|
| `users` | idx_users_email_status | email, status |
| `users` | idx_users_phone_status | phone, status |
| `leads` | idx_leads_status_date | status_id, created_at |
| `leads` | idx_leads_assigned_date | assigned_to, created_at |
| `estimator_requests` | idx_estimator_requests_status | status, created_at |
| `otps` | idx_otps_user_type_used | user_id, otp_type, is_used |
| `security_logs` | idx_security_logs_severity_created | severity, created_at |
| `login_attempts` | idx_login_attempts_phone | phone |
| `rate_limits` | idx_rate_limits_identifier_action_blocked | identifier, action_type, blocked_until |