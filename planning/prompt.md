Full Platform Completion Request
Before making any database changes, first fully analyze the current codebase architecture, authentication flow, database structure, middleware, admin modules, frontend rendering system, and deployment configuration.

Then complete the following implementation tasks in a production-safe manner.

1️⃣ SECURITY HARDENING
Core Security
Verify CSP compatibility with all frontend libraries
Add HTTPS secure cookie enforcement
Add upload folder execution blocking (.htaccess)
Add session invalidation after password reset
Add suspicious login/device detection
Add admin audit logs UI
Add centralized production error logging
Add honeypot spam protection to contact forms
Requirements
No breaking changes to existing authentication
All security middleware must be centralized
Production-safe defaults only
Add database migrations where required
Add rollback-safe migration strategy
2️⃣ AUTHENTICATION IMPROVEMENTS
Implement:

Remember Me functionality
Email verification system
Concurrent session control
Device-based session tracking
Session persistence synchronization
Multi-device logout support
Requirements:

Maintain backward compatibility
Ensure secure token storage
Add DB tables/indexes only after schema validation
Add proper cleanup cron handling for expired sessions/tokens
3️⃣ PUBLIC WEBSITE CMS
Homepage CMS
Implement fully dynamic:

Hero slider
Homepage sections
Project carousel
Testimonial carousel
Blog carousel
FAQ section
CTA management
Website Features
Implement:

WhatsApp floating integration
Sticky CTA buttons
Contact auto-response email
Dynamic SEO metadata system
Sitemap auto-generation
Robots.txt generator
Requirements:

CMS-driven rendering
Reusable components
SEO optimized
Cache-aware implementation
4️⃣ ADMIN PANEL
Dashboard
Implement:

Dashboard UI improvements
Revenue analytics
Lead analytics
Estimator analytics
Security monitoring dashboard
Activity logs dashboard
Management Modules
Implement:

Leads management
Follow-up management
CRM pipeline
Project management
Estimator management
Quotation management
Media management
CMS management
Requirements:

RBAC-ready architecture
Pagination/search/filter support
Audit logging
Modular controllers/services
5️⃣ DYNAMIC ESTIMATOR ENGINE
Implement:

Dynamic package pricing
Material pricing engine
Location-based pricing
Smart home pricing
Interior upgrade pricing
GST calculation engine
Timeline estimation engine
Dynamic quotation generation
Requirements:

Formula-driven pricing system
Admin configurable pricing rules
Database normalization
Extensible architecture
6️⃣ PACKAGE SPECIFICATION SYSTEM
Silver Package
Structural specifications
Flooring specifications
Plumbing specifications
Electrical specifications
Material specification system
Gold Package
Premium specification engine
Smart home addons
Modular kitchen options
Platinum Package
Luxury specification system
Imported material engine
Automation integration
Requirements:

Fully dynamic package configuration
CMS-manageable specifications
Reusable package engine
7️⃣ CLIENT PORTAL
Implement:

Client dashboard
Project tracking
Payment tracking
Invoice downloads
File downloads
Client communication system
Feedback system
Requirements:

Secure client isolation
Role-based access
Download permission checks
Audit tracking
8️⃣ CMS SYSTEM
Implement management systems for:

Blogs
FAQs
Services
Testimonials
Portfolio
Homepage CMS
About page CMS
Contact page CMS
Requirements:

SEO-ready
Slug system
Draft/publish workflow
Media integration
9️⃣ MEDIA MANAGEMENT
Implement:

Central media library
Image compression
WebP conversion
Video upload support
Gallery management
YouTube integration
Requirements:

Secure upload validation
MIME validation
Size restrictions
Queue/background optimization if needed
🔟 SEO SYSTEM
Implement:

Dynamic meta titles
Dynamic descriptions
Open Graph tags
Canonical URLs
Schema markup
XML sitemap
SEO-friendly slugs
Structured data system
Requirements:

CMS integrated
Auto-generated fallback metadata
Route-aware rendering
1️⃣1️⃣ PERFORMANCE OPTIMIZATION
Implement:

Query optimization
Database indexing optimization
Asset minification
Lazy loading
CDN integration readiness
Image optimization
Requirements:

Avoid premature optimization
Profile bottlenecks first
Maintain compatibility with existing stack
1️⃣2️⃣ FINAL PRODUCTION TASKS
Implement and verify:

Disable debug mode
Production environment configuration
Penetration testing fixes
Security audit fixes
Performance audit fixes
Backup automation
Deployment pipeline readiness
IMPORTANT IMPLEMENTATION RULES
FIRST analyze the current codebase completely before modifying anything.
FIRST understand the existing database schema before adding migrations.
Avoid duplicate indexes, duplicate constraints, or conflicting migrations.
Preserve backward compatibility.
Use production-safe patterns only.
Refactor where necessary instead of patch stacking.
Add proper comments/documentation.
Group work into logical commits/modules.
Ensure admin panel and public website remain functional during refactors.
Add validation, authorization, logging, and error handling consistently.
Please complete the implementation progressively and safely across the codebase.

