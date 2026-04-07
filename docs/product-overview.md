# Product Overview

## What This System Is

This is a **multi-tenant modular SaaS platform** built with Laravel and Filament. It is designed to grow over time, starting with email marketing and expanding into other communication channels and business tools without requiring architectural rewrites.

The platform is not an email marketing tool. It is a platform that *includes* email marketing as its first module.

---

## Current State (V1 Scope)

### What We Are Building Now

**Core Platform**
- Multi-tenant workspace foundation
- User accounts and team memberships
- Tenant-scoped feature flags and plan entitlements
- Shared contact model (channel-agnostic)

**Communications Module — Email Marketing**
- Contact management (contacts + identities)
- Contact lists and tags
- CSV import
- Basic audience segments
- Email templates with versioning
- Campaigns (test send, immediate send, scheduled send)
- Provider integrations: Gmail SMTP, Brevo
- Delivery tracking and event recording
- Suppression and unsubscribe handling
- Filament admin UI for all of the above

---

## Module Map

```
Platform
├── Core                          ← foundation, tenancy, users, feature flags
└── Communications
    ├── Contacts                  ← channel-agnostic contact model
    ├── EmailMarketing            ← campaigns, templates, delivery, tracking
    └── Shared                    ← contracts, DTOs, enums shared within Communications
```

Modules planned but **not built in V1**:

| Module | Status | Notes |
|---|---|---|
| Communications / SMS | Reserved | Architecture must not block it |
| Booking | Reserved | Top-level module, not inside Communications |

---

## Why This Structure

### Why "Communications" as a Container

Email marketing and SMS are both communication channels. They share concepts: contacts, suppression, delivery events, opt-out handling. Grouping them under a `Communications` top-level domain lets us share these concepts without forcing premature abstractions.

When SMS is added, it lives alongside `EmailMarketing` inside `Communications`, not in a separate unrelated tree.

### Why Contacts Are Channel-Agnostic

A contact is a person, not an email address. Modeling contacts with a separate `contact_identities` table means:
- Today: one identity per contact (email)
- Later: the same contact can have an email identity *and* a phone identity
- No model rewrite needed when SMS is added

### Why Booking Is Separate

Booking is not a communication channel. It is a distinct business domain. When built, it will be a top-level module alongside `Communications`, not nested inside it.

---

## Tenancy Model

Every workspace is a **tenant**. All data owned by a workspace is scoped by `workspace_id`. There is no data shared between workspaces (except system-level config like plans).

This is a **single-database multi-tenant** architecture. Each workspace gets rows in shared tables, not separate database schemas or instances.

---

## Feature Flags and Plans

Access to modules and capabilities is controlled by a layered system:

1. **Plan defaults** — what a plan includes by default
2. **Workspace overrides** — per-workspace toggles on top of the plan
3. **User permissions** — fine-grained permission inside a workspace

Feature access is not only on/off. Some features carry limits:
- `max_contacts`
- `max_monthly_emails`
- `max_team_members`

---

## Email Provider Strategy

The platform supports pluggable email providers. Each provider is an adapter behind a shared contract. Business logic never talks to Brevo or SMTP directly.

V1 providers:
- **Gmail SMTP** — basic transport, limited capability
- **Brevo** — API-based, supports richer tracking and webhooks

The architecture is designed so adding Mailgun, Amazon SES, or others later requires only a new adapter — no changes to campaign logic.

---

## Non-Goals for V1

- No SMS implementation
- No booking module
- No omnichannel abstractions
- No database-per-tenant
- No microservices
- No giant generic message bus
