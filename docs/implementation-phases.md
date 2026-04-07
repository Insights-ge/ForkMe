# Implementation Phases

## Overview

The build is split into five sequential phases. Each phase produces a working, deployable state. Do not begin Phase N+1 until Phase N is stable and reviewed.

---

## Phase 1 — Foundation

**Goal:** A working multi-tenant platform with user management, workspaces, memberships, and feature flags. No email marketing yet.

### Migrations

```
workspaces
  - id, name, slug, plan_id, settings (JSON), created_at, updated_at

workspace_members
  - id, workspace_id, user_id, role (enum), joined_at

plans
  - id, name, slug, features (JSON), entitlements (JSON), is_active

workspace_feature_overrides
  - id, workspace_id, feature (string), value (string)
```

Update existing `users` table if needed (no workspace-specific fields on users — memberships handle the relationship).

### Models

- `Workspace` — with `BelongsToMany` users through `WorkspaceMember`
- `WorkspaceMember` — pivot with `role` column
- `Plan` — simple model, no complex logic
- Update `User` — add workspace relationship

### Services / Actions

- `CreateWorkspaceAction` — creates workspace + default feature flags from plan
- `InviteMemberAction` — adds a user to a workspace
- `FeatureFlagService` — resolves feature access from plan + overrides

### Traits

- `BelongsToWorkspace` — `workspace()` relation + `forWorkspace()` scope
- `HasTenantContext` — for jobs (scaffold only, used in later phases)

### Filament

- Workspace management panel (or super-admin panel)
- User list
- Workspace list with plan assignment
- Feature flag override management per workspace

### Tests

- Workspace creation sets correct defaults
- Feature flag resolution: plan default, workspace override, disabled feature
- Tenant boundary: `forWorkspace()` returns only correct workspace's data
- Member role is correctly stored and readable

---

## Phase 2 — Contacts

**Goal:** A working contact system — channel-agnostic contacts, email identities, lists, tags, CSV import, basic segments.

### Migrations

```
contacts
  - id, workspace_id, first_name, last_name, status (enum), created_at, updated_at

contact_identities
  - id, contact_id, workspace_id, type (email|phone), value, is_primary, verified_at

contact_lists
  - id, workspace_id, name, description

contact_list_members
  - id, contact_list_id, contact_id, subscribed_at

contact_tags
  - id, workspace_id, name, color

contact_tag_assignments
  - id, contact_id, contact_tag_id

segments
  - id, workspace_id, name, conditions (JSON), last_evaluated_at
```

### Models

- `Contact`, `ContactIdentity`, `ContactList`, `ContactTag`, `Segment`
- All use `BelongsToWorkspace`

### Actions / Services

- `CreateContactAction`
- `ImportContactsAction` (dispatches job)
- `SegmentQueryBuilder` — evaluates segment conditions into a query
- `ImportContactsCsvJob`

### Filament

