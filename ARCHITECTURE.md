# KVN Construction Platform - Architecture Documentation

## Architecture Overview

```
Client Request (HTTP)
    ↓
public/index.php (Entry Point)
    ↓
Router (core/Router.php)
    ↓
Middleware (Authentication, CSRF, Security Headers)
    ↓
Controller (Thin - request parsing, response formatting)
    ↓
Service (Business Logic - validation, calculations, workflows)
    ↓
Repository (SQL Queries Only)
    ↓
Database (MySQL/MariaDB)
```

## Directory Structure

```
KVN_Construction/
├── app/
│   ├── controllers/          # Thin controllers (request/response only)
│   │   ├── admin/            # Admin controllers
│   │   └── auth/             # Authentication controllers
│   ├── models/               # Data entities (legacy - being phased out)
│   ├── repositories/         # Data access layer (SQL only)
│   ├── services/             # Business logic layer
│   └── views/                # View templates
├── bootstrap/
│   └── providers/            # Service Provider (DI container)
├── config/                   # Application configuration
├── core/                     # Framework base classes
│   ├── Controller.php
│   ├── Model.php
│   ├── Router.php
│   ├── View.php
│   ├── Repository.php        # Base repository
│   ├── Service.php           # Base service
│   └── Event.php             # Event system
├── database/
│   ├── migration/            # Database migrations
│   └── seeders/              # Seed data
├── helpers/                  # Legacy helpers (being consolidated)
├── middleware/               # Request middleware
├── public/                   # Web root (entry point)
├── routes/                   # Route definitions
├── storage/                  # Cache, logs, private files
└── tests/                    # Test suite
```

## Layers

### Controllers (Thin)
- Parse HTTP requests (GET/POST parameters, headers)
- Validate basic input format
- Call appropriate Service methods
- Format and return responses (JSON, redirect, view)
- **No SQL, no business logic allowed**

### Services (Business Logic)
- All validation rules
- All calculations and workflows
- Authorization checks
- Event dispatching
- Orchestrate multiple repositories
- Return standardized response array: `{status, message, data, errors, meta}`

### Repositories (Data Access)
- All SQL queries (prepared statements only)
- CRUD operations
- Complex joins and aggregations
- **No business logic allowed**

## Dependency Injection

Services and repositories are created by `ServiceProvider`:

```php
$leadService = ServiceProvider::get('LeadService');
$leadRepo = ServiceProvider::get('LeadRepository');
```

## Standard API Response Format

```json
{
    "status": true,
    "message": "Operation successful",
    "data": { ... },
    "errors": null,
    "meta": {
        "total": 100,
        "page": 1,
        "per_page": 15,
        "total_pages": 7
    }
}
```

## Authentication Flow

1. **Password Login**: User submits email/phone + password → AuthService → UserRepository → Session creation
2. **OTP Login**: User submits phone → OtpService.generate() → OTP stored in user_otps → User verifies → OtpService.verify() → Session creation
3. **Admin Login**: Admin submits email + password → AuthService.adminLogin() → Role verification → Admin session
4. **Session Validation**: middleware/auth.php validates session fingerprint, device hash, timeout, database session record

## Event System

Events allow loose coupling between components:

```php
// Dispatch
Event::dispatch('UserRegistered', ['user_id' => 123, 'email' => '...']);

// Listen (registered in core/Event.php)
Event::listen('UserRegistered', function($payload) { ... });
```

## Security Measures

- **SQL Injection**: All queries use PDO prepared statements with named parameters
- **XSS**: Output escaped via `escapeHtml()` / `htmlspecialchars()` with ENT_QUOTES
- **CSRF**: Token-based protection on all POST requests
- **Session**: Fingerprint validation, device hash, timeout, database tracking
- **Headers**: CSP, HSTS, X-Frame-Options, X-Content-Type-Options, Referrer-Policy
- **Rate Limiting**: Session-based rate limiting per action
- **Password**: bcrypt hashing (PASSWORD_DEFAULT), strength validation
- **OTP**: Hashed storage, single-use, expiry, attempt limits, cooldown