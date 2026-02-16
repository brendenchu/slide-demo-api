# Slide Demo API Documentation

## Overview

The Slide Demo API is a RESTful API built with Laravel 12 that provides endpoints for managing stories/projects, users, teams, and authentication. The API uses Bearer token authentication and implements role-based access control (RBAC) for authorization.

**Base URL**: `https://vue-slide-demo.test/api/v1`

**API Version**: 1.0.0

## Interactive Documentation

Interactive API documentation powered by Scramble is available at:

**URL**: https://vue-slide-demo.test/docs/api

The interactive documentation includes:
- Complete endpoint listing with descriptions
- Request/response examples
- Try-it-out functionality for testing endpoints
- OpenAPI specification download

## Authentication

### Overview

The API uses **Bearer Token authentication** powered by Laravel Sanctum. All authenticated endpoints require an `Authorization` header with a valid API token.

### Obtaining a Token

**Endpoint**: `POST /api/v1/auth/login`

**Request Body**:
```json
{
  "email": "user@example.com",
  "password": "password"
}
```

**Response**:
```json
{
  "success": true,
  "data": {
    "user": {
      "id": "1",
      "name": "Test User",
      "email": "user@example.com",
      "roles": ["client"],
      "permissions": ["view-project", "create-project", "update-project"],
      "must_accept_terms": true
    },
    "token": "1|AbCdEfGhIjKlMnOpQrStUvWxYz..."
  },
  "message": "Login successful"
}
```

### Using the Token

Include the token in the `Authorization` header of subsequent requests:

```
Authorization: Bearer 1|AbCdEfGhIjKlMnOpQrStUvWxYz...
```

### Token Lifecycle

- **Creation**: Tokens are created upon successful login or registration
- **Storage**: Tokens are stored in the `personal_access_tokens` database table
- **Expiration**: Tokens do not expire by default (configurable in `config/sanctum.php`)
- **Revocation**: Tokens are immediately revoked upon logout
- **Multi-device**: Users can have multiple active tokens (one per device/session)

### Registering a New Account

**Endpoint**: `POST /api/v1/auth/register`

**Request Body**:
```json
{
  "name": "New User",
  "email": "newuser@example.com",
  "password": "password",
  "password_confirmation": "password"
}
```

**Response**: Same as login response (includes user data and token)

### Logging Out

**Endpoint**: `POST /api/v1/auth/logout`

**Headers**: `Authorization: Bearer {token}`

**Response**:
```json
{
  "success": true,
  "data": null,
  "message": "Logout successful"
}
```

## Rate Limiting

### Default Rate Limits

All API endpoints are rate-limited to **60 requests per minute per user**.

**Rate Limit Headers**:
```
X-RateLimit-Limit: 60
X-RateLimit-Remaining: 59
```

### Rate Limit Exceeded

When the rate limit is exceeded, the API returns a `429 Too Many Requests` response:

```json
{
  "message": "Too Many Attempts."
}
```

### Adjusting Rate Limits

Rate limits can be configured in `bootstrap/app.php` by modifying the throttle middleware configuration.

## Authorization & Roles

### Available Roles

The API implements the following roles:

| Role | Description | Key Permissions |
|------|-------------|-----------------|
| `super-admin` | Full system access | All permissions |
| `admin` | Administrative access | User and project management |
| `consultant` | Project consultant | View and manage projects |
| `client` | Standard user | View and manage own projects |
| `guest` | Read-only access | View projects only |

### Permission System

Permissions are granular and follow the pattern: `{action}-{resource}`

**Examples**:
- `view-project`, `create-project`, `update-project`, `delete-project`
- `view-user`, `create-user`, `update-user`, `delete-user`

### Checking Permissions

User permissions are returned in the authentication response:

```json
{
  "user": {
    "roles": ["client"],
    "permissions": ["view-project", "create-project", "update-project"]
  }
}
```

## Response Format

### Standard Success Response

```json
{
  "success": true,
  "data": { ... },
  "message": "Operation successful"
}
```

### Standard Error Response

```json
{
  "success": false,
  "message": "Error message here",
  "errors": {
    "field": ["Validation error message"]
  }
}
```

### HTTP Status Codes

| Status Code | Meaning |
|-------------|---------|
| 200 | OK - Request successful |
| 201 | Created - Resource created successfully |
| 204 | No Content - Successful deletion |
| 400 | Bad Request - Invalid request format |
| 401 | Unauthorized - Authentication required or token invalid |
| 403 | Forbidden - Insufficient permissions |
| 404 | Not Found - Resource doesn't exist |
| 422 | Unprocessable Entity - Validation failed |
| 429 | Too Many Requests - Rate limit exceeded |
| 500 | Internal Server Error - Server error |

