# Entity-Relationship Summary (Best-Effort)

This audit parses CREATE TABLE statements from .sql files to extract schema information.

**Tables:** 116

## Table List

```
about_advantages
about_page
about_process_steps
about_specifications
active_sessions_view
admin_action_logs
analytics_events
audit_logs
blocked_users
blog_categories
blog_comments
blog_posts
blog_tags
blogs
client_agreements
client_documents
client_downloads
client_feedback
client_files
client_invoices
client_messages
client_notifications
client_payments
client_permits
client_projects
client_quotations
client_testimonials
client_uploaded_images
client_uploaded_videos
clients
construction_packages
contact_page
contact_page_features
cta_blocks
email_verification_tokens
estimator_calculation_log
estimator_leads
estimator_materials
estimator_packages
estimator_pricing
estimator_requests
estimators
failed_login_attempts_view
faqs
general_settings
homepage_content
homepage_sections
homepage_slides
integration_settings
labor_pricing
lead_activities
lead_followups
lead_statuses
leads
location_zones
login_attempts
mail_logs
material_pricing
media
media_derivatives
media_library
otp_attempts
otps
package_features
package_specifications
password_history
payment_receipts
payment_transactions
permissions
portfolio
portfolio_projects
project_files
project_gallery
project_media
project_milestones
project_payments
project_reports
project_schedules
project_statuses
project_tasks
project_timelines
project_updates
projects
quotation_downloads
quotation_items
quotation_versions
quotations
rate_limits
remember_tokens
revenue_reports
role_permissions
roles
route_seo_meta
schema_migrations
security_logs
security_overview
security_settings
seo_settings
services
session_history
site_settings
sms_logs
sms_settings
smtp_settings
support_messages
support_tickets
suspicious_activity
suspicious_activity_view
testimonial_videos
testimonials
user_devices
user_roles
user_sessions
users
video_categories
videos
```

## Relationships (Foreign Keys)

| Referenced Table | Source File |
|---|---|
| client_projects | database\migration\Kvnc_platform.sql |
| leads | database\migration\Kvnc_platform.sql |
| quotations | database\migration\Kvnc_platform.sql |
| blog_posts | database\migration\Kvnc_platform.sql |
| construction_packages | database\migration\Kvnc_platform.sql |
| permissions | database\migration\Kvnc_platform.sql |
| projects | database\migration\Kvnc_platform.sql |
| roles | database\migration\Kvnc_platform.sql |
| blog_categories | database\migration\Kvnc_platform.sql |
| media_library | database\migration\Kvnc_platform.sql |
| estimator_requests | database\migration\Kvnc_platform.sql |
| location_zones | database\migration\Kvnc_platform.sql |
| users | database\migration\Kvnc_platform.sql |
| lead_statuses | database\migration\Kvnc_platform.sql |
| project_statuses | database\migration\Kvnc_platform.sql |
