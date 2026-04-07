# Folder Structure

## Top-Level Map

```
app/
├── Domains/
│   ├── Core/
│   │   ├── Tenancy/
│   │   ├── Users/
│   │   ├── Memberships/
│   │   ├── Plans/
│   │   ├── FeatureFlags/
│   │   ├── Settings/
│   │   ├── Audit/
│   │   └── Shared/
│   │
│   └── Communications/
│       ├── Contacts/
│       ├── EmailMarketing/
│       └── Shared/
│
├── Integrations/
│   └── Email/
│       ├── Brevo/
│       └── Smtp/
│
├── Filament/              ← Filament panels, resources, pages, widgets
│   └── App/
│       ├── Resources/
│       ├── Pages/
│       └── Widgets/
│
├── Http/                  ← only for webhooks, API endpoints, web routes
├── Console/               ← artisan commands
├── Jobs/                  ← queued jobs (must carry tenant context)
└── Providers/             ← service providers
```

---

## Core Domain

### `Core/Tenancy/`
Workspace/tenant model, tenant resolution, middleware, and helpers to get the current workspace in application code.

```
Core/Tenancy/
├── Models/
│   └── Workspace.php
├── Services/
│   └── TenantResolver.php
├── Actions/
│   └── CreateWorkspaceAction.php
├── DTOs/
│   └── WorkspaceData.php
└── Concerns/
    └── BelongsToWorkspace.php      ← trait for tenant-scoped models
```

### `Core/Users/`
User model, profile, password reset. Does not contain workspace membership logic.

### `Core/Memberships/`
The join between users and workspaces. Handles invitations, roles within a workspace, and membership lifecycle.

```
Core/Memberships/
├── Models/
│   └── WorkspaceMember.php
├── Actions/
│   ├── InviteMemberAction.php
│   └── RemoveMemberAction.php
└── Enums/
    └── MemberRole.php
```

### `Core/Plans/`
Plan definitions and their default feature sets. Plans are system-level records, not per-workspace.

### `Core/FeatureFlags/`
Feature flag and entitlement system. See [multi-tenancy-and-feature-flags.md](multi-tenancy-and-feature-flags.md) for full detail.

```
Core/FeatureFlags/
├── Models/
│   ├── FeatureFlag.php
│   └── WorkspaceFeatureOverride.php
├── Services/
│   └── FeatureFlagService.php
├── Enums/
│   └── Feature.php
└── DTOs/
    └── FeatureAccess.php
```

### `Core/Settings/`
Workspace-level and system-level settings. Uses a typed settings approach — each settings group is a class.

### `Core/Audit/`
Audit log for important workspace actions. Not a full event sourcing system — just a record of what happened and who did it.

### `Core/Shared/`
Base classes, interfaces, and traits used across Core sub-domains only.

---

## Communications Domain

### `Communications/Contacts/`
The shared contact model used by all communication channels.

```
Communications/Contacts/
├── Models/
│   ├── Contact.php
│   ├── ContactIdentity.php       ← email, phone, etc.
│   ├── ContactList.php
│   ├── ContactTag.php
│   └── Segment.php
├── Actions/
│   ├── CreateContactAction.php
│   ├── ImportContactsAction.php
│   └── SyncContactListAction.php
├── Services/
│   └── SegmentQueryBuilder.php
├── DTOs/
│   ├── ContactData.php
│   └── ImportRowData.php
├── Imports/
│   └── ContactCsvImporter.php
└── Enums/
    ├── IdentityType.php           ← email, phone
    └── ContactStatus.php
```

### `Communications/EmailMarketing/`
Everything specific to email campaigns.