## Terms of Service Acceptance

After authenticating, users must accept the current terms of service before accessing most protected endpoints. The `ensure_terms_accepted` middleware enforces this.

### How It Works

1. On login/register, the user resource includes a `must_accept_terms` boolean field
2. If `must_accept_terms` is `true`, all endpoints behind the `ensure_terms_accepted` middleware return `403`
3. The client should redirect the user to accept terms before continuing
4. After acceptance, subsequent requests proceed normally

### Exempt Endpoints

The following authenticated endpoints do **not** require terms acceptance:
- `POST /api/v1/auth/logout`
- `GET /api/v1/auth/user`
- `GET /api/v1/terms`
- `POST /api/v1/terms/accept`

### Terms Endpoints

| Method | Endpoint | Auth Required | Description |
|--------|----------|---------------|-------------|
| GET | `/api/v1/terms` | Yes | Get current terms info and acceptance status |
| POST | `/api/v1/terms/accept` | Yes | Accept the current terms |

#### GET `/api/v1/terms`

**Response**:
```json
{
  "success": true,
  "data": {
    "version": "1.0",
    "label": "Terms of Service",
    "url": "https://example.com/terms",
    "accepted": false
  }
}
```

#### POST `/api/v1/terms/accept`

**Request Body**:
```json
{
  "accepted": true
}
```

**Response**:
```json
{
  "success": true,
  "data": null,
  "message": "Terms accepted successfully."
}
```

### Middleware 403 Response

When an endpoint requires terms acceptance and the user has not accepted:

```json
{
  "success": false,
  "message": "You must accept the current terms of service before continuing.",
  "must_accept_terms": true,
  "terms": {
    "version": "1.0",
    "label": "Terms of Service",
    "url": "https://example.com/terms"
  }
}
```

## API Endpoints

### Authentication

| Method | Endpoint | Auth Required | Terms Required | Description |
|--------|----------|---------------|----------------|-------------|
| POST | `/api/v1/auth/register` | No | No | Register new user |
| POST | `/api/v1/auth/login` | No | No | Login user |
| POST | `/api/v1/auth/logout` | Yes | No | Logout user |
| GET | `/api/v1/auth/user` | Yes | No | Get authenticated user |
| PUT | `/api/v1/auth/user` | Yes | Yes | Update authenticated user |
| DELETE | `/api/v1/auth/user` | Yes | Yes | Delete authenticated user |

### Terms

| Method | Endpoint | Auth Required | Terms Required | Description |
|--------|----------|---------------|----------------|-------------|
| GET | `/api/v1/terms` | Yes | No | Get current terms and acceptance status |
| POST | `/api/v1/terms/accept` | Yes | No | Accept current terms |

### Projects (Stories)

All project endpoints require terms acceptance.

| Method | Endpoint | Auth Required | Permission | Description |
|--------|----------|---------------|------------|-------------|
| GET | `/api/v1/projects` | Yes | `view-project` | List user's projects |
| GET | `/api/v1/projects/{id}` | Yes | `view-project` | Get single project |
| POST | `/api/v1/projects` | Yes | `create-project` | Create new project |
| PUT | `/api/v1/projects/{id}` | Yes | `update-project` | Update project |
| DELETE | `/api/v1/projects/{id}` | Yes | `delete-project` | Delete project |
| POST | `/api/v1/projects/{id}/responses` | Yes | `update-project` | Save form responses |
| POST | `/api/v1/projects/{id}/complete` | Yes | `update-project` | Mark project complete |

### User Management (Admin)

All admin endpoints require terms acceptance.

| Method | Endpoint | Auth Required | Permission | Description |
|--------|----------|---------------|------------|-------------|
| GET | `/api/v1/admin/users` | Yes | `view-user` | List all users |
| GET | `/api/v1/admin/users/{id}` | Yes | `view-user` | Get single user |
| POST | `/api/v1/admin/users` | Yes | `create-user` | Create new user |
| PUT | `/api/v1/admin/users/{id}` | Yes | `update-user` | Update user |
| DELETE | `/api/v1/admin/users/{id}` | Yes | `delete-user` | Delete user |

### Teams

All team endpoints require terms acceptance.

| Method | Endpoint | Auth Required | Permission | Description |
|--------|----------|---------------|------------|-------------|
| GET | `/api/v1/teams` | Yes | N/A | List user's teams |
| GET | `/api/v1/teams/{id}` | Yes | N/A | Get single team |
| POST | `/api/v1/teams` | Yes | N/A | Create new team |
| PUT | `/api/v1/teams/{id}` | Yes | N/A | Update team |

