# Slide Form Demo

A Laravel 12 REST API demonstrating multi-step form workflows with role-based access control and token authentication.

## Overview

Laravel backend providing a RESTful API for client applications. Implements domain-driven architecture with user management, project workflows, and team collaboration.

## Features

- RESTful API with Laravel Sanctum token authentication
- Frontend agnostic - supports any client framework
- Multi-step form workflow system
- Role-based access control (5 roles: Guest, Client, Consultant, Admin, Super Admin)
- Domain-driven architecture
- Incremental form saving
- Team ownership and ownership transfer
- 416 backend tests with 100% API endpoint coverage

## Tech Stack

- Laravel 12 (PHP 8.4+)
- Laravel Sanctum v4
- Spatie Laravel Permission
- Pest v3
- Scramble (OpenAPI 3.1.0)
- MySQL/PostgreSQL/SQLite

## Installation

```bash
# Install dependencies
composer install

# Configure environment
cp .env.example .env
php artisan key:generate

# Configure database
# Edit .env: DB_CONNECTION=sqlite (or mysql, pgsql)

# Run migrations
php artisan migrate --seed

# Start server
php artisan serve
```

API available at `http://localhost:8000`
API documentation at `http://localhost:8000/docs/api`

## Project Structure

```
app/
├── Enums/                  # Domain-specific enums
├── Http/
│   ├── Controllers/API/    # REST API controllers (domain-organized)
│   ├── Middleware/         # SecurityHeaders, CORS
│   ├── Requests/           # Form validation classes
│   └── Resources/          # API resource transformers
├── Models/                 # Domain-organized Eloquent models
└── Services/               # Business logic layer

routes/
└── api.php                 # REST API routes

tests/
├── Feature/API/            # API integration tests
└── Unit/                   # Unit tests
```

## Domain Organization

Code organized by business domain:

- **Account** - User profiles, teams, subscriptions, terms
- **Story** - Project workflow, forms, responses, tokens
- **Admin** - Administrative functions, user management
- **Auth** - Authentication and user profile management

Core entities (User, Role, Permission) at root level.

## API Documentation

Interactive documentation powered by Scramble:

```
http://localhost:8000/docs/api
```

OpenAPI 3.1.0 specification:
- JSON: `docs/api/openapi.json`
- Endpoint: `http://localhost:8000/docs/api.json`

## Testing

```bash
php artisan test
```

416 tests, 1277 assertions:
- API Endpoints: 100%
- Authentication & Authorization: 100%
- User Management: 59 tests
- Projects/Stories: 69 tests
- Teams: 119 tests (members, invitations, ownership transfer)
- Rate Limiting: 4 tests
- Security Headers: 5 tests

## Security

- Token-based authentication (Sanctum)
- Security headers (CSP, X-Frame-Options, etc.)
- Role-based access control
- Permission-based authorization
- Password hashing (bcrypt)
- Mass assignment protection
- Rate limiting (5 req/min auth, 60 req/min API)
- Form Request validation
- SQL injection prevention (Eloquent ORM)

## Development

```bash
# Testing
php artisan test
php artisan test --filter=testName
php artisan test --coverage

# Code formatting
vendor/bin/pint
vendor/bin/pint --dirty

# Database
php artisan migrate
php artisan migrate:fresh --seed
php artisan db:seed

# API documentation
php artisan scramble:export
```

## Demo Accounts

| Role        | Email                   | Password    |
| ----------- | ----------------------- | ----------- |
| Guest       | guest@example.com       | guest       |
| Client      | client@example.com      | client      |
| Consultant  | consultant@example.com  | consultant  |
| Admin       | admin@example.com       | admin       |
| Super Admin | super-admin@example.com | super-admin |

## Environment Configuration

```bash
APP_ENV=production
APP_DEBUG=false
APP_URL=https://api.your-domain.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_DATABASE=your_database
DB_USERNAME=your_username
DB_PASSWORD=your_password

FRONTEND_URL=https://app.your-domain.com
SANCTUM_EXPIRATION=1440
```

## Deployment

Supported platforms:
- Shared hosting (cPanel, PHP 8.4+)
- VPS (DigitalOcean, Linode, AWS EC2)
- Platform as a Service (Laravel Forge, Ploi, Envoyer)
- Containerized (Docker, Kubernetes)

## Contributing

1. Follow domain-driven architecture patterns
2. Maintain 100% API endpoint test coverage
3. Use Form Request classes for validation
4. Use Resource classes for API responses
5. Keep controllers thin, use services for business logic
6. Run `vendor/bin/pint --dirty` before committing
7. Run `php artisan test` before submitting

## License

Demonstration project for educational purposes.
