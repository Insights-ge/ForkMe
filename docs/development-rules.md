# Development Rules

These rules apply to all contributors and all modules. They are not suggestions.

---

## Tenancy Rules

**R-T1: Every tenant-owned model must have `workspace_id`.**
No nullable, no optional. If a record belongs to a workspace, the column is required and has a foreign key constraint.

**R-T2: Models must use the `BelongsToWorkspace` trait.**
Do not write `where('workspace_id', ...)` manually in queries. Use `->forWorkspace($workspace)` scope.

**R-T3: Services and Actions must receive the workspace explicitly.**
Never resolve the current workspace from the session inside a Service or Action. The workspace is passed in as a parameter. This keeps the code testable.

**R-T4: Jobs must use the `HasTenantContext` trait.**
Every queued job that touches tenant data serializes `workspace_id` and resolves it at the start of `handle()`.

**R-T5: Filament resources must be workspace-scoped.**
Use the panel-level tenancy config or override `getEloquentQuery()` to scope every list and detail view to the current workspace.

**R-T6: Webhook handlers must resolve workspace safely.**
Never trust a workspace ID from a webhook payload directly. Look up the workspace via the `ProviderConnection` tied to the incoming message ID.

---

## Provider Rules

**R-P1: Provider-specific code must not leave `Integrations/`.**
`BrevoApiClient`, `SmtpTransportFactory`, and anything else inside `Integrations/Email/` must never be imported into `Domains/` or `Filament/`.

**R-P2: Campaign code must only know `EmailProviderContract`.**
`EmailDeliveryService` resolves an adapter from the registry. Nothing above it knows the provider type.

**R-P3: `SendMessageData` must be provider-agnostic.**
Never add a field to `SendMessageData` that only one provider understands. Use the `headers` or `tags` array for optional provider hints.

**R-P4: `ProviderSendResult` must be provider-agnostic.**
The result DTO contains only `success`, `providerMessageId`, `errorCode`, `errorMessage`. Nothing Brevo-specific, nothing SMTP-specific.

**R-P5: Provider credentials are always encrypted.**
`ProviderConnection.config` uses Laravel's `encrypted` cast. Never store plaintext credentials in any column or log.

---

## Filament Rules

**R-F1: Filament resources must stay thin.**
A resource's `create()`, `edit()`, and table actions must call a domain Action, not contain business logic themselves. If you find yourself writing more than 10 lines of non-UI logic inside a resource, extract it to an Action.

**R-F2: Filament resources must never query cross-tenant data.**
Always scope list queries to the current workspace. If you are adding a new resource and are unsure, check the `getEloquentQuery()` method.

**R-F3: Form schemas should use DTOs as their data shape.**
When a Filament form creates or updates a domain object, it should build a DTO and pass it to an Action, not spread form state across the action call.

---

## Domain Rules

**R-D1: Actions contain application-level orchestration only.**
An Action coordinates: check feature flag, validate inputs, call services, dispatch jobs, return result. It should not contain complex business logic — that belongs in a domain Service.

**R-D2: Services contain business logic only.**
Services do not know about HTTP, queues, or Filament. They receive domain objects and return domain objects.

**R-D3: DTOs are immutable.**
All DTO properties are `readonly`. DTOs are never mutated after construction.

**R-D4: Enums are the source of truth for status values.**
Do not use raw strings for statuses, event types, or identifiers that have a fixed set of values. Define an enum and use it everywhere.

**R-D5: No direct cross-domain imports.**
`EmailMarketing` may import from `Communications/Shared/` and `Communications/Contacts/`. It must not import from `Core/` directly except for base traits and contracts. `Core` must never import from `Communications`.

---

## Module Isolation Rules

**R-M1: SMS must be a separate module when built.**
Do not add SMS-specific fields or logic into `EmailMarketing` or `Contacts` when the time comes. Create `Communications/Sms/` as a sibling module.

**R-M2: Booking must be a top-level domain when built.**
`Domains/Booking/` is a sibling to `Domains/Communications/`, not nested inside it.

**R-M3: Contacts are channel-agnostic.**
`Contact` is a person. `ContactIdentity` is how you reach them. `IdentityType::Email` is used today. Do not model contacts as email-only.

---

## Code Quality Rules

**R-Q1: No logic in controllers.**
HTTP controllers (webhook handlers, API endpoints) validate the request, dispatch a Job or call an Action, and return a response. Nothing more.

**R-Q2: No raw SQL unless absolutely necessary.**
Use Eloquent. If you need a complex query, extract it to a dedicated query class or a named scope, not inline in an action or service.

**R-Q3: Write tests for tenant boundaries.**
Every domain Action that reads or writes tenant data needs a test asserting it does not touch another workspace's data.

**R-Q4: Name things for what they do, not how they work.**
`DispatchCampaignAction` not `CampaignSender`. `RecordMessageEventAction` not `EventLogger`. The name should describe the intent.

**R-Q5: Static analysis must pass.**
Run `php artisan larastan:analyse` before pushing. Do not use `@phpstan-ignore` unless there is a documented reason.

---

## Adding a New Feature Checklist

When adding any new tenant-owned feature:

- [ ] Model has `workspace_id` and uses `BelongsToWorkspace`
- [ ] Migration has foreign key constraint on `workspace_id`
- [ ] Action receives `Workspace` as explicit parameter
- [ ] Job uses `HasTenantContext` if it touches tenant data
- [ ] Feature flag check in Action if the feature is gated
- [ ] Filament resource scopes to current workspace
- [ ] Tests cover tenant isolation
- [ ] Static analysis passes
