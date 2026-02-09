# Slide Demo API Documentation

## Overview

The Slide Demo API is a RESTful API built with Laravel 12 that provides endpoints for managing stories/projects, users, teams, invitations, and notifications. The API uses Bearer token authentication and implements team-based access control for authorization.

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
      "permissions": ["view-project", "create-project", "update-project"]
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
- **Expiration**: Tokens expire after 1440 minutes (24 hours), configurable via `SANCTUM_EXPIRATION` env var
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

- **Auth endpoints** (login, register): **5 requests per minute**
- **API endpoints** (all other protected routes): **60 requests per minute per user**

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

### Team Roles

The API implements team-based access control with the following roles:

| Role | Description |
|------|-------------|
| `owner` | Team owner with full control. Cannot be assigned via invitation. |
| `admin` | Team administrator. Can manage members and invitations. |
| `member` | Standard team member. Can view and contribute to team projects. |

### Assignable Roles

Only `admin` and `member` roles can be assigned to team members via invitations.

### Checking User Info

User details and roles are returned in the authentication response:

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

## API Endpoints

### Public

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/v1/demo/status` | Check demo mode status and resource limits |
| GET | `/api/v1/names` | Get list of safe names |

### Authentication

| Method | Endpoint | Auth Required | Description |
|--------|----------|---------------|-------------|
| POST | `/api/v1/auth/register` | No | Register new user |
| POST | `/api/v1/auth/login` | No | Login user |
| POST | `/api/v1/auth/logout` | Yes | Logout user |
| GET | `/api/v1/auth/user` | Yes | Get authenticated user |
| PUT | `/api/v1/auth/user` | Yes | Update authenticated user |
| DELETE | `/api/v1/auth/user` | Yes | Delete authenticated user |

### Projects (Stories)

| Method | Endpoint | Auth Required | Description |
|--------|----------|---------------|-------------|
| GET | `/api/v1/projects` | Yes | List user's projects |
| POST | `/api/v1/projects` | Yes | Create new project |
| GET | `/api/v1/projects/{id}` | Yes | Get single project |
| PUT | `/api/v1/projects/{id}` | Yes | Update project |
| DELETE | `/api/v1/projects/{id}` | Yes | Delete project |
| POST | `/api/v1/projects/{id}/responses` | Yes | Save form responses |
| POST | `/api/v1/projects/{id}/complete` | Yes | Mark project complete |

### Teams

| Method | Endpoint | Auth Required | Description |
|--------|----------|---------------|-------------|
| GET | `/api/v1/teams` | Yes | List user's teams |
| POST | `/api/v1/teams` | Yes | Create new team |
| POST | `/api/v1/teams/current` | Yes | Set current active team |
| GET | `/api/v1/teams/{teamId}` | Yes | Get single team |
| PUT | `/api/v1/teams/{teamId}` | Yes | Update team |
| DELETE | `/api/v1/teams/{teamId}` | Yes | Delete team |

### Team Members

| Method | Endpoint | Auth Required | Description |
|--------|----------|---------------|-------------|
| GET | `/api/v1/teams/{teamId}/members` | Yes | List team members |
| PUT | `/api/v1/teams/{teamId}/members/{userId}/role` | Yes | Update member role |
| DELETE | `/api/v1/teams/{teamId}/members/{userId}` | Yes | Remove member from team |
| POST | `/api/v1/teams/{teamId}/transfer-ownership` | Yes | Transfer team ownership |

### Team Invitations

| Method | Endpoint | Auth Required | Description |
|--------|----------|---------------|-------------|
| GET | `/api/v1/teams/{teamId}/invitations` | Yes | List team invitations |
| POST | `/api/v1/teams/{teamId}/invitations` | Yes | Create invitation |
| DELETE | `/api/v1/teams/{teamId}/invitations/{invitationId}` | Yes | Delete invitation |

### User Invitations

| Method | Endpoint | Auth Required | Description |
|--------|----------|---------------|-------------|
| GET | `/api/v1/invitations` | Yes | List user's pending invitations |
| POST | `/api/v1/invitations/{invitationId}/accept` | Yes | Accept team invitation |
| POST | `/api/v1/invitations/{invitationId}/decline` | Yes | Decline team invitation |

### Notifications

| Method | Endpoint | Auth Required | Description |
|--------|----------|---------------|-------------|
| GET | `/api/v1/notifications` | Yes | List user's notifications |
| POST | `/api/v1/notifications/read-all` | Yes | Mark all notifications as read |
| POST | `/api/v1/notifications/{id}/read` | Yes | Mark single notification as read |

### Users

| Method | Endpoint | Auth Required | Description |
|--------|----------|---------------|-------------|
| GET | `/api/v1/users/search` | Yes | Search users by name/email (for team invitations) |

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

### Creating a Team

```bash
curl -X POST https://vue-slide-demo.test/api/v1/teams \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "My Team"
  }'
```

### Inviting a Team Member

```bash
curl -X POST https://vue-slide-demo.test/api/v1/teams/{teamId}/invitations \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "email": "colleague@example.com",
    "role": "member"
  }'
```

### Transferring Team Ownership

```bash
curl -X POST https://vue-slide-demo.test/api/v1/teams/{teamId}/transfer-ownership \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "user_id": 5
  }'
```

### Setting Current Team

```bash
curl -X POST https://vue-slide-demo.test/api/v1/teams/current \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "team_id": 1
  }'
```

### Listing Notifications

```bash
curl -X GET https://vue-slide-demo.test/api/v1/notifications \
  -H "Authorization: Bearer {token}"
```

### Searching Users

```bash
curl -X GET "https://vue-slide-demo.test/api/v1/users/search?q=john" \
  -H "Authorization: Bearer {token}"
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

## Demo Mode

When `DEMO_MODE=true` is set, the API enforces resource limits:

| Resource | Default Limit |
|----------|---------------|
| Max users | 25 |
| Max teams per user | 3 |
| Max projects per team | 5 |
| Max invitations per team | 5 |

Demo accounts are protected from deletion/modification by the `protect_demo_account` middleware.

## Testing the API

### Using cURL

```bash
# Login
TOKEN=$(curl -s -X POST https://vue-slide-demo.test/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"client@demo.com","password":"password"}' \
  | jq -r '.data.token')

# Use token for authenticated request
curl -X GET https://vue-slide-demo.test/api/v1/projects \
  -H "Authorization: Bearer $TOKEN"
```

### Demo Accounts

All demo accounts use the password `password`.

| Email | Role | Description |
|-------|------|-------------|
| `admin@demo.com` | Super Admin | Full administrative access |
| `admin@example.com` | Admin | Administrative access |
| `consultant@example.com` | Consultant | Consultant account |
| `client@demo.com` | Client | Standard user account |
| `guest@demo.com` | Guest | Read-only access |

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

**Last Updated**: February 9, 2026
**API Version**: 1.0.0
**Laravel Version**: 12.50.0
