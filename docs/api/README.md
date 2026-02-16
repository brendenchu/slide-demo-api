# API Documentation

This directory contains comprehensive documentation for the Slide Demo API.

## Available Documentation

### 1. API Documentation (Markdown)
**File**: `API_DOCUMENTATION.md`

A comprehensive guide covering:
- Authentication (Bearer tokens via Laravel Sanctum)
- Rate limiting (60 requests/minute)
- Authorization & roles
- Complete endpoint reference
- Request/response examples
- Error handling
- CORS configuration
- Demo accounts for testing

### 2. Interactive Documentation (Scramble)
**URL**: https://vue-slide-demo.test/docs/api

Interactive documentation powered by Scramble includes:
- Live API endpoint browser
- Try-it-out functionality for testing
- Automatically generated from Laravel routes
- Real-time request/response examples
- OpenAPI 3.1.0 compliant

### 3. OpenAPI Specification
**File**: `openapi.json`

Machine-readable OpenAPI 3.1.0 specification that can be:
- Imported into Postman, Insomnia, or other API clients
- Used to generate client libraries
- Validated with OpenAPI tools
- Used for API contract testing

**Download URL**: https://vue-slide-demo.test/docs/api.json

## Quick Start

### 1. View Interactive Docs
```bash
open https://vue-slide-demo.test/docs/api
```

### 2. Test with cURL
```bash
# Login and save token
TOKEN=$(curl -s -X POST https://vue-slide-demo.test/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"client@demo.com","password":"password"}' \
  | jq -r '.data.token')

# Make authenticated request
curl -X GET https://vue-slide-demo.test/api/v1/projects \
  -H "Authorization: Bearer $TOKEN"
```

### 3. Import OpenAPI Spec
```bash
# Use with Postman
# File → Import → Upload openapi.json

# Or use with other tools that support OpenAPI 3.1
```

## Demo Credentials

Use these credentials to test the API:

| Email | Password | Role | Permissions |
|-------|----------|------|-------------|
| `client@demo.com` | `password` | Client | Create and manage own projects |
| `admin@demo.com` | `password` | Super Admin | Full system access |
| `consultant@example.com` | `password` | Consultant | View and manage projects |
| `guest@demo.com` | `password` | Guest | Read-only access |

## Base URLs

- **Development**: `https://vue-slide-demo.test/api/v1`
- **Production**: Configure via `FRONTEND_URL` environment variable

## Terms of Service Acceptance

After authentication, users must accept the current terms of service before accessing most API endpoints. Endpoints protected by the `ensure_terms_accepted` middleware return a `403` with `must_accept_terms: true` until the user accepts.

Exempt from the terms requirement: login, register, logout, get current user, and the terms endpoints themselves.

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/v1/terms` | Get current terms info and user acceptance status |
| POST | `/api/v1/terms/accept` | Accept the current terms (body: `{"accepted": true}`) |

## Authentication

All authenticated endpoints require a Bearer token:

```bash
Authorization: Bearer {your-token-here}
```

Get a token by:
1. POST to `/api/v1/auth/login` with email and password
2. POST to `/api/v1/auth/register` to create new account
3. Extract the `token` from the response

## Rate Limiting

- **Limit**: 60 requests per minute per user
- **Headers**:
  - `X-RateLimit-Limit`: Total allowed
  - `X-RateLimit-Remaining`: Requests remaining
- **Exceeded**: Returns `429 Too Many Requests`

## Key Features

- ✅ RESTful API design
- ✅ Token-based authentication (Sanctum)
- ✅ Role-based access control (RBAC)
- ✅ Terms of service acceptance enforcement
- ✅ Rate limiting
- ✅ Comprehensive validation
- ✅ Standardized error responses
- ✅ OpenAPI 3.1.0 specification
- ✅ Interactive documentation

## Additional Resources

- **Laravel Sanctum**: https://laravel.com/docs/12.x/sanctum
- **Spatie Permission**: https://spatie.be/docs/laravel-permission
- **Scramble Docs**: https://scramble.dedoc.co
- **OpenAPI Spec**: https://spec.openapis.org/oas/v3.1.0

## Support

For questions or issues:
1. Check the interactive documentation
2. Review `API_DOCUMENTATION.md`
3. Consult the test files in `tests/Feature/API`
4. Check Laravel Sanctum documentation for auth questions

---

**Last Updated**: February 16, 2026