## Common Request Examples

### Creating a Project

```bash
curl -X POST https://vue-slide-demo.test/api/v1/projects \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "title": "My New Story"
  }'
```

**Response**:
```json
{
  "success": true,
  "data": {
    "id": "abc123",
    "title": "My New Story",
    "status": "draft",
    "current_step": "intro",
    "responses": {},
    "created_at": "2026-01-11T15:30:00.000000Z"
  },
  "message": "Project created successfully"
}
```

### Saving Form Responses

```bash
curl -X POST https://vue-slide-demo.test/api/v1/projects/abc123/responses \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "step": "section_a",
    "responses": {
      "question1": "Answer 1",
      "question2": "Answer 2"
    }
  }'
```

### Listing Projects

```bash
curl -X GET https://vue-slide-demo.test/api/v1/projects \
  -H "Authorization: Bearer {token}"
```

**Response**:
```json
{
  "success": true,
  "data": [
    {
      "id": "abc123",
      "title": "My Story",
      "status": "in_progress",
      "current_step": "section_a",
      "created_at": "2026-01-11T15:30:00.000000Z"
    }
  ],
  "message": "Projects retrieved successfully"
}
```

## Error Handling

### Validation Errors

When validation fails, the API returns a `422` status with error details:

```json
{
  "success": false,
  "message": "The given data was invalid.",
  "errors": {
    "email": [
      "The email has already been taken."
    ],
    "password": [
      "The password must be at least 8 characters."
    ]
  }
}
```

### Authentication Errors

**401 Unauthorized** - Invalid or missing token:
```json
{
  "message": "Unauthenticated."
}
```

**403 Forbidden** - Insufficient permissions:
```json
{
  "success": false,
  "message": "Unauthorized action."
}
```

**403 Forbidden** - Terms not accepted:
```json
{
  "success": false,
  "message": "You must accept the current terms of service before continuing.",
  "must_accept_terms": true,
  "terms": {
    "version": "1.0",
    "label": "Terms of Service",
    "url": "https://example.com/terms"
  }
}
```

### Resource Not Found

**404 Not Found**:
```json
{
  "success": false,
  "message": "Resource not found"
}
```

## CORS Configuration

### Allowed Origins

The API accepts requests from the following origins:
- `http://localhost:5173` (Development - default port)
- `http://localhost:5174` (Development - alternate port)
- Configurable production URL via `FRONTEND_URL` environment variable

### Allowed Methods

- GET, HEAD, POST, PUT, PATCH, DELETE, OPTIONS

### Allowed Headers

- Content-Type, Authorization, Accept, X-Requested-With

### Credentials

- Credentials are supported (cookies, authorization headers)

## Testing the API

### Using cURL

```bash
# Login
TOKEN=$(curl -X POST https://vue-slide-demo.test/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"client@demo.com","password":"password"}' \
  | jq -r '.data.token')

# Use token for authenticated request
curl -X GET https://vue-slide-demo.test/api/v1/projects \
  -H "Authorization: Bearer $TOKEN"
```

### Using Postman

1. Import the Postman collection from `docs/api/vue-slide-demo.postman_collection.json`
2. Set the `{{baseUrl}}` variable to `https://vue-slide-demo.test/api/v1`
3. Login to obtain a token
4. Set the `{{token}}` variable with the returned token
5. Test endpoints using the collection

### Demo Accounts

The following demo accounts are seeded in the development database:

| Email | Password | Role | Description |
|-------|----------|------|-------------|
| `client@demo.com` | `password` | Client | Standard user account |
| `admin@demo.com` | `password` | Super Admin | Full administrative access |
| `consultant@example.com` | `password` | Consultant | Consultant account |
| `guest@demo.com` | `password` | Guest | Read-only access |

## Additional Resources

- **Interactive Docs**: https://vue-slide-demo.test/docs/api
- **OpenAPI Spec**: https://vue-slide-demo.test/docs/api.json
- **Source Code**: Check the `app/Http/Controllers/API` directory
- **Tests**: See `tests/Feature/API` for API test examples

## Support & Contact

For questions or issues regarding the API:
- Review the interactive documentation
- Check the test files for usage examples
- Refer to Laravel Sanctum documentation for authentication questions
- Consult Spatie Permission documentation for authorization questions

---

**Last Updated**: February 16, 2026
**API Version**: 1.0.0
**Laravel Version**: 12
