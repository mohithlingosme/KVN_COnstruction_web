# KVN Construction - Full Route Discovery

## Public Pages (PHP Files in /public/)

| Route | Method | File | Controller | Dependencies | Status |
|-------|--------|------|------------|--------------|--------|
| /index.php | GET | public/index.php | None | config/app.php, helpers/functions.php, header.php, footer.php | ✅ |
| /about-us.php | GET | public/about-us.php | None | config/app.php, header.php, footer.php | ✅ |
| /services.php | GET | public/services.php | None | config/app.php, header.php, footer.php | ✅ |
| /projects.php | GET | public/projects.php | None | config/app.php, header.php, footer.php | ✅ |
| /project-details.php | GET | public/project-details.php | None | config/app.php, header.php, footer.php | ✅ |
| /blogs.php | GET | public/blogs.php | None | config/app.php, header.php, footer.php | ✅ |
| /blog-details.php | GET | public/blog-details.php | None | config/app.php, header.php, footer.php | ✅ |
| /contact.php | GET/POST | public/contact.php | None | config/app.php, csrf.php, security.php, rateLimiter.php, upload.php, header.php, footer.php | ✅ |
| /estimator.php | GET/POST | public/estimator.php | None | config/app.php, security.php, csrf.php, rateLimiter.php, database.php, header.php, footer.php | ⚠️ |
| /login.php | GET | public/login.php | None | config/app.php, views/auth/client-login.php | ✅ |
| /phone-login.php | GET | public/phone-login.php | None | config/app.php, csrf.php, otp.php, sms.php, security.php, session.php, header.php, footer.php | ⚠️ |
| /register.php | GET | public/register.php | None | config/app.php, security.php, session.php, csrf.php, guest.php | ✅ |
| /forgot-password.php | GET/POST | public/forgot-password.php | AuthController | config/app.php, security.php, session.php, csrf.php, rateLimiter.php, mail.php, User.php, AuthController.php, guest.php, header.php, footer.php | ⚠️ |
| /reset-password.php | GET/POST | public/reset-password.php | Unknown | Unknown | 🔍 |
| /verify-phone-otp.php | GET/POST | public/verify-phone-otp.php | Unknown | Unknown | 🔍 |
| /verify-reset-otp.php | GET/POST | public/verify-reset-otp.php | Unknown | Unknown | 🔍 |
| /logout.php | POST | public/logout.php | AuthController | config/app.php, security.php, session.php, AuthController.php | ⚠️ |
| /gallery.php | GET | public/gallery.php | None | config/app.php, header.php, footer.php | 🔍 |
| /packages.php | GET | public/packages.php | None | config/app.php, header.php, footer.php | 🔍 |
| /faq.php | GET | public/faq.php | None | config/app.php, header.php, footer.php | 🔍 |
| /careers.php | GET | public/careers.php | None | config/app.php, header.php, footer.php | 🔍 |
| /testimonials.php | GET | public/testimonials.php | None | config/app.php, header.php, footer.php | 🔍 |
| /privacy.php | GET | public/privacy.php | None | config/app.php, header.php, footer.php | 🔍 |
| /terms.php | GET | public/terms.php | None | config/app.php, header.php, footer.php | 🔍 |
| /videos.php | GET | public/videos.php | None | config/app.php, header.php, footer.php | 🔍 |
| /404.php | GET | public/404.php | None | None | ✅ |

## Auth Handlers

| Route | Method | File | Dependencies | Status |
|-------|--------|------|--------------|--------|
| /auth/phone-login-handler.php | POST | public/auth/phone-login-handler.php | config/app.php, security.php, session.php, AuthController.php | ⚠️ |
| /auth/register-handler.php | POST | public/auth/register-handler.php | config/app.php, security.php, session.php, csrf.php | 🔍 |

## Admin Pages

| Route | Method | File | Status |
|-------|--------|------|--------|
| /admin/login.php | GET/POST | public/admin/login.php | 🔍 |
| /admin/dashboard.php | GET | public/admin/dashboard.php | 🔍 |

## Client Pages

| Route | Method | File | Status |
|-------|--------|------|--------|
| /client/dashboard.php | GET | public/client/dashboard.php | 🔍 |

## API Routes

| Route | Method | File | Status |
|-------|--------|------|--------|
| /routes/api_estimator.php | POST | routes/api_estimator.php | 🔍 |

## Assets

| Asset | Path | Status |
|-------|------|--------|
| CSS | assets/css/style.css | 🔍 |
| JS | assets/js/app.js | 🔍 |
| Favicon | assets/images/favicon.png | 🔍 |
| OG Image | assets/images/og-image.jpg | 🔍 |
| Default User | assets/images/default-user.png | 🔍 |
| Contact Hero | assets/images/contact/contact-hero.jpg | 🔍 |

## Router Routes (URL-based Controller Resolution)

| URL Pattern | Controller | Method | Status |
|-------------|------------|--------|--------|
| /{controller}/{method}/{params} | app/controllers/{Controller}Controller.php | {method} | ⚠️ |

## Includes/Requires

| Source File | Include Path | Status |
|-------------|-------------|--------|
| header.php (line 279) | C:\xampp\htdocs\KVN_Construction\public\about-us.php | ❌ BROKEN |
| public/index.php | ../config/app.php | ✅ |
| public/index.php | ../helpers/functions.php | ✅ |
| public/index.php | ../app/views/layouts/header.php | ✅ |
| public/index.php | ../app/views/layouts/footer.php | ✅ |

## Database Tables Referenced

| Table | Usage | Status |
|-------|-------|--------|
| portfolio | index.php, projects.php | 🔍 |
| blogs | index.php, blogs.php, blog-details.php | 🔍 |
| testimonials | index.php | 🔍 |
| construction_packages | index.php | 🔍 |
| about_page | about-us.php | 🔍 |
| about_advantages | about-us.php | 🔍 |
| about_process_steps | about-us.php | 🔍 |
| about_specifications | about-us.php | 🔍 |
| contact_page | contact.php | 🔍 |
| contact_page_features | contact.php | 🔍 |
| leads | contact.php (INSERT) | 🔍 |
| estimator_packages | estimator.php | 🔍 |
| estimator_leads | estimator.php (INSERT) | 🔍 |
| blog_categories | blogs.php, blog-details.php | 🔍 |
| videos | blogs.php | 🔍 |
| users | forgot-password.php | 🔍 |
| password_resets | forgot-password.php | 🔍 |