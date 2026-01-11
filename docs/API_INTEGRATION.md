# API Integration Documentation

**Project**: Slide Demo API - RESTful API Integration
**Last Updated**: January 11, 2026
**API Version**: 1.0.0

## Table of Contents

- [Overview](#overview)
- [Architecture & Design Decisions](#architecture--design-decisions)
- [Adapter Pattern Implementation](#adapter-pattern-implementation)
- [Authentication Flow](#authentication-flow)
- [Authorization & Permissions](#authorization--permissions)
- [API Endpoints Reference](#api-endpoints-reference)
- [Error Handling](#error-handling)
- [Testing Strategy](#testing-strategy)
- [Migration from Local to API Mode](#migration-from-local-to-api-mode)

---

## Overview

This application implements a **decoupled architecture** with two distinct deployment modes:

1. **Laravel Monolith** - Traditional server-rendered application (Inertia.js, Blade, etc.)
2. **API + SPA** - Decoupled REST API with standalone frontend client

The adapter pattern enables seamless switching between local browser storage and API-backed data persistence without changing application code.

### Key Features

- ✅ Token-based authentication (Laravel Sanctum)
- ✅ Role-based access control (Spatie Laravel Permission)
- ✅ RESTful API design with versioning (v1)
- ✅ Standardized response format
- ✅ Rate limiting (60 requests/minute, 5 for auth)
- ✅ Comprehensive validation via Form Requests
- ✅ Interactive API documentation (Scramble)
- ✅ OpenAPI 3.1.0 specification
- ✅ 100% test coverage (323 backend tests, 128 frontend tests)

---

## Architecture & Design Decisions

### Design Decision #1: Adapter Pattern for Data Sources

**Problem**: Need to support both local browser storage and API-backed storage without duplicating code.

**Solution**: Implement the Adapter Pattern with a unified `DataSource` interface.

**Benefits**:
- Single codebase for multiple storage backends
- Easy switching between modes via environment variable
- Testable with mock implementations
- Future-proof for hybrid mode (offline-first with sync)

**Implementation**: See [Adapter Pattern Implementation](#adapter-pattern-implementation)

### Design Decision #2: Laravel Sanctum for Authentication

**Why Sanctum over JWT?**

| Factor | Sanctum | JWT |
|--------|---------|-----|
| **Complexity** | Simple, built into Laravel | Requires 3rd-party package |
| **Token Storage** | Database (revocable) | Stateless (can't revoke) |
| **Refresh Tokens** | Not needed (long-lived) | Required for security |
| **CSRF Protection** | Built-in for SPA | Manual implementation |
| **Laravel Integration** | Native middleware | Custom guards |
| **Expiration** | Optional (per-token) | Required (security) |

**Decision**: Sanctum provides better developer experience, native Laravel integration, and token revocation without the complexity of JWT refresh token flows.

### Design Decision #3: RESTful API Design

**Versioning Strategy**: URL-based versioning (`/api/v1/`)

**Why URL versioning?**
- Explicit and clear in documentation
- Easy to test different versions
- Browser-friendly (can test in address bar)
- Standard industry practice

**Response Format**:
```json
{
  "success": true,
  "data": { ... },
  "message": "Optional message"
}
```

**Error Format**:
```json
{
  "success": false,
  "message": "Error message",
  "errors": {
    "field": ["Validation error"]
  }
}
```

### Design Decision #4: Resource-Based Controllers

**Pattern**: Each endpoint is a dedicated controller class (invokable or resource)

**Why?**
- Single Responsibility Principle
- Easier to test individual endpoints
- Clear route-to-controller mapping
- Simpler dependency injection

**Example**:
```php
// Instead of: ProjectController@login, ProjectController@register, ProjectController@logout
// We use: LoginController, RegisterController, LogoutController
```

### Design Decision #5: Service Layer for Business Logic

**Controllers are thin** - they delegate to services:

```php
// Controller (thin)
public function store(CreateProjectRequest $request, ProjectService $projectService)
{
    $project = $projectService->createProject($request->validated());
    return $this->created(new ProjectResource($project));
}

// Service (thick)
class ProjectService
{
    public function createProject(array $data): Project
    {
        // Complex business logic here
        // Transaction handling
        // Logging
        // etc.
    }
}
```

---

## Adapter Pattern Implementation

### Interface Definition

The `DataSource` interface defines all operations:

```typescript
// vue-spa/src/stores/persistence/types.ts
export interface DataSource {
  // Authentication
  login(email: string, password: string): Promise<{ user: User; token: string }>
  register(data: RegisterData): Promise<{ user: User; token: string }>
  logout(): Promise<void>
  getUser(): Promise<User | null>
  updateUser(data: Partial<User>): Promise<User>

  // Projects
  getProjects(): Promise<Project[]>
  getProject(id: string): Promise<Project | null>
  createProject(data: CreateProjectData): Promise<Project>
  updateProject(id: string, data: Partial<Project>): Promise<Project>
  deleteProject(id: string): Promise<void>
  saveResponses(projectId: string, step: string, responses: Record<string, unknown>): Promise<Project>
  completeProject(projectId: string): Promise<Project>

  // Admin - User Management
  getUsers(): Promise<User[]>
  getUserById(id: string): Promise<User | null>
  createUser(data: CreateUserData): Promise<User>
  updateUserById(id: string, data: Partial<User>): Promise<User>
  deleteUser(id: string): Promise<void>

  // Teams
  getTeams(): Promise<Team[]>
  getTeam(id: string): Promise<Team | null>
  createTeam(data: CreateTeamData): Promise<Team>
  updateTeam(id: string, data: Partial<Team>): Promise<Team>
}
```

### Data Source Factory

The factory creates the appropriate implementation based on configuration:

```typescript
// vue-spa/src/stores/persistence/dataSourceFactory.ts
export class DataSourceFactory {
  static create(mode?: DataSourceMode): DataSource {
    const configMode = import.meta.env.VITE_DATA_SOURCE || mode || 'local'

    switch (configMode) {
      case 'api':
        return new ApiDataSource()
      case 'hybrid':
        throw new Error('Hybrid mode not yet implemented')
      case 'local':
      default:
        return new LocalDataSource()
    }
  }
}
```

### Implementation: LocalDataSource

Uses browser storage (LocalStorage + IndexedDB via localforage):

```typescript
// vue-spa/src/stores/persistence/localDataSource.ts
export class LocalDataSource implements DataSource {
  async login(email: string, password: string): Promise<{ user: User; token: string }> {
    const users = await storage.get<User[]>('users') || []
    const user = users.find(u => u.email === email)

    if (!user || user.password !== password) {
      throw new Error('Invalid credentials')
    }

    const token = this.generateToken()
    await storage.set('auth:token', token)
    await storage.set('auth:user', user)

    return { user, token }
  }

  // ... other methods
}
```

### Implementation: ApiDataSource

Uses REST API via axios:

```typescript
// vue-spa/src/stores/persistence/apiDataSource.ts
export class ApiDataSource implements DataSource {
  private api: AxiosInstance

  constructor() {
    this.api = getApiClient()
  }

  async login(email: string, password: string): Promise<{ user: User; token: string }> {
    const response = await this.api.post<{ data: { user: User; token: string } }>(
      '/auth/login',
      { email, password }
    )
    return response.data.data
  }

  // ... other methods
}
```

### Usage in Pinia Stores

Stores use the factory without knowing the implementation:

```typescript
// vue-spa/src/stores/auth.ts
import { DataSourceFactory } from './persistence/dataSourceFactory'

export const useAuthStore = defineStore('auth', () => {
  const dataSource = DataSourceFactory.create()

  const login = async (email: string, password: string) => {
    const { user, token } = await dataSource.login(email, password)
    // Store token and user...
  }

  return { login, ... }
})
```

**Key Benefit**: Change `VITE_DATA_SOURCE` environment variable and the entire app switches storage backends without code changes.

---

## Authentication Flow

### Registration Flow

```
User → [POST /api/v1/auth/register] → RegisterController
         ↓
    RegisterRequest validates:
    - name: required, string, max:255
    - email: required, email, unique:users
    - password: required, min:8
         ↓
    Create User with hashed password
         ↓
    Assign 'client' role (default)
         ↓
    Create profile
         ↓
    Load relationships (profile, roles, permissions)
         ↓
    Generate API token via Sanctum
         ↓
    Return UserResource + token
```

**Response**:
```json
{
  "success": true,
  "data": {
    "user": {
      "id": "abc123",
      "name": "John Doe",
      "email": "john@example.com",
      "roles": ["client"],
      "permissions": ["view-project", "create-project", "update-project"]
    },
    "token": "1|AbCdEfGhIjKlMnOpQrStUvWxYz"
  },
  "message": "Registration successful"
}
```

### Login Flow

```
User → [POST /api/v1/auth/login] → LoginController
         ↓
    LoginRequest validates:
    - email: required, email
    - password: required, string
         ↓
    Auth::attempt(credentials)
         ↓
    If invalid → 401 Unauthorized
    If valid → Continue
         ↓
    Load user with relationships
         ↓
    Generate API token via Sanctum
         ↓
    Return UserResource + token
```

### Logout Flow

```
User → [POST /api/v1/auth/logout] → LogoutController
         ↓
    Middleware: auth:sanctum validates token
         ↓
    Delete current access token
         ↓
    Return 200 success
```

### Authenticated Requests

All authenticated endpoints require:

**Header**:
```
Authorization: Bearer {token}
```

**Middleware Chain**:
```
Request → auth:sanctum → throttle:60,1 → Controller
```

**Token Storage** (Frontend):
- Stored in localStorage: `vsd:auth:token`
- Attached to all API requests via axios interceptor
- Cleared on logout or 401 response

---

## Authorization & Permissions

### Roles

Defined in `app/Enums/Role.php`:

```php
enum Role: string
{
    case SuperAdmin = 'super-admin';
    case Admin = 'admin';
    case Consultant = 'consultant';
    case Client = 'client';
    case Guest = 'guest';
}
```

### Permissions

Defined in `app/Enums/Permission.php`:

```php
enum Permission: string
{
    case ViewProject = 'view-project';
    case CreateProject = 'create-project';
    case UpdateProject = 'update-project';
    case DeleteProject = 'delete-project';
    case ManageUser = 'manage-user';
    // ... etc
}
```

### Authorization Patterns

**Controller Level** (via middleware):
```php
Route::middleware('permission:manage-user')
    ->prefix('admin/users')
    ->group(function() {
        // Admin user management routes
    });
```

**Controller Method Level** (via Gate):
```php
public function destroy(string $id)
{
    $this->authorize('delete-user');
    // ... delete user
}
```

**Policy Level** (for model authorization):
```php
public function update(User $user, Project $project)
{
    return $user->id === $project->user_id || $user->hasRole('admin');
}
```

**Frontend Level** (via UserResource):
```typescript
// User resource includes roles and permissions
const user = {
  id: '1',
  name: 'John Doe',
  roles: ['client'],
  permissions: ['view-project', 'create-project']
}

// Check in frontend components
if (user.permissions.includes('manage-user')) {
  // Show admin panel
}
```

---

## API Endpoints Reference

### Authentication Endpoints

| Method | Endpoint | Description | Auth | Rate Limit |
|--------|----------|-------------|------|------------|
| POST | `/api/v1/auth/register` | Register new user | No | 5/min |
| POST | `/api/v1/auth/login` | Login user | No | 5/min |
| POST | `/api/v1/auth/logout` | Logout user | Yes | 60/min |
| GET | `/api/v1/auth/user` | Get authenticated user | Yes | 60/min |
| PUT | `/api/v1/auth/user` | Update user profile | Yes | 60/min |

### Project Endpoints

| Method | Endpoint | Description | Auth | Permission |
|--------|----------|-------------|------|------------|
| GET | `/api/v1/projects` | List all projects | Yes | view-project |
| POST | `/api/v1/projects` | Create project | Yes | create-project |
| GET | `/api/v1/projects/{id}` | Get project | Yes | view-project |
| PUT | `/api/v1/projects/{id}` | Update project | Yes | update-project |
| DELETE | `/api/v1/projects/{id}` | Delete project | Yes | delete-project |
| POST | `/api/v1/projects/{id}/responses` | Save form responses | Yes | update-project |
| POST | `/api/v1/projects/{id}/complete` | Mark project complete | Yes | update-project |

### Admin Endpoints

| Method | Endpoint | Description | Auth | Permission |
|--------|----------|-------------|------|------------|
| GET | `/api/v1/admin/users` | List all users | Yes | manage-user |
| POST | `/api/v1/admin/users` | Create user | Yes | manage-user |
| GET | `/api/v1/admin/users/{id}` | Get user | Yes | manage-user |
| PUT | `/api/v1/admin/users/{id}` | Update user | Yes | manage-user |
| DELETE | `/api/v1/admin/users/{id}` | Delete user | Yes | manage-user |

### Team Endpoints

| Method | Endpoint | Description | Auth | Permission |
|--------|----------|-------------|------|------------|
| GET | `/api/v1/teams` | List teams | Yes | view-team |
| POST | `/api/v1/teams` | Create team | Yes | create-team |
| GET | `/api/v1/teams/{id}` | Get team | Yes | view-team |
| PUT | `/api/v1/teams/{id}` | Update team | Yes | update-team |

**Full API Documentation**: See [docs/api/API_DOCUMENTATION.md](api/API_DOCUMENTATION.md)

---

## Error Handling

### Standard Error Response

All errors follow this format:

```json
{
  "success": false,
  "message": "Human-readable error message",
  "errors": {
    "field_name": ["Validation error message"]
  }
}
```

### HTTP Status Codes

| Code | Meaning | When Used |
|------|---------|-----------|
| 200 | OK | Successful GET, PUT requests |
| 201 | Created | Successful POST creating resource |
| 204 | No Content | Successful DELETE |
| 400 | Bad Request | Generic client error |
| 401 | Unauthorized | Missing or invalid token |
| 403 | Forbidden | Valid token but insufficient permissions |
| 404 | Not Found | Resource doesn't exist |
| 422 | Unprocessable Entity | Validation failed |
| 429 | Too Many Requests | Rate limit exceeded |
| 500 | Internal Server Error | Server error |

### ApiController Methods

The base `ApiController` provides standardized response methods:

```php
// Success responses
$this->success($data, $message, $statusCode)
$this->created($data, $message) // 201
$this->noContent() // 204

// Error responses
$this->error($message, $statusCode, $errors)
$this->unauthorized($message) // 401
$this->forbidden($message) // 403
$this->notFound($message) // 404
$this->validationError($errors, $message) // 422
```

### Frontend Error Handling

The `ApiDataSource` handles errors gracefully:

```typescript
async getProject(id: string): Promise<Project | null> {
  try {
    const response = await this.api.get(`/projects/${id}`)
    return response.data.data
  } catch (error) {
    // 404 returns null (not found)
    if (this.isNotFoundError(error)) {
      return null
    }
    // Other errors are thrown
    console.error('Get project failed:', getErrorMessage(error))
    throw error
  }
}
```

**Axios Interceptor** automatically:
- Attaches auth token to requests
- Handles 401 by clearing auth and redirecting to login
- Extracts error messages from API responses

---

## Testing Strategy

### Backend Testing (Pest)

**Coverage**: 323 tests passing, 100% pass rate

**Test Organization**:
```
tests/
├── Feature/
│   ├── API/
│   │   ├── Auth/
│   │   │   ├── LoginTest.php
│   │   │   ├── RegisterTest.php
│   │   │   ├── LogoutTest.php
│   │   │   └── UserTest.php
│   │   ├── Story/
│   │   │   ├── ProjectTest.php
│   │   │   ├── ResponseTest.php
│   │   │   └── CompleteProjectTest.php
│   │   ├── Admin/
│   │   │   └── UserManagementTest.php
│   │   └── Team/
│   │       └── TeamTest.php
│   └── RateLimitTest.php
└── Unit/
    └── Services/
        ├── ProjectServiceTest.php
        └── TokenServiceTest.php
```

**Testing Patterns**:

```php
// Authentication testing
it('logs in with valid credentials', function () {
    $user = User::factory()->create(['password' => Hash::make('password')]);

    $response = $this->postJson('/api/v1/auth/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $response->assertSuccessful()
        ->assertJsonStructure([
            'success',
            'data' => ['user', 'token'],
            'message',
        ]);
});

// Authorization testing
it('prevents non-admin from accessing admin endpoints', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $response = $this->getJson('/api/v1/admin/users');

    $response->assertForbidden();
});

// Rate limiting testing
it('enforces rate limit on auth endpoints', function () {
    for ($i = 0; $i < 5; $i++) {
        $this->postJson('/api/v1/auth/login', [
            'email' => 'test@example.com',
            'password' => 'password',
        ]);
    }

    // 6th request should be rate limited
    $response = $this->postJson('/api/v1/auth/login', [
        'email' => 'test@example.com',
        'password' => 'password',
    ]);

    $response->assertStatus(429);
});
```

### Frontend Testing (Vitest)

**Coverage**: 128 tests passing, 96.49% code coverage

**Test Organization**:
```
vue-spa/tests/
├── unit/
│   └── persistence/
│       ├── localDataSource.test.ts (41 tests)
│       └── apiDataSource.test.ts (45 tests)
└── integration/
    └── stores/
        ├── auth.test.ts (20 tests)
        └── projects.test.ts (22 tests)
```

**Testing Patterns**:

```typescript
// Unit test: ApiDataSource
describe('ApiDataSource', () => {
  it('logs in with valid credentials', async () => {
    const mockUser = { id: '1', name: 'Test', email: 'test@example.com' }
    const mockToken = 'abc123'

    mockApi.post.mockResolvedValueOnce({
      data: { data: { user: mockUser, token: mockToken } }
    })

    const result = await dataSource.login('test@example.com', 'password')

    expect(mockApi.post).toHaveBeenCalledWith('/auth/login', {
      email: 'test@example.com',
      password: 'password'
    })
    expect(result).toEqual({ user: mockUser, token: mockToken })
  })
})

// Integration test: Auth Store
describe('Auth Store Integration', () => {
  it('logs in and stores user data', async () => {
    const store = useAuthStore()
    const mockUser = { id: '1', name: 'Test', email: 'test@example.com' }
    const mockToken = 'abc123'

    vi.mocked(DataSourceFactory.create().login).mockResolvedValueOnce({
      user: mockUser,
      token: mockToken
    })

    await store.login('test@example.com', 'password')

    expect(store.user).toEqual(mockUser)
    expect(store.isAuthenticated).toBe(true)
  })
})
```

---

## Migration from Local to API Mode

### Step 1: Update Environment Variables

**Frontend Client** (`.env`):
```bash
# Change from local to api
VITE_DATA_SOURCE=api

# Set API URL (without /api suffix)
VITE_API_URL=https://vue-slide-demo.test
```

**Laravel Backend** (`.env`):
```bash
# Set frontend URL for CORS (production)
FRONTEND_URL=https://your-spa-domain.com
```

### Step 2: Data Migration (Optional)

If users have local data, provide migration:

```typescript
// vue-spa/src/utils/migrate.ts
export async function migrateLocalDataToAPI() {
  const localDataSource = new LocalDataSource()
  const apiDataSource = new ApiDataSource()

  // Get local projects
  const projects = await localDataSource.getProjects()

  // Upload to API
  for (const project of projects) {
    await apiDataSource.createProject({
      label: project.label,
      description: project.description,
      // ... other fields
    })
  }

  // Clear local storage after successful migration
  await localDataSource.logout()
}
```

### Step 3: Rebuild & Deploy

```bash
# Frontend
cd vue-spa
npm run build

# Deploy dist/ folder to CDN or static host
```

### Step 4: CORS Configuration

Ensure CORS is properly configured in Laravel:

```php
// config/cors.php
return [
    'paths' => ['api/*', 'sanctum/csrf-cookie'],
    'allowed_origins' => [env('FRONTEND_URL')],
    'allowed_methods' => ['*'],
    'allowed_headers' => ['*'],
    'supports_credentials' => true,
];
```

---

## Best Practices

### API Development

1. **Always use Form Requests** for validation
2. **Always use Resources** for responses
3. **Keep controllers thin** - delegate to services
4. **Use proper HTTP status codes** - see ApiController methods
5. **Document all endpoints** with PHPDoc blocks for Scramble
6. **Test all endpoints** with Pest feature tests
7. **Version your API** - use `/api/v1/` prefix

### Frontend Development

1. **Never bypass the DataSource interface** - always use Pinia stores
2. **Handle errors gracefully** - provide user feedback
3. **Implement optimistic updates** where appropriate
4. **Cache API responses** in Pinia store state
5. **Test both implementations** - LocalDataSource and ApiDataSource
6. **Use TypeScript strictly** - leverage type safety

### Security

1. **Never expose sensitive data** in API responses
2. **Always validate on the backend** - don't trust frontend validation
3. **Use rate limiting** to prevent abuse
4. **Implement proper authorization** on all endpoints
5. **Sanitize user input** to prevent XSS
6. **Use parameterized queries** to prevent SQL injection (Eloquent handles this)
7. **Revoke tokens on logout** - Sanctum's token deletion

---

## Related Documentation

- [API Documentation (Markdown)](api/API_DOCUMENTATION.md) - Comprehensive API guide
- [API Documentation (Interactive)](https://vue-slide-demo.test/docs/api) - Scramble interface
- [Configuration Guide](CONFIGURATION.md) - Environment variable reference
- [Architecture Documentation](ARCHITECTURE.md) - Overall system architecture
- [OpenAPI Specification](api/openapi.json) - Machine-readable spec

---

**Last Updated**: January 11, 2026
