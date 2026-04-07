# Old Integrations Migration Plan

## Principle

Do not paste old code into the new project. Old projects are **reference material**, not source of truth for architecture.

The job is to:
1. Understand what the old code does
2. Extract only the valuable logic (API calls, payload shapes, event mappings)
3. Rewrite it to fit the new integration pattern
4. Discard bad naming, bad structure, tightly coupled logic

---

## Old Gmail SMTP Integration

### What Typically Exists in an Old SMTP Integration

A typical old SMTP integration in a Laravel project contains some mix of:
- `config/mail.php` with hardcoded or `.env`-driven Gmail SMTP settings
- A `Mailable` class (or several) with message building logic embedded
- A service class or helper that calls `Mail::to()->send()`
- Possibly queue configuration for mail

### What Is Reusable

| Component | Notes |
|---|---|
| SMTP connection params (host, port, encryption) | Reuse the values, not the config placement |
| Mailable HTML structure / templates | Extract as HTML, rebuild as `EmailTemplate` records |
| Working logic for building a `Message` object | Useful reference for SMTP adapter |

### What to Discard

- Any `Mailable` class that mixes template logic with sending logic
- Any service that calls `Mail::send()` without abstraction
- Any hardcoded "from" address
- Config that only lives in `config/mail.php` without workspace-level override support

### Rewrite Tasks

1. Create `SmtpEmailProvider` implementing `EmailProviderContract`
2. Create `SmtpTransportFactory` — takes a `config` array, builds a Symfony Mailer transport
3. Create `SmtpSendResult` DTO
4. Per-workspace SMTP config is stored in `ProviderConnection.config`, not in `.env` or `config/mail.php`
5. Verification: attempt to connect and send a test message, catch `TransportException`

### Target Location

```
app/Integrations/Email/Smtp/
├── SmtpEmailProvider.php
├── SmtpTransportFactory.php
└── DTOs/
    └── SmtpSendResult.php
```

### Migration Notes

- The old project may use Laravel's built-in `Mail` facade. In the new project, use Symfony Mailer directly inside the adapter to avoid Laravel mail config coupling.
- Gmail SMTP requires an App Password, not the account password. This must be documented in the Filament provider config form.
- Per-connection `from` address is stored on `ProviderConnection`, not derived from global config.

---

## Old Brevo Integration

### What Typically Exists in an Old Brevo Integration

A typical Brevo integration contains:
- An API client class wrapping Guzzle or the official Brevo PHP SDK
- A service class calling `sendTransacEmail` or similar
- Possibly a webhook controller
- Possibly event/status mapping logic

### What Is Reusable

| Component | Notes |
|---|---|
| HTTP API call structure (endpoint, headers, payload shape) | Reuse payload structure, not the client class |
| Brevo event type strings | Valuable reference for event mapper |
| Webhook payload structure | Good reference for `BrevoWebhookProcessor` |
| Error handling patterns (rate limit, auth failure) | Reference for `BrevoApiClient` |

### What to Discard

- Any service that takes a `User` model or app-specific model directly
- Any class that mixes sending logic with template rendering
- Any webhook controller that writes to DB directly without going through an Action
- Any tight coupling to the old app's config or service container

### Rewrite Tasks

1. Create `BrevoApiClient` — clean HTTP wrapper using Laravel `Http` facade (no Guzzle directly, no Brevo SDK dependency)
2. Create `BrevoEmailProvider` implementing `EmailProviderContract`
3. Create `BrevoSendPayload` DTO — maps `SendMessageData` to Brevo API structure
4. Create `BrevoWebhookProcessor` — parses incoming webhook JSON to `BrevoWebhookEvent` DTO
5. Create `BrevoEventMapper` — maps Brevo event strings to domain `MessageStatus` / `MessageEvent`
6. Write tests using `Http::fake()` for API calls and fixture JSON for webhook payloads

### Target Location

```
app/Integrations/Email/Brevo/
├── BrevoEmailProvider.php
├── BrevoApiClient.php
├── BrevoWebhookProcessor.php
├── DTOs/
│   ├── BrevoSendPayload.php
│   └── BrevoWebhookEvent.php
└── Mappers/
    └── BrevoEventMapper.php
```

### Migration Notes

- The old client may use the official `sendinblue/api-v3-sdk` PHP package. In the new project, use Laravel's `Http` client directly to avoid SDK versioning issues and gain better testability with `Http::fake()`.
- Brevo's transactional email API base URL: `https://api.brevo.com/v3/smtp/email`
- The webhook must validate the Brevo signature header (`X-Sib-Signature`) before processing — extract this from the old controller if it exists.
- Old Brevo integrations often name their events differently. Map them explicitly in `BrevoEventMapper`:

```php
// Known Brevo event type strings → domain MessageStatus
private const EVENT_MAP = [
    'request'         => MessageStatus::Sent,
    'delivered'       => MessageStatus::Delivered,
    'opened'          => MessageStatus::Opened,
    'click'           => MessageStatus::Clicked,
    'hard_bounce'     => MessageStatus::HardBounced,
    'soft_bounce'     => MessageStatus::SoftBounced,
    'spam'            => MessageStatus::Complained,
    'unsubscribed'    => MessageStatus::Unsubscribed,
    'invalid_email'   => MessageStatus::Failed,
    'blocked'         => MessageStatus::Failed,
    'error'           => MessageStatus::Failed,
];
```

---

## Migration Checklist

### For Each Old Integration

- [ ] Read through the old code in full — understand what it does before extracting anything
- [ ] List what is genuinely reusable (API knowledge, payload shapes, event strings)
- [ ] List what must be discarded (coupling, bad naming, mixed concerns)
- [ ] Create the new adapter skeleton in `app/Integrations/Email/`
- [ ] Port only the useful logic, rewritten to match new patterns
- [ ] Write tests for the adapter (Http::fake for API, fixture JSON for webhooks)
- [ ] Verify end-to-end with a real ProviderConnection in a local/staging environment
- [ ] Document any provider-specific quirks in the adapter's class docblock

### Testing Both Adapters

Each adapter should have:
- A test for a successful send (returns `ProviderSendResult` with `success = true`)
- A test for a failed send (API error — returns `ProviderSendResult` with `success = false`, error details set)
- A test for `verify()` — returns `true` when credentials are valid
- For Brevo only: a test for `BrevoWebhookProcessor::parse()` using fixture JSON
- For Brevo only: a test for `BrevoEventMapper` covering all known event types
