# Provider Integration Strategy

## The Core Problem

Email providers have wildly different capabilities:
- SMTP is a transport protocol — it delivers a message and tells you success or failure
- Brevo is an API platform — it delivers, tracks opens/clicks, reports bounces, fires webhooks

If campaign code talks directly to providers, adding a new provider requires changing campaign code. That is wrong. Instead, all provider logic is hidden behind a shared contract.

---

## The Contract

```php
// app/Domains/Communications/Shared/Contracts/EmailProviderContract.php

interface EmailProviderContract
{
    /**
     * Send a single email message.
     * Returns a result regardless of success/failure — never throws for delivery failures.
     */
    public function send(SendMessageData $data, array $config): ProviderSendResult;

    /**
     * Validate that the given config connects successfully.
     * Used when saving a ProviderConnection.
     */
    public function verify(array $config): bool;

    /**
     * Human-readable provider name.
     */
    public function name(): string;

    /**
     * Which capabilities this provider supports.
     */
    public function capabilities(): ProviderCapabilities;
}
```

### `SendMessageData` DTO

Provider-agnostic input:

```php
class SendMessageData
{
    public function __construct(
        public readonly string $fromName,
        public readonly string $fromEmail,
        public readonly string $toEmail,
        public readonly string $toName,
        public readonly string $subject,
        public readonly string $htmlBody,
        public readonly ?string $textBody,
        public readonly ?string $replyTo,
        public readonly array  $headers = [],
        public readonly array  $tags    = [],    // for providers that support it
    ) {}
}
```

### `ProviderSendResult` DTO

Provider-agnostic output:

```php
class ProviderSendResult
{
    public function __construct(
        public readonly bool    $success,
        public readonly ?string $providerMessageId,
        public readonly ?string $errorCode,
        public readonly ?string $errorMessage,
    ) {}
}
```

### `ProviderCapabilities`

Expresses what a provider can do, so the rest of the system can behave accordingly:

```php
class ProviderCapabilities
{
    public function __construct(
        public readonly bool $supportsWebhooks      = false,
        public readonly bool $supportsOpenTracking  = false,
        public readonly bool $supportsClickTracking = false,
        public readonly bool $supportsBounceReports = false,
        public readonly bool $supportsTagging       = false,
    ) {}
}
```

SMTP returns `ProviderCapabilities` with all `false`. Brevo returns `true` for all relevant fields.

---

## Provider Registry

Providers are registered in a service provider and resolved by name:

```php
// app/Providers/EmailProviderServiceProvider.php

$this->app->singleton(EmailProviderRegistry::class, function () {
    $registry = new EmailProviderRegistry();
    $registry->register('brevo', new BrevoEmailProvider(...));
    $registry->register('smtp',  new SmtpEmailProvider(...));
    return $registry;
});
```

`EmailDeliveryService` calls `$registry->resolve($connection->provider)` to get the right adapter.

---

## SMTP Adapter

Location: `app/Integrations/Email/Smtp/`

### Responsibilities
- Build a Symfony Mailer transport from the `config` array (host, port, username, password, encryption)
- Send the message using `SymfonyMailer::send()`
- Return a `ProviderSendResult` with success/failure

### Limitations
- No tracking events after delivery
- No webhook support
- No message IDs usable for event correlation
- Verification = attempt a connection to the SMTP server

### What It Must Not Do
- Hard-code any credentials
- Import anything from `Communications/` or `Domains/`
- Know what a campaign is

---

## Brevo Adapter

Location: `app/Integrations/Email/Brevo/`

### Components

**`BrevoApiClient`** — wraps HTTP calls to the Brevo Transactional Email API. Handles auth headers, base URL, retries, and error parsing. Returns raw arrays or throws `BrevoApiException`.

**`BrevoEmailProvider`** — implements `EmailProviderContract`. Translates `SendMessageData` into a Brevo-specific payload using `BrevoSendPayload` DTO, calls `BrevoApiClient`, maps the response to `ProviderSendResult`.

**`BrevoWebhookProcessor`** — parses the raw webhook payload from Brevo into a `BrevoWebhookEvent` DTO. Validates the event structure. Does not write to the database.

**`BrevoEventMapper`** — maps `BrevoWebhookEvent` to a domain `MessageEvent`. The only place where Brevo event type strings are translated to `MessageStatus` enum values.

### Webhook Payload Handling

```
Raw JSON from Brevo
  → BrevoWebhookProcessor::parse()  → BrevoWebhookEvent DTO
  → BrevoEventMapper::toMessageEvent()  → MessageEvent data array
  → RecordMessageEventAction::execute()  → persisted MessageEvent
```

The domain `RecordMessageEventAction` does not know it received data from Brevo. It only receives a `MessageEvent`-shaped value.

---

## Adding a New Provider

To add Mailgun or Amazon SES:

1. Create `app/Integrations/Email/Mailgun/` (or `Ses/`)
2. Implement `EmailProviderContract`
3. Create provider-specific DTOs and mappers inside that folder
4. Register the provider in `EmailProviderServiceProvider`
5. Add the provider key to the `ProviderConnection` enum/config
6. Add a Filament form schema for that provider's config fields
7. Write adapter tests using HTTP fakes

No changes to campaign, delivery, or tracking code are needed.

---

## Config Storage

Provider config (credentials, host, API keys) is stored in the `ProviderConnection.config` JSON column, encrypted at rest using Laravel's `encrypted` cast.

The shape of `config` is defined per adapter:

```php
// Brevo config shape
[
    'api_key' => '...',
]

// SMTP config shape
[
    'host'       => 'smtp.gmail.com',
    'port'       => 587,
    'username'   => 'user@example.com',
    'password'   => '...',
    'encryption' => 'tls',
]
```

Adapters cast and validate their own config. The domain layer never reads raw config keys.

---

## What Must Never Happen

- Campaign code must never import `BrevoApiClient` or `SmtpTransportFactory` directly
- Provider-specific event type strings (e.g. Brevo's `"hard_bounce"`) must never appear outside `Integrations/`
- `SendMessageData` must never contain a provider-specific field
- `ProviderSendResult` must never contain a provider-specific field
- Webhook routes must not be protected by CSRF middleware, but must validate provider signatures