```
Communications/EmailMarketing/
├── Campaigns/
│   ├── Models/
│   │   ├── EmailCampaign.php
│   │   └── EmailCampaignRecipient.php
│   ├── Actions/
│   │   ├── CreateCampaignAction.php
│   │   ├── SendTestEmailAction.php
│   │   ├── DispatchCampaignAction.php
│   │   └── ScheduleCampaignAction.php
│   └── Enums/
│       └── CampaignStatus.php
│
├── Templates/
│   ├── Models/
│   │   ├── EmailTemplate.php
│   │   └── EmailTemplateVersion.php
│   └── Actions/
│       └── CreateTemplateVersionAction.php
│
├── Delivery/
│   ├── Models/
│   │   ├── OutboundMessage.php
│   │   └── ProviderMessageAttempt.php
│   ├── Jobs/
│   │   ├── SendCampaignBatchJob.php
│   │   └── SendSingleEmailJob.php
│   └── Services/
│       └── EmailDeliveryService.php
│
├── Tracking/
│   ├── Models/
│   │   └── MessageEvent.php
│   ├── Actions/
│   │   └── RecordMessageEventAction.php
│   └── Webhooks/
│       └── BrevoWebhookHandler.php
│
├── Providers/
│   ├── Models/
│   │   └── ProviderConnection.php
│   └── Actions/
│       └── SaveProviderConnectionAction.php
│
└── Support/
    ├── Models/
    │   ├── SuppressionEntry.php
    │   └── UnsubscribeEntry.php
    └── Actions/
        └── SuppressContactAction.php
```

### `Communications/Shared/`
Contracts, DTOs, and enums shared between `Contacts`, `EmailMarketing`, and future SMS.

```
Communications/Shared/
├── Contracts/
│   ├── EmailProviderContract.php
│   └── SuppressibleContract.php
├── DTOs/
│   ├── SendMessageData.php
│   └── ProviderSendResult.php
└── Enums/
    ├── MessageStatus.php
    └── ChannelType.php            ← email, sms — for future use
```

---

## Integrations

Provider-specific code. Nothing outside this directory knows about Brevo or SMTP implementation details.

### `Integrations/Email/Brevo/`

```
Integrations/Email/Brevo/
├── BrevoEmailProvider.php         ← implements EmailProviderContract
├── BrevoApiClient.php             ← HTTP client wrapper
├── BrevoWebhookProcessor.php      ← parse incoming webhook payloads
├── DTOs/
│   ├── BrevoSendPayload.php
│   └── BrevoWebhookEvent.php
└── Mappers/
    └── BrevoEventMapper.php       ← maps Brevo events → MessageEvent
```

### `Integrations/Email/Smtp/`

```
Integrations/Email/Smtp/
├── SmtpEmailProvider.php          ← implements EmailProviderContract
├── SmtpTransportFactory.php       ← builds configured Symfony Mailer transport
└── DTOs/
    └── SmtpSendResult.php
```

---

## Filament

Thin UI layer. Resources call Actions; they do not contain business logic.

```
app/Filament/App/
├── Resources/
│   ├── WorkspaceResource.php
│   ├── ContactResource.php
│   ├── EmailTemplateResource.php
│   ├── EmailCampaignResource.php
│   └── ProviderConnectionResource.php
├── Pages/
│   ├── CampaignAnalyticsPage.php
│   └── SendCampaignPage.php
└── Widgets/
    └── CampaignStatsWidget.php
```

---

## Jobs

All queued jobs must carry and restore tenant context.

```
app/Jobs/
├── Concerns/
│   └── HasTenantContext.php       ← trait: serialize/restore workspace_id
├── SendCampaignBatchJob.php
├── ProcessProviderWebhookJob.php
└── ImportContactsCsvJob.php
```

---

## Routes

```
routes/
├── web.php          ← Filament panel routes (minimal)
├── api.php          ← not used in V1 unless needed
└── webhooks.php     ← provider webhook endpoints (no CSRF)
```

---

## Naming Conventions

| Type | Convention | Example |
|---|---|---|
| Model | `PascalCase` singular | `EmailCampaign` |
| Action | `VerbNounAction` | `DispatchCampaignAction` |
| Service | `NounService` | `EmailDeliveryService` |
| DTO | `NounData` | `ContactData` |
| Enum | `PascalCase` | `CampaignStatus` |
| Job | `VerbNounJob` | `SendCampaignBatchJob` |
| Contract/Interface | `NounContract` | `EmailProviderContract` |
| Mapper | `SourceTargetMapper` | `BrevoEventMapper` |
