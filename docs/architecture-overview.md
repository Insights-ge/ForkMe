# Architecture Overview

## Architectural Style: Multi-Tenant Modular Monolith

### Why a Monolith

This is a greenfield V1. Microservices require operational infrastructure (service discovery, inter-service auth, distributed tracing, independent deployments) that provides no benefit at this scale and creates significant coordination overhead.

A monolith allows:
- Shared database transactions
- Single deployment unit
- Straightforward tenant scoping
- Easier debugging and testing

The code is structured *as if* it could be split later — clear domain boundaries, no circular dependencies between modules — but it ships as one application.

### Why Modular

Putting everything in `app/Http/Controllers` and `app/Models` produces an unmaintainable codebase at scale. Modules:
- Enforce ownership boundaries (who is responsible for what)
- Prevent cross-domain coupling from accumulating silently
- Make future extraction possible if ever needed

### Why Single-Database Multi-Tenancy

Database-per-tenant is appropriate for strict data isolation requirements (e.g. regulatory, enterprise contracts). For a SaaS V1:
- Single database is far simpler to operate and migrate
- Tenant isolation is achieved through consistent `workspace_id` scoping in application code
- Moving to database-per-tenant later is possible, but the reverse is much harder

---

## Top-Level Domain Structure

```
app/
├── Domains/
│   ├── Core/               ← platform-level concerns
│   └── Communications/     ← communication channel modules
│
└── Integrations/
    └── Email/              ← provider adapters (not domain logic)
```

### Core

Everything the platform needs to function regardless of which product modules are enabled.

Sub-domains:
- `Tenancy` — workspace model, tenant resolution
- `Users` — user accounts
- `Memberships` — user ↔ workspace relationships
- `Plans` — plan definitions, defaults
- `FeatureFlags` — per-workspace feature flags and entitlements
- `Settings` — workspace and system settings
- `Audit` — audit log
- `Shared` — cross-domain base classes, traits, contracts used by Core

### Communications

Everything related to sending messages to contacts, regardless of channel.

Sub-domains:
- `Contacts` — contact model, identities, lists, tags, segments, import
- `EmailMarketing` — campaigns, templates, delivery, tracking, suppressions
- `Shared` — contracts, DTOs, enums shared across Communications sub-domains

### Integrations

Provider-specific implementation details. This layer exists so provider code never leaks into domain logic.

Sub-trees:
- `Email/Smtp` — SMTP transport adapter
- `Email/Brevo` — Brevo API adapter

---

## Why Communications Exists as a Domain

`Communications` is not just email. It groups all outbound messaging channels under one domain so that:
- Contacts, suppressions, and opt-out logic are shared concepts
- Provider abstraction lives at the Communications level, not per-channel
- When SMS is added, it is a sibling to `EmailMarketing`, not bolted onto it

---

## Why EmailMarketing Is Inside Communications

`EmailMarketing` is one channel implementation inside the Communications domain. It should not be a top-level domain because:
- It shares `Contacts` with future SMS
- It shares `Shared/Contracts` with future providers
- It is not a standalone business domain; it is a capability of the communications system

---

## Why Booking Is Not Inside Communications

Booking is a separate business domain. It manages availability, scheduling, calendar, appointments — none of which are communication concepts. When built, `Booking` will be a sibling to `Communications` at the top level, under `Domains/`.

---

## Dependency Direction

```
Filament Resources
      ↓
Domain Actions / Services
      ↓
Domain Models / DTOs
      ↓
Integrations (Adapters)
      ↓
External providers / transport
```

Rules:
- Filament resources call Actions, not services directly
- Actions contain application-level orchestration
- Domain services contain business logic
- Integrations contain provider-specific code only
- Nothing above Integrations knows about Brevo or SMTP specifics

---

## Key Packages in Use

| Package | Purpose |
|---|---|
| `filament/filament` v5 | Admin UI framework |
| `spatie/laravel-permission` | Role/permission system |
| `spatie/laravel-medialibrary` | File/media management |
| `bezhansalleh/filament-shield` | Filament permission integration |
| `jeffgreco13/filament-breezy` | Auth UI for Filament |
| `pestphp/pest` | Testing framework |
| `larastan/larastan` | Static analysis |

---

## Testing Strategy

- Feature tests for Actions and Services
- Unit tests for DTOs, Enums, domain logic
- Tenant boundary tests — assert queries are scoped
- Provider adapter tests using HTTP fakes, not live APIs
- Filament resource tests using Pest + Livewire testing helpers