- `ContactResource` — list, view, edit
- `ContactListResource` — list management, member count
- `ContactTagResource`
- CSV import page (using Filament's built-in import infrastructure)
- Segment builder (basic — name + tag/list filters)

### Tests

- CSV import creates correct contacts + identities
- Import is scoped to the correct workspace
- Segment query returns correct contacts
- Contacts from other workspaces are not returned

---

## Phase 3 — Email Marketing Core

**Goal:** Templates, campaigns, provider connections. Test sends working. No tracking yet.

### Migrations

```
provider_connections
  - id, workspace_id, provider (string), label, config (encrypted JSON), is_active, verified_at

email_templates
  - id, workspace_id, name, description, current_version_id

email_template_versions
  - id, email_template_id, subject, html_body, text_body, created_at, published_at

email_campaigns
  - id, workspace_id, name, email_template_version_id, from_name, from_email, reply_to
  - provider_connection_id, audience_type, audience_id
  - status (enum), scheduled_at, sent_at, stats_cache (JSON)

email_campaign_recipients
  - id, email_campaign_id, contact_id, contact_identity_id, outbound_message_id

outbound_messages
  - id, workspace_id, email_campaign_id (nullable), contact_id, contact_identity_id
  - status (enum), provider_connection_id, sent_at

provider_message_attempts
  - id, outbound_message_id, provider, provider_message_id
  - request_payload (JSON), response_payload (JSON), status, attempted_at
```

### Models

- `ProviderConnection`, `EmailTemplate`, `EmailTemplateVersion`
- `EmailCampaign`, `EmailCampaignRecipient`
- `OutboundMessage`, `ProviderMessageAttempt`

### Contracts / Interfaces

- `EmailProviderContract`
- `ProviderCapabilities`

### Integration Adapters

- `SmtpEmailProvider` + `SmtpTransportFactory`
- `BrevoEmailProvider` + `BrevoApiClient`
- `EmailProviderRegistry`

### Actions / Services

- `SaveProviderConnectionAction` (with `verify()` call)
- `CreateEmailTemplateAction`, `PublishTemplateVersionAction`
- `CreateCampaignAction`
- `SendTestEmailAction`
- `DispatchCampaignAction`
- `ScheduleCampaignAction`
- `EmailDeliveryService`

### Jobs

- `SendCampaignBatchJob`
- `SendSingleEmailJob`

### Filament

- `ProviderConnectionResource`
- `EmailTemplateResource` (with version history)
- `EmailCampaignResource`
- `SendCampaignPage` (wizard: audience → template → provider → send)

### Tests

- Test send dispatches correct payload to adapter
- SMTP adapter builds correct transport from config
- Brevo adapter builds correct API payload from `SendMessageData`
- Campaign dispatch scopes recipients to correct workspace
- Suppressed address is skipped (scaffold — suppression table empty at this point)

---

## Phase 4 — Tracking, Suppression, Webhooks

**Goal:** Full event lifecycle. Bounces, opens, clicks, unsubscribes handled. Suppressions enforced before send.

### Migrations

```
message_events
  - id, workspace_id, outbound_message_id, event_type (enum)
  - provider, provider_event_id, occurred_at, metadata (JSON)

suppression_entries
  - id, workspace_id, email_address, reason (enum), suppressed_at

unsubscribe_entries
  - id, workspace_id, contact_id, contact_identity_id, unsubscribed_at, source (enum)
```

### Models

- `MessageEvent`, `SuppressionEntry`, `UnsubscribeEntry`

### Webhook Infrastructure

- `BrevoWebhookProcessor`
- `BrevoEventMapper`
- `BrevoWebhookHandler` (controller)
- `ProcessProviderWebhookJob`
- Webhook route in `routes/webhooks.php`

### Actions

- `RecordMessageEventAction`
- `SuppressContactAction`
- Update `SendSingleEmailJob` to check suppressions

### Filament

- `SuppressionResource` — view and manually add suppressions
- Campaign analytics page — open/click/bounce stats
- `CampaignStatsWidget`

### Tests

- `BrevoWebhookProcessor` parses all known event types from fixture JSON
- `BrevoEventMapper` maps all known Brevo event strings correctly
- Suppressed address is skipped on send
- Bounce event creates suppression entry
- Complaint event creates suppression entry
- Unsubscribe link records `UnsubscribeEntry`

---

## Phase 5 — Filament Polish

**Goal:** Production-ready admin UI. Complete flows, error states, permissions.

- Filament Shield integration for role-based access within a workspace
- Campaign status badges, delivery stats visible in list views
- Error states: failed provider connections, failed sends
- Scheduled campaign management (list, cancel, reschedule)
- Import history and failed row reporting
- Audit log viewer (workspace activity)
- Settings pages for workspace profile and notification preferences

---

## What Is Explicitly Postponed

| Feature | Reason |
|---|---|
| SMS module | Architecture is ready; implementation deferred |
| Booking module | Architecture is ready; implementation deferred |
| Mailgun / SES adapters | Add as needed after Phase 3 pattern is proven |
| API for external access | Not needed for V1 admin UI |
| Advanced segment conditions | Start with list/tag filters; complex boolean later |
| Email editor (drag/drop) | Use raw HTML in V1; integrate editor separately |
| Sending domain management | Useful later; not blocking V1 |
