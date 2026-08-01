# KVN Construction Platform - Module Map

## Proposed Module Architecture

```
app/
├── Modules/
│   ├── Authentication/
│   │   ├── Controllers/
│   │   ├── Services/
│   │   ├── Repositories/
│   │   ├── Middleware/
│   │   ├── Views/
│   │   └── Routes/
│   ├── CMS/
│   │   ├── Controllers/
│   │   ├── Services/
│   │   ├── Repositories/
│   │   ├── Views/
│   │   └── Routes/
│   ├── CRM/
│   │   ├── Controllers/
│   │   ├── Services/ (LeadService)
│   │   ├── Repositories/ (LeadRepository)
│   │   ├── Views/
│   │   └── Routes/
│   ├── Estimator/
│   │   ├── Controllers/
│   │   ├── Services/ (EstimationService)
│   │   ├── Repositories/ (EstimatorRepository)
│   │   ├── Views/
│   │   └── Routes/
│   ├── Projects/
│   │   ├── Controllers/
│   │   ├── Services/ (ProjectService)
│   │   ├── Repositories/ (ProjectRepository)
│   │   ├── Views/
│   │   └── Routes/
│   ├── Finance/
│   │   ├── Controllers/
│   │   ├── Services/ (QuotationService, PaymentService)
│   │   ├── Repositories/
│   │   ├── Views/
│   │   └── Routes/
│   ├── ClientPortal/
│   │   ├── Controllers/
│   │   ├── Services/
│   │   ├── Repositories/
│   │   ├── Views/
│   │   └── Routes/
│   ├── Administration/
│   │   ├── Controllers/
│   │   ├── Services/ (UserService, RoleService)
│   │   ├── Repositories/
│   │   ├── Views/
│   │   └── Routes/
│   ├── Media/
│   │   ├── Controllers/
│   │   ├── Services/ (MediaService)
│   │   ├── Repositories/ (MediaRepository)
│   │   └── Routes/
│   ├── Analytics/
│   │   ├── Controllers/
│   │   ├── Services/ (AnalyticsService)
│   │   ├── Repositories/
│   │   └── Routes/
│   ├── SEO/
│   │   ├── Controllers/
│   │   ├── Services/ (SeoService)
│   │   ├── Repositories/
│   │   └── Routes/
│   └── Notifications/
│       ├── Services/ (NotificationService)
│       └── Channels/ (SMS, Email, Push)
├── Core/
│   ├── Controller.php
│   ├── Model.php
│   ├── Router.php
│   ├── View.php
│   ├── Service.php (Base Service)
│   └── Repository.php (Base Repository)
├── Config/
├── Helpers/ (Consolidated)
├── Middleware/
├── Resources/
│   ├── Views/
│   └── Lang/
├── Storage/
├── Tests/
└── Vendor/
```

## Module Responsibilities

### Authentication Module
- User registration, login, logout
- OTP generation and verification
- Password management
- Session management
- Remember me tokens
- Email verification
- Device tracking

### CMS Module
- Blog management (posts, categories, tags)
- About page management
- Contact page management
- FAQ management
- Services management
- Homepage content management
- Testimonials management
- Packages management

### CRM Module
- Lead management
- Lead status tracking
- Lead source tracking
- Lead assignment
- Lead activities and followups

### Estimator Module
- Cost estimation calculations
- Package management
- Pricing management
- Material pricing
- Labor pricing
- Location zone management

### Projects Module
- Project CRUD operations
- Project milestones
- Project updates
- Project gallery/media
- Project tasks
- Project statuses

### Finance Module
- Quotation management
- Payment tracking
- Invoice management
- Revenue reporting
- GST calculations

### Client Portal Module
- Client dashboard
- Project tracking
- Document management
- Support tickets
- Notifications
- Payment history

### Administration Module
- User management
- Role-based access control
- Security settings
- System settings
- Audit log viewing

### Media Module
- File upload (images, documents, videos)
- Media library management
- Image optimization/derivatives
- File storage abstraction

### Analytics Module
- Event tracking
- User behavior analytics
- Conversion tracking
- Report generation

### SEO Module
- Meta tag management
- Sitemap generation
- Schema.org markup
- Open Graph tags
- Robots management

### Notifications Module
- Email notifications
- SMS notifications
- In-app notifications
- Notification preferences
- Template management

## Current File Mapping to New Modules

| Current File | Target Module |
|---|---|
| `app/controllers/admin/AdminController.php` | Administration |
| `app/controllers/admin/LeadController.php` | CRM |
| `app/controllers/admin/MediaController.php` | Media |
| `app/controllers/admin/ProjectController.php` | Projects |
| `app/controllers/auth/AuthController.php` | Authentication |
| `app/controllers/auth/AdminAuthController.php` | Authentication |
| `app/models/Lead.php` | CRM |
| `app/models/User.php` | Authentication |
| `middleware/auth.php` | Authentication |
| `middleware/admin.php` | Authentication/Admin |
| `middleware/admin-auth.php` | Authentication |
| `middleware/admin-guest.php` | Authentication |
| `middleware/client.php` | ClientPortal |
| `middleware/clients.php` | ClientPortal |
| `middleware/guest.php` | Authentication |
| `helpers/auth.php` | Authentication |
| `helpers/session.php` | Authentication |
| `helpers/otp.php` | Authentication |
| `helpers/security.php` | Core/Security |
| `helpers/csrf.php` | Core/Security |
| `helpers/upload.php` | Media |
| `helpers/mail.php` | Notifications |
| `helpers/sms.php` | Notifications |
| `helpers/rateLimiter.php` | Core/Security |
| `helpers/seo.php` | SEO |
| `helpers/formatter.php` | Core |
| `helpers/functions.php` | Core |
| `helpers/functions_security.php` | Core/Security |
| `helpers/security_audit.php` | Administration |
| `routes/api_estimator.php` | Estimator |
| `public/estimator.php` | Estimator |
| `public/projects.php` | Projects |
| `public/blogs.php` | CMS |
| `public/blog-details.php` | CMS |
| `public/portfolio/` files | CMS |
| `public/admin/` files | Administration |
| `public/client/` files | ClientPortal |
| `public/auth/` files | Authentication |