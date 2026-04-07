# Email Marketing Module

## Overview

`Communications/EmailMarketing` is the first production module in the platform. It handles the full lifecycle of email campaigns — from template creation through sending to event tracking and suppression.

It does not contain provider-specific logic. Provider adapters live in `Integrations/Email/`.

---

## Sub-Domains

### Templates

An `EmailTemplate` is a reusable design. Each time it is edited and published, a new `EmailTemplateVersion` is created. Campaigns reference a specific version, so editing a template does not break in-flight campaigns.

```
EmailTemplate
├── id
├── workspace_id
├── name
├── description
└── current_version_id (FK → EmailTemplateVersion)

EmailTemplateVersion
├── id
├── email_template_id
├── subject
├── html_body
├── text_body
├── created_at
└── published_at
```

### Campaigns

An `EmailCampaign` ties together a template version, an audience (list or segment), a provider connection, and a send schedule.

```
EmailCampaign
├── id
├── workspace_id
├── name
├── email_template_version_id
├── from_name
├── from_email
├── reply_to
├── provider_connection_id
├── audience_type (list | segment)
├── audience_id
├── status (draft | scheduled | sending | sent | cancelled)
├── scheduled_at
├── sent_at
└── stats_cache (JSON)           ← denormalized counts for quick display
```

Recipients are expanded at send time, not at creation. This avoids stale snapshots for scheduled campaigns.

```
EmailCampaignRecipient
├── id
├── email_campaign_id
├── contact_id
├── contact_identity_id          ← which email address was targeted
└── outbound_message_id          ← link to delivery record
```

### Delivery

`OutboundMessage` is the central delivery record for a single email send attempt. It is provider-agnostic.

```
OutboundMessage
├── id
├── workspace_id
├── email_campaign_id (nullable) ← null for test sends
├── contact_id
├── contact_identity_id
├── status (pending | sent | delivered | bounced | failed | suppressed)
├── provider_connection_id
└── sent_at

ProviderMessageAttempt
├── id
├── outbound_message_id
├── provider (brevo | smtp)
├── provider_message_id          ← ID returned by the provider
├── request_payload (JSON)       ← what we sent
├── response_payload (JSON)      ← what the provider returned
├── status
└── attempted_at
```

### Tracking

`MessageEvent` records events that happen after delivery — opens, clicks, bounces, etc.

```
MessageEvent
├── id
├── workspace_id
├── outbound_message_id
├── event_type (sent | delivered | opened | clicked | bounced | complained | unsubscribed)
├── provider (brevo | smtp)
├── provider_event_id
├── occurred_at
└── metadata (JSON)              ← click URL, user agent, etc.
```

SMTP does not generate tracking events beyond send success/failure. Events come from Brevo webhooks for API-based sends.

### Providers

`ProviderConnection` stores the configuration needed to send via a specific provider for a workspace.

```
ProviderConnection
├── id
├── workspace_id
├── provider (brevo | smtp | ...)
├── label                        ← user-visible name, e.g. "Company Gmail"
├── config (encrypted JSON)      ← credentials, host, port, etc.
├── is_active
└── verified_at
```

Credentials are always stored encrypted. The `config` column shape is provider-specific but never read outside the provider adapter.

### Suppression and Unsubscribes

```
SuppressionEntry
├── id
├── workspace_id
├── email_address
├── reason (bounced | complained | manual)
└── suppressed_at

UnsubscribeEntry
├── id
├── workspace_id
├── contact_id
├── contact_identity_id
├── unsubscribed_at
└── source (link | api | import)
```

Before sending any email, the delivery service checks both tables. Suppressed or unsubscribed addresses are skipped and recorded as `suppressed` on the `OutboundMessage`.

---

## Campaign Send Flow

### Test Send

```
SendTestEmailAction
  → validate recipient address
  → resolve ProviderConnection
  → build SendMessageData DTO
  → EmailDeliveryService::send()
  → record OutboundMessage (no campaign_id)
```

### Immediate Send

```
DispatchCampaignAction
  → check FeatureFlag: email_marketing
  → set campaign status = sending
  → chunk recipients (list/segment)
  → dispatch SendCampaignBatchJob per chunk

SendCampaignBatchJob (queued, carries workspace_id)
  → for each recipient:
      → check suppression
      → dispatch SendSingleEmailJob

SendSingleEmailJob (queued)
  → resolve contact + identity
  → check suppression again (race-safe)
  → build SendMessageData DTO
  → EmailDeliveryService::send()
  → record OutboundMessage + ProviderMessageAttempt
```

### Scheduled Send

```
ScheduleCampaignAction
  → validate scheduled_at is in the future
  → set campaign status = scheduled
  → store scheduled_at

Scheduler (cron, every minute)
  → find campaigns where status = scheduled AND scheduled_at <= now
  → call DispatchCampaignAction for each
```

---

## EmailDeliveryService

This is the single entry point for sending an email. It resolves the correct provider adapter and delegates.

```php
class EmailDeliveryService
{
    public function send(SendMessageData $data, ProviderConnection $connection): ProviderSendResult
    {
        $provider = $this->providerRegistry->resolve($connection->provider);
        return $provider->send($data, $connection->config);
    }
}
```

It does not know about Brevo or SMTP. It only knows `EmailProviderContract`.

---

## Webhook Handling

Brevo sends events to a registered webhook URL. The flow:

```
POST /webhooks/brevo
  → BrevoWebhookController (no CSRF)
  → validate signature
  → dispatch ProcessProviderWebhookJob

ProcessProviderWebhookJob
  → BrevoWebhookProcessor::parse()  ← returns BrevoWebhookEvent DTO
  → BrevoEventMapper::toMessageEvent() ← maps to MessageEvent
  → RecordMessageEventAction::execute()
  → update OutboundMessage status
  → handle suppression if bounce/complaint
```

SMTP does not have webhooks. SMTP-sent campaigns only have sent/failed status.

---

## Filament Resources (Email Marketing)

| Resource | Covers |
|---|---|
| `EmailTemplateResource` | Create, edit, version templates |
| `EmailCampaignResource` | Create, configure, review campaigns |
| `ProviderConnectionResource` | Add and verify provider configs |
| `ContactResource` | View, search, filter contacts |
| `SuppressionResource` | View and manually add suppressions |

Each resource is thin — it calls Actions for any state-changing operation.

Custom pages:
- `SendCampaignPage` — step-through wizard for dispatching a campaign
- `CampaignAnalyticsPage` — open/click/bounce stats for a campaign
