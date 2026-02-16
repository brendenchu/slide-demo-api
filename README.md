# Slide Form Demo API

A Laravel 12 REST API demonstrating multi-step form workflows with team-based access control and token authentication.

## Overview

Laravel backend providing a RESTful API for client applications. Implements domain-driven architecture with user management, project workflows, team collaboration, invitations, and notifications.

## Features

- RESTful API with Laravel Sanctum token authentication (24-hour expiry)
- Frontend agnostic - supports any client framework
- Multi-step form workflow system
- Team-based access control (owner, admin, member roles)
- Domain-driven architecture
- Incremental form saving
- Team management with invitations and ownership transfer
- In-app notification system
- Demo mode with configurable resource limits
- 353 backend tests with full API endpoint coverage

## Tech Stack

- Laravel 12 (PHP 8.4+)
- Laravel Sanctum v4
- Spatie Laravel Permission
- Pest v3
- Scramble (OpenAPI 3.1.0)
- Rector
- SQLite (default, supports MySQL/PostgreSQL)

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
│   ├── Middleware/          # SecurityHeaders, CORS, DemoProtection
│   ├── Requests/           # Form validation classes
│   └── Resources/          # API resource transformers
├── Models/                 # Domain-organized Eloquent models
└── Services/               # Business logic layer

routes/
└── api.php                 # REST API routes (v1 prefixed)

tests/
├── Feature/API/            # API integration tests
└── Unit/                   # Unit tests
```

## Domain Organization

Code organized by business domain:

- **Auth** - Authentication, registration, user profile management
- **Story** - Project workflows, forms, responses, completion
- **Team** - Team CRUD, members, invitations, ownership transfer, user search
- **Notifications** - In-app notifications with read tracking
- **Demo** - Demo status and safe names endpoints

Core entities (User, Role, Permission) at root level.

## API Endpoints

All API routes are prefixed with `/api/v1`.

### Public

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/demo/status` | Check demo mode status |
| GET | `/names` | Get safe names list |

### Authentication (rate limit: 5/min)

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/auth/login` | Login user |
| POST | `/auth/register` | Register new user |

### Protected (rate limit: 60/min, requires Bearer token)

#### Auth User

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/auth/logout` | Logout user |
| GET | `/auth/user` | Get authenticated user |
| PUT | `/auth/user` | Update authenticated user |
| DELETE | `/auth/user` | Delete authenticated user |

#### Projects

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/projects` | List user's projects |
| POST | `/projects` | Create new project |
| GET | `/projects/{id}` | Get single project |
| PUT | `/projects/{id}` | Update project |
| DELETE | `/projects/{id}` | Delete project |
| POST | `/projects/{id}/responses` | Save form responses |
| POST | `/projects/{id}/complete` | Mark project complete |

#### Teams

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/teams` | List user's teams |
| POST | `/teams` | Create new team |
| POST | `/teams/current` | Set current team |
| GET | `/teams/{teamId}` | Get single team |
| PUT | `/teams/{teamId}` | Update team |
| DELETE | `/teams/{teamId}` | Delete team |

#### Team Members

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/teams/{teamId}/members` | List team members |
| PUT | `/teams/{teamId}/members/{userId}/role` | Update member role |
| DELETE | `/teams/{teamId}/members/{userId}` | Remove member |
| POST | `/teams/{teamId}/transfer-ownership` | Transfer team ownership |

#### Team Invitations

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/teams/{teamId}/invitations` | List team invitations |
| POST | `/teams/{teamId}/invitations` | Create invitation |
| DELETE | `/teams/{teamId}/invitations/{id}` | Delete invitation |

#### User Invitations

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/invitations` | List user's pending invitations |
| POST | `/invitations/{id}/accept` | Accept invitation |
| POST | `/invitations/{id}/decline` | Decline invitation |

#### Notifications

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/notifications` | List user's notifications |
| POST | `/notifications/read-all` | Mark all as read |
| POST | `/notifications/{id}/read` | Mark single as read |

#### Users

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/users/search` | Search users (for team invitations) |

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

353 tests, 1112 assertions:
- Authentication & Authorization
- Projects/Stories CRUD and form workflows
- Teams: CRUD, members, invitations, ownership transfer
- Notifications
- Rate Limiting
- Security Headers

## Security

- Token-based authentication (Sanctum, 24-hour expiry)
- Security headers (CSP, X-Frame-Options, etc.)
- Team-based access control
- Permission-based authorization
- Password hashing (bcrypt)
- Mass assignment protection
- Rate limiting (5 req/min auth, 60 req/min API)
- Form Request validation
- SQL injection prevention (Eloquent ORM)
- Demo account protection middleware

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

# Demo reset (full cleanup + re-seed)
php artisan demo:reset

# API documentation
php artisan scramble:export
```

## Demo Mode

When `DEMO_MODE=true`, the application runs in demo mode with a pre-seeded demo user and 10 dummy users.

### Demo Credentials

| Email | Password | Description |
|-------|----------|-------------|
| `demo@example.com` | `password` | Primary demo user |

10 additional dummy users (`@example.com`) are seeded for team collaboration and invitation demos.

### Daily Reset

When demo mode is enabled, `demo:reset` runs daily (America/Vancouver timezone) via the Laravel scheduler. The reset:

1. Deletes visitor-created users (anyone who registered during the demo)
2. Cleans all seeded content (non-personal teams, projects, notifications, tokens, terms)
3. Removes orphaned teams
4. Resets the demo user's credentials and profile to defaults
5. Re-seeds the full database via `DatabaseSeeder` (roles, demo user, dummy users, demo content)

The demo user and dummy users are preserved across resets — only visitor accounts are removed.

Run manually: `php artisan demo:reset`

### Resource Limits

Configurable via `.env`:
- Max users: 25
- Max teams per user: 3
- Max projects per team: 5
- Max invitations per team: 5

## Environment Configuration

```bash
APP_ENV=production
APP_DEBUG=false
APP_URL=https://api.your-domain.com
APP_TIMEZONE=America/Vancouver

DB_CONNECTION=sqlite

FRONTEND_URL=https://app.your-domain.com
SANCTUM_EXPIRATION=1440

DEMO_MODE=false

# Demo reset schedule (requires scheduler running)
# Resets daily at midnight America/Vancouver when DEMO_MODE=true
```

## Deployment

Supported platforms:
- Shared hosting (cPanel, PHP 8.4+)
- VPS (DigitalOcean, Linode, AWS EC2)
- Platform as a Service (Laravel Forge, Ploi, Envoyer)
- Containerized (Docker, Kubernetes)

## Contributing

1. Follow domain-driven architecture patterns
2. Maintain full API endpoint test coverage
3. Use Form Request classes for validation
4. Use Resource classes for API responses
5. Keep controllers thin, use services for business logic
6. Run `vendor/bin/pint --dirty` before committing
7. Run `php artisan test` before submitting

## License

MIT
