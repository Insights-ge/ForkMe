# Multi-Tenancy and Feature Flags

## Tenancy Model

### What a Tenant Is

In this system, a **workspace** is the tenant. Every user belongs to one or more workspaces. All data in the system either belongs to a workspace or is system-level (e.g. plans, global config).

The word "workspace" is used throughout the codebase instead of "tenant" because it is a product-facing concept. Internally, workspace == tenant.

### Single-Database Approach

All tenants share the same database. Isolation is enforced through:
- `workspace_id` columns on all tenant-owned tables
- Application-level query scoping
- No raw queries that bypass the model layer

There is no database-per-tenant and no schema-per-tenant in V1.

---

## Tenant-Owned Entities

Every entity that belongs to a workspace must:

1. Have a `workspace_id` column (not nullable, indexed, foreign key to `workspaces`)
2. Use the `BelongsToWorkspace` trait
3. Have a global scope (or consistent scope helper) so queries are always scoped
4. Never be loaded without a workspace context in application code

### The `BelongsToWorkspace` Trait

```php
// app/Domains/Core/Tenancy/Concerns/BelongsToWorkspace.php

trait BelongsToWorkspace
{
    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }

    public function scopeForWorkspace(Builder $query, Workspace $workspace): Builder
    {
        return $query->where('workspace_id', $workspace->id);
    }
}
```

All tenant-scoped models use this trait. Services never query tenant-owned models without calling `forWorkspace()` or passing `workspace_id` explicitly.

---

## Resolving the Current Workspace

The current workspace is resolved early in the request lifecycle (middleware) and stored on a singleton or in the request context. All application code that needs the current workspace calls the resolver — it never reads `workspace_id` from session directly.

```php
// Example usage in an Action
class CreateContactAction
{
    public function execute(ContactData $data, Workspace $workspace): Contact
    {
        return Contact::create([
            'workspace_id' => $workspace->id,
            // ...
        ]);
    }
}
```

Actions receive the workspace explicitly — they do not resolve it themselves. This makes actions testable without mocking request state.

---

## Jobs Must Preserve Tenant Context

Queued jobs run outside the HTTP request lifecycle. They must serialize and restore workspace context.

```php
// app/Jobs/Concerns/HasTenantContext.php

trait HasTenantContext
{
    public int $workspaceId;

    public function forWorkspace(Workspace $workspace): static
    {
        $this->workspaceId = $workspace->id;
        return $this;
    }

    protected function resolveWorkspace(): Workspace
    {
        return Workspace::findOrFail($this->workspaceId);
    }
}
```

Every job that touches tenant data must use this trait and call `resolveWorkspace()` at the start of `handle()`.

---

## Filament Tenant Awareness

Filament resources that show tenant-owned data must scope their queries to the current workspace.

```php
// Example: only show contacts for current workspace
public static function getEloquentQuery(): Builder
{
    return parent::getEloquentQuery()
        ->forWorkspace(filament()->getTenant());
}
```

Filament v3+ supports multi-tenancy natively via `->tenant(Workspace::class)` on panel configuration. This project uses that approach so tenant scoping is handled at the panel level, not repeated in every resource.

---

## Feature Flags

### Why Feature Flags

Feature flags control which modules and capabilities a workspace can access. They enable:
- Gradual rollout of new features
- Plan-based access control
- Per-workspace overrides for special cases

### The Three Layers of Access

Access to a feature is resolved in this order:

```
1. Plan defaults          ← what the workspace's plan includes
2. Workspace overrides    ← manual toggles on top of the plan
3. User permissions       ← fine-grained permission within a workspace
```

The `FeatureFlagService` handles resolution. Callers do not need to know which layer granted or denied access.

### Feature Keys

Feature keys are defined as an enum:

```php
enum Feature: string
{
    case EmailMarketing = 'email_marketing';
    case Sms            = 'sms';              // reserved, not active
    case Booking        = 'booking';           // reserved, not active
}
```

Boolean features resolve to `true` or `false`. Limit-based entitlements resolve to an integer or `null` (unlimited).

### Entitlements

Some features carry quantitative limits rather than a simple on/off:

| Key | Type | Example |
|---|---|---|
| `max_contacts` | integer | 5000 |
| `max_monthly_emails` | integer | 50000 |
| `max_team_members` | integer | 5 |

These are stored as `entitlements` on the plan record and can be overridden per workspace.

### Data Model

```
plans
├── id
├── name
├── features (JSON)          ← default Feature booleans
└── entitlements (JSON)      ← default limit values

workspaces
├── id
├── plan_id
└── ...

workspace_feature_overrides
├── workspace_id
├── feature (string / enum)
└── value (string/bool/int)  ← overrides the plan default
```

### FeatureFlagService

```php
class FeatureFlagService
{
    public function isEnabled(Feature $feature, Workspace $workspace): bool;

    public function entitlementValue(string $key, Workspace $workspace): int|null;

    public function check(Feature $feature, Workspace $workspace): void; // throws if disabled
}
```

Callers in Actions or Services use `$this->featureFlags->check(Feature::EmailMarketing, $workspace)` before performing feature-gated operations. This throws a `FeatureNotAvailableException` if the feature is disabled.

---

## Future Module Rules

When SMS or Booking is added, it must follow the same model:

1. Add a new `Feature` enum case
2. Add plan defaults for the new feature
3. Any SMS or Booking models must use `BelongsToWorkspace`
4. Any SMS or Booking jobs must use `HasTenantContext`
5. Filament resources for the new module must be workspace-scoped
6. Feature access must be checked via `FeatureFlagService` before any feature-gated operation

No shortcuts. The pattern must be consistent across all modules.

---

## Testing Tenant Boundaries

For every tenant-owned action or service, write a test that asserts:

```
- workspace A cannot read workspace B's data
- queries are always scoped (assert workspace_id in WHERE clause)
- jobs restore correct workspace context
- webhook handlers resolve workspace correctly before writing data
```

Example test pattern:

```php
it('does not return contacts from other workspaces', function () {
    $workspaceA = Workspace::factory()->create();
    $workspaceB = Workspace::factory()->create();

    Contact::factory()->for($workspaceA)->count(3)->create();
    Contact::factory()->for($workspaceB)->count(2)->create();

    $contacts = Contact::query()->forWorkspace($workspaceA)->get();

    expect($contacts)->toHaveCount(3);
    expect($contacts->pluck('workspace_id')->unique()->first())->toBe($workspaceA->id);
});
```
