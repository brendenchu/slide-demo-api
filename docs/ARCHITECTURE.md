# Architecture Documentation

**Project:** Vue Slide Demo - Laravel 12 Application
**Last Updated:** December 31, 2025
**Laravel Version:** 12
**PHP Version:** 8.4+

## Table of Contents

- [Overview](#overview)
- [Domain-Driven Organization](#domain-driven-organization)
- [Directory Structure](#directory-structure)
- [Naming Conventions](#naming-conventions)
- [Layer Responsibilities](#layer-responsibilities)
- [Frontend Architecture](#frontend-architecture)
- [Best Practices](#best-practices)

---

## Overview

This Laravel 12 application demonstrates a multi-step slide form system with role-based access control. The architecture follows **Domain-Driven Design (DDD)** principles with clear separation of concerns across business domains.

### Core Business Domains

1. **Account** - User profiles, teams, subscriptions, terms & agreements
2. **Story** - Project/story workflow, form responses, tokens
3. **Admin** - Administrative functions, user management
4. **Auth** - Authentication and authorization
5. **API** - External API endpoints

### Architectural Principles

- **Domain-Driven Design:** Code organized by business domain, not technical layer
- **Service Layer Pattern:** Complex business logic encapsulated in dedicated services
- **Repository Pattern:** Data access abstracted through Eloquent models
- **Resource Pattern:** API/frontend data transformation via Eloquent Resources
- **Request Validation:** Form Request classes for input validation
- **Role-Based Access Control:** Spatie Laravel Permission for permissions

---

## Domain-Driven Organization

### The Gold Standard: Models

Our Models directory serves as the **organizational blueprint** for the entire application:

```
app/Models/
├── User.php                    (Core entity - root level)
├── Account/                    (Account domain)
│   ├── Profile.php
│   ├── Team.php
│   ├── Plan.php
│   ├── Subscription.php
│   └── Terms/                  (Account subdomain)
│       ├── Agreement.php
│       └── Violation.php
└── Story/                      (Story domain)
    ├── Project.php
    ├── Response.php
    └── Token.php
```

**Why this structure?**
- User is a **core entity** used across all domains → root level
- Domain entities grouped together → easier to understand relationships
- Subdomains nested within parent domains → clear hierarchy

### Applying the Pattern Across Layers

All major application layers follow this same domain organization:

| Layer | Root-Level Items | Domain-Grouped Items |
|-------|------------------|----------------------|
| **Models** | User.php | Account/, Story/ |
| **Controllers** | Controller.php | Account/, Story/, Admin/, Auth/, API/ |
| **Services** | - | AccountService, ProjectService, TokenService |
| **Resources** | UserResource.php | Account/, Story/ |
| **Factories** | UserFactory.php | Account/, Story/ |
| **Enums** | Role, Permission | Account/, Story/ |
| **Form Requests** | - | Auth/, Story/Form/, Account/ |

---

## Directory Structure

### Backend Structure

```
app/
├── Enums/
│   ├── Role.php                        (Core - used across domains)
│   ├── Permission.php                  (Core - used across domains)
│   ├── Account/
│   │   └── TeamStatus.php              (Account-specific enum)
│   └── Story/
│       ├── ProjectStatus.php           (Story-specific enum)
│       └── ProjectStep.php             (Story-specific enum)
│
├── Http/
│   ├── Controllers/
│   │   ├── Controller.php              (Base controller)
│   │   ├── Account/
│   │   │   ├── AccountDashboardController.php
│   │   │   ├── TeamSelectController.php
│   │   │   └── Terms/
│   │   │       ├── SetupTermsController.php
│   │   │       └── AcceptTermsController.php
│   │   ├── Admin/
│   │   │   ├── AdminDashboardController.php
│   │   │   └── User/
│   │   │       ├── BrowseUsersController.php
│   │   │       ├── ManageUserController.php
│   │   │       └── ...
│   │   ├── Story/
│   │   │   ├── StoryDashboardController.php
│   │   │   ├── StoryController.php
│   │   │   ├── NewStoryController.php
│   │   │   ├── ContinueStoryController.php
│   │   │   ├── CompleteStoryController.php
│   │   │   ├── PublishStoryController.php
│   │   │   └── Form/
│   │   │       ├── LoadFormController.php
│   │   │       └── SaveFormController.php
│   │   ├── Auth/                       (Laravel Breeze controllers)
│   │   └── API/
│   │       └── GetCurrentTeamController.php
│   │
│   ├── Middleware/
│   │   ├── HandleInertiaRequests.php
│   │   └── ...
│   │
│   ├── Requests/
│   │   ├── Auth/
│   │   │   ├── LoginRequest.php
│   │   │   └── ...
│   │   ├── Account/
│   │   │   └── StoreTeamRequest.php
│   │   └── Story/
│   │       ├── StoryFormRequest.php    (Base form request)
│   │       └── Form/
│   │           ├── IntroFormRequest.php
│   │           ├── SectionAFormRequest.php
│   │           ├── SectionBFormRequest.php
│   │           └── SectionCFormRequest.php
│   │
│   └── Resources/
│       ├── UserResource.php            (Core resource)
│       ├── Account/
│       │   ├── ProfileResource.php
│       │   ├── TeamResource.php
│       │   └── TermsResource.php
│       └── Story/
│           ├── ProjectResource.php
│           ├── ResponseResource.php
│           └── TokenResource.php
│
├── Models/                             (See "Gold Standard" above)
│
├── Services/
│   ├── AccountService.php
│   ├── ProjectService.php
│   └── TokenService.php
│
└── Traits/
    ├── AcceptsTerms.php
    └── HasSettings.php
```

### Database Structure

```
database/
├── factories/
│   ├── UserFactory.php                 (Core factory)
│   ├── Account/
│   │   ├── ProfileFactory.php
│   │   ├── TeamFactory.php
│   │   ├── PlanFactory.php
│   │   └── SubscriptionFactory.php
│   └── Story/
│       ├── ProjectFactory.php
│       ├── ResponseFactory.php
│       └── TokenFactory.php
│
├── migrations/
│   ├── 0001_01_01_000000_create_users_table.php
│   ├── 0001_01_01_000001_create_cache_table.php
│   └── ...
│
└── seeders/
    ├── DatabaseSeeder.php
    └── RolePermissionSeeder.php
```

### Frontend Structure

```
resources/
├── js/
│   ├── app.ts                          (Entry point)
│   ├── bootstrap.ts                    (Bootstrap file)
│   │
│   ├── Components/
│   │   ├── Common/                     (Shared cross-domain components)
│   │   │   ├── UI/
│   │   │   │   ├── Buttons/
│   │   │   │   │   ├── PrimaryButton.vue
│   │   │   │   │   ├── SecondaryButton.vue
│   │   │   │   │   └── DangerButton.vue
│   │   │   │   ├── Navigation/
│   │   │   │   │   ├── NavLink.vue
│   │   │   │   │   ├── DropdownLink.vue
│   │   │   │   │   ├── DropdownMenu.vue
│   │   │   │   │   └── ResponsiveNavLink.vue
│   │   │   │   ├── ApplicationLogo.vue
│   │   │   │   └── ModalComponent.vue
│   │   │   └── Layout/
│   │   │       ├── PageHeader.vue
│   │   │       └── PageFooter.vue
│   │   ├── Flash/                      (Flash messages)
│   │   │   ├── FlashModal.vue
│   │   │   ├── FlashProvider.vue
│   │   │   └── types.d.ts
│   │   ├── Form/                       (Form inputs)
│   │   │   ├── FormCheckbox.vue
│   │   │   ├── FormField.vue
│   │   │   ├── FormLabel.vue
│   │   │   └── index.ts                (Barrel exports)
│   │   ├── Search/                     (Search components)
│   │   │   └── PaginatorLinks.vue
│   │   ├── Slide/                      (Slide system)
│   │   │   ├── SlideProvider.vue       (Main provider)
│   │   │   ├── Blocks/                 (Structure components)
│   │   │   │   ├── SlideContent.vue
│   │   │   │   ├── SlideControls.vue
│   │   │   │   ├── SlideFrame.vue
│   │   │   │   └── index.ts
│   │   │   ├── UI/                     (UI elements)
│   │   │   │   ├── ActionButton.vue
│   │   │   │   ├── NavigationButton.vue
│   │   │   │   └── index.ts
│   │   │   ├── types.d.ts              (TypeScript types)
│   │   │   └── index.ts
│   │   └── Story/                      (Story domain components)
│   │       └── Form/                   (Form-specific components)
│   │           ├── Forms/
│   │           │   ├── IntroForm.vue
│   │           │   ├── SectionAForm.vue
│   │           │   ├── SectionBForm.vue
│   │           │   ├── SectionCForm.vue
│   │           │   └── index.ts
│   │           └── UI/
│   │               ├── DashboardButton.vue
│   │               ├── LogoutButton.vue
│   │               ├── ProgressBar.vue
│   │               ├── ProgressTimeline.vue
│   │               └── index.ts
│   │
│   ├── Layouts/
│   │   ├── AuthenticatedLayout.vue
│   │   ├── GuestLayout.vue
│   │   └── StoryLayout.vue
│   │
│   ├── Pages/                          (Organized by domain)
│   │   ├── Account/
│   │   │   ├── ClientDashboard.vue
│   │   │   ├── AcceptTerms.vue
│   │   │   ├── Profile/
│   │   │   │   ├── EditProfile.vue
│   │   │   │   └── Partials/
│   │   │   ├── Team/
│   │   │   │   └── SelectTeam.vue
│   │   │   └── Partials/
│   │   ├── Admin/
│   │   │   ├── AdminDashboard.vue
│   │   │   ├── BrowseUsers.vue
│   │   │   ├── CreateUser.vue
│   │   │   ├── ShowUser.vue
│   │   │   └── Partials/
│   │   ├── Auth/
│   │   │   ├── LoginUser.vue
│   │   │   ├── RegisterUser.vue
│   │   │   ├── ForgotPassword.vue
│   │   │   ├── ResetPassword.vue
│   │   │   ├── ConfirmPassword.vue
│   │   │   └── VerifyEmail.vue
│   │   └── Story/
│   │       ├── NewStory.vue
│   │       ├── ContinueStory.vue
│   │       ├── CompleteStory.vue
│   │       └── StoryForm.vue
│   │
│   ├── types/                          (TypeScript type definitions)
│   │   ├── global.d.ts
│   │   ├── index.d.ts
│   │   └── vite-env.d.ts
│   │
│   └── utils/                          (Utility functions)
│       ├── ui.ts                       (UI utilities: cn, delay)
│       ├── format.ts                   (Formatting: toMoney, toPercent)
│       ├── math.ts                     (Math: add, subtract, multiply, divide)
│       ├── navigation.ts               (Navigation: back)
│       └── story/                      (Story domain utilities)
│           ├── form.ts                 (Form utilities)
│           └── workflow.ts             (Workflow utilities)
│
├── css/
│   └── app.css                         (Tailwind entry point)
│
└── views/
    └── app.blade.php                   (Root template for Inertia)
```

### Routes Structure

```
routes/
├── web.php                             (Main router - includes modules)
├── auth.php                            (Laravel Breeze auth routes)
├── console.php                         (Console commands)
└── modules/
    ├── account.php                     (Account routes)
    ├── admin.php                       (Admin routes)
    ├── story.php                       (Story routes)
    ├── profile.php                     (Profile routes - nested in account)
    └── team.php                        (Team routes - nested in account)
```

---

## Naming Conventions

### Controllers

**Pattern:** `{Domain?}{Action}{Entity}Controller`

| Type | Examples | Notes |
|------|----------|-------|
| **Invokable** | `StoryController`, `SaveFormController` | Single-action controllers |
| **Resource** | `ManageUserController` | Full CRUD operations |
| **Domain-prefixed** | `AccountDashboardController`, `AdminDashboardController`, `StoryDashboardController` | When multiple DashboardControllers exist across domains |

**Why domain prefixes for some controllers?**
- When the same controller name appears in multiple domains (e.g., DashboardController)
- Improves clarity: `Admin\AdminDashboardController` vs `Admin\DashboardController`
- Prevents ambiguity in stack traces and logs

### Services

**Pattern:** `{Domain}Service`

- `AccountService` - User account operations
- `ProjectService` - Project/story management
- `TokenService` - Token handling

### Resources

**Pattern:** `{Entity}Resource`

- `UserResource` (core) → root level
- `ProfileResource`, `TeamResource` → Account/
- `ProjectResource`, `TokenResource` → Story/

### Form Requests

**Pattern:** `{Action}{Entity}Request` or `{Section}FormRequest`

- `LoginRequest` → Auth/
- `StoreTeamRequest` → Account/
- `IntroFormRequest`, `SectionAFormRequest` → Story/Form/

### Factories

**Pattern:** `{Entity}Factory`

- `UserFactory` → root level
- `TeamFactory`, `ProfileFactory` → Account/
- `ProjectFactory`, `TokenFactory` → Story/

### Enums

**Pattern:** `{Concept}` or `{Entity}{Property}`

**Root Level (cross-domain):**
- `Role`, `Permission`

**Domain Level:**
- `TeamStatus` → Account/
- `ProjectStatus`, `ProjectStep` → Story/

### Models

**Pattern:** `{Entity}`

- `User` → root level
- Domain entities → respective domain folders

---

## Layer Responsibilities

### Models

**Purpose:** Data structure, relationships, business logic intrinsic to the entity

**Responsibilities:**
- Define database table structure via migrations
- Define eloquent relationships (`hasMany`, `belongsTo`, etc.)
- Define attribute casting
- Define scopes for common queries
- Model events (creating, created, updating, updated, etc.)

**Example:**
```php
class Project extends Model
{
    use HasFactory;

    protected $fillable = ['key', 'label', 'description', 'status'];

    public function teams(): BelongsToMany
    {
        return $this->belongsToMany(Team::class, 'projects_teams');
    }

    public function responses(): HasMany
    {
        return $this->hasMany(Response::class);
    }

    protected function casts(): array
    {
        return [
            'status' => ProjectStatus::class,
        ];
    }
}
```

### Controllers

**Purpose:** HTTP request handling, route-to-service delegation

**Responsibilities:**
- Receive and validate HTTP requests (via Form Requests)
- Delegate business logic to Services
- Return Inertia/JSON responses
- Handle redirects and flash messages

**Single Responsibility:** Controllers should be thin - business logic belongs in Services.

**Example:**
```php
class NewStoryController extends Controller
{
    public function store(
        Request $request,
        ProjectService $projectService,
        TokenService $tokenService
    ): RedirectResponse {
        $project = $projectService->createProject($request->user()->currentTeam());
        $token = $tokenService->createToken($project);

        return to_route('story.form', [
            'project' => $project,
            'step' => 'intro',
            'token' => $token->public_id,
        ]);
    }
}
```

### Services

**Purpose:** Complex business logic, multi-model orchestration

**Responsibilities:**
- Encapsulate business workflows
- Coordinate multiple models
- Handle complex data transformations
- Manage transactions
- Log important operations

**Fluent Interface Pattern:**
```php
$responses = $projectService
    ->setProject($project)
    ->setSteps('intro', 'section-a', 'section-b')
    ->getResponsesArray();
```

**Example:**
```php
class ProjectService
{
    protected Project $project;
    protected array $steps = [];

    public function setProject(Project|string $project): self
    {
        $this->project = $project instanceof Project
            ? $project
            : Project::where('public_id', $project)->first();

        return $this;
    }

    public function publishProject(): bool
    {
        $this->project->status = ProjectStatus::PUBLISHED;
        $saved = $this->project->save();

        if ($saved) {
            Log::info('Project published', ['project_id' => $this->project->public_id]);
        }

        return $saved;
    }
}
```

### Resources

**Purpose:** Transform model data for API/frontend consumption

**Responsibilities:**
- Format data for JSON responses
- Control what attributes are exposed
- Include related resources
- Apply business rules to presentation

**Example:**
```php
class ProjectResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->public_id,
            'label' => $this->label,
            'description' => $this->description,
            'status' => $this->status->value,
            'created_at' => $this->created_at->toISOString(),
        ];
    }
}
```

### Form Requests

**Purpose:** Centralized request validation

**Responsibilities:**
- Define validation rules
- Define authorization logic
- Prepare/transform data before validation
- Custom error messages

**Example:**
```php
class LoginRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge([
            'email' => $this->normalizeEmailInput($this->input('email')),
        ]);
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ];
    }
}
```

### Enums

**Purpose:** Define fixed sets of values with behavior

**Responsibilities:**
- Define allowed values
- Provide helper methods (labels, colors, etc.)
- Type-safe constants

**Example:**
```php
enum ProjectStatus: string
{
    case DRAFT = 'draft';
    case PUBLISHED = 'published';

    public function label(): string
    {
        return match($this) {
            self::DRAFT => 'Draft',
            self::PUBLISHED => 'Published',
        };
    }
}
```

---

## Frontend Architecture

### Component Organization

Frontend components follow the same domain-driven principles as the backend:

**Organization Hierarchy:**

1. **Common/** - Shared components used across all domains
   - `UI/Buttons/` - Button components (Primary, Secondary, Danger)
   - `UI/Navigation/` - Navigation components (NavLink, Dropdown, etc.)
   - `UI/` - General UI components (Logo, Modal)
   - `Layout/` - Layout components (PageHeader, PageFooter)

2. **Domain-Specific** - Components specific to business domains
   - `Story/Form/` - Story form components and UI
   - `Flash/` - Flash message components
   - `Form/` - Reusable form input components
   - `Slide/` - Multi-step slide system

3. **Pages/** - Inertia.js page components organized by domain
   - `Account/`, `Admin/`, `Auth/`, `Story/`

**Naming Convention:**
- Component files: PascalCase (e.g., `PrimaryButton.vue`)
- Directory names: PascalCase for components (e.g., `Common/`, `Story/`)
- Barrel exports via `index.ts` for grouped components

### Utils Organization

Utility functions are organized by purpose and domain:

**General Utilities (root level):**
- `utils/ui.ts` - UI utilities (cn, delay)
- `utils/format.ts` - Formatting (toMoney, toPercent)
- `utils/math.ts` - Mathematical operations (add, subtract, multiply, divide)
- `utils/navigation.ts` - Navigation utilities (back)

**Domain-Specific Utilities:**
- `utils/story/form.ts` - Story form utilities (saveForm, delta, nullifyFields)
- `utils/story/workflow.ts` - Story workflow utilities (prevNextSteps, completeStory)

**Import Pattern:**
```typescript
import { cn, delay } from '@/utils/ui'
import { toMoney } from '@/utils/format'
import { saveForm } from '@/utils/story/form'
```

### Slide System

The core feature is a reusable multi-step slide form system built with Vue 3 Composition API.

**Key Components:**

1. **SlideProvider.vue** - Main provider managing state, navigation, pagination
2. **Blocks/** - Frame and Content components for slide structure
3. **UI/** - Reusable UI elements (buttons, indicators)

**Props:**
- `current` - Current page index
- `pages` - Total number of pages
- `actions` - Navigation actions (next, previous, submit)
- `direction` - Animation direction

**Usage:**
```vue
<SlideProvider :current="1" :pages="5" :actions="actions">
  <template #page-1>
    <IntroForm />
  </template>
  <template #page-2>
    <SectionAForm />
  </template>
</SlideProvider>
```

### Inertia.js Integration

- **Pages/** components map to Laravel routes
- Server-side rendering via `Inertia::render()`
- Props passed from controllers as arrays
- Form handling via `useForm()` composable

### TypeScript Types

- Type definitions centralized in `types/` directory
- Component-specific types alongside components (e.g., `Slide/types.d.ts`)
- Global types in `types/global.d.ts`
- Auto-imported via `@/types` path alias

---

## Best Practices

### Adding New Features

When adding a new feature, ask:

1. **What domain does this belong to?**
   - Account, Story, Admin, Auth, API, or new domain?

2. **What backend layers are affected?**
   - Model? → Add to `app/Models/{Domain}/`
   - Controller? → Add to `app/Http/Controllers/{Domain}/`
   - Service? → Add to `app/Services/`
   - Resource? → Add to `app/Http/Resources/{Domain}/`
   - Enum? → Add to `app/Enums/{Domain}/`

3. **What frontend layers are affected?**
   - Page component? → Add to `resources/js/Pages/{Domain}/`
   - Domain component? → Add to `resources/js/Components/{Domain}/`
   - Shared component? → Add to `resources/js/Components/Common/`
   - Utility function? → Add to `resources/js/utils/` or `resources/js/utils/{domain}/`
   - Type definition? → Add to `resources/js/types/` or component-specific `types.d.ts`

4. **Follow existing patterns**
   - Check sibling files for naming conventions
   - Use the same validation approach (array vs string rules)
   - Follow the service pattern for complex logic
   - Use barrel exports (`index.ts`) for grouped components
   - Keep utilities focused and single-purpose

### File Placement Decision Tree

**Backend:**
```
Is it a core entity used across ALL domains?
├─ YES → Root level (User, Role, Permission)
└─ NO → Which domain does it belong to?
    ├─ Account → app/{Layer}/Account/
    ├─ Story → app/{Layer}/Story/
    ├─ Admin → app/{Layer}/Admin/
    └─ Auth → app/{Layer}/Auth/
```

**Frontend:**
```
Is it a shared UI component used across ALL domains?
├─ YES → Components/Common/
│   ├─ Button/Navigation/etc? → Common/UI/{Type}/
│   └─ Layout element? → Common/Layout/
└─ NO → Is it domain-specific?
    ├─ YES → Components/{Domain}/
    └─ Utility function?
        ├─ General purpose? → utils/
        └─ Domain-specific? → utils/{domain}/
```

### Code Organization Checklist

Before committing new code:

**Backend:**
- [ ] Does the file location match the domain pattern?
- [ ] Are namespace imports using the correct domain path?
- [ ] Does the naming follow the established convention?
- [ ] Is business logic in Services, not Controllers?
- [ ] Are validation rules in Form Request classes?
- [ ] Does the factory exist and use the correct namespace?
- [ ] Are tests organized to match the code structure?

**Frontend:**
- [ ] Is the component in the correct location (Common vs Domain)?
- [ ] Are imports using `@/` path aliases correctly?
- [ ] Does the component use PascalCase naming?
- [ ] Are utilities properly categorized (general vs domain)?
- [ ] Are TypeScript types defined for props and emits?
- [ ] Is the component exported via barrel exports if grouped?
- [ ] Are shared utilities extracted from component files?

### Migration Guidelines

- **Never modify past migrations** - always create new migrations
- Use descriptive migration names
- Foreign keys should reference `public_id` columns for security
- Always add indexes for foreign keys

### Testing Guidelines

- Use Pest for all tests
- Feature tests in `tests/Feature/{Domain}/`
- Unit tests in `tests/Unit/{Domain}/`
- Use factories with appropriate states
- Use datasets for testing multiple similar scenarios

---

## Version History

| Version | Date | Changes |
|---------|------|---------|
| 1.1 | 2025-12-31 | Added frontend organization (Common components, utils structure, Story/Form rename) |
| 1.0 | 2025-12-31 | Initial architecture documentation after backend Phase 1 & 2 refactoring |

---

## Related Documentation

- [CLAUDE.md](../CLAUDE.md) - Claude Code integration instructions
- [README.md](../README.md) - Project overview and setup instructions
