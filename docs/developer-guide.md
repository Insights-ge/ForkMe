# Developer Guide

Practical instructions for working inside this codebase.

---

## Adding a New Email Provider

1. Create `app/Integrations/Email/{ProviderName}/`
2. Implement `EmailProviderContract` in `{ProviderName}EmailProvider.php`
3. Add a client class for the API/transport
4. Create provider-specific DTOs inside `DTOs/`
5. Create mappers if the provider has webhooks inside `Mappers/`
6. Register the provider in `EmailProviderServiceProvider`:

```php
$registry->register('mailgun', new MailgunEmailProvider(...));
```

7. Add the provider key to `ProviderConnection` validation
8. Add the provider's config form schema to the Filament `ProviderConnectionResource`
9. Write adapter tests using `Http::fake()` (API) or fixture JSON (webhooks)

---

## Adding a New Campaign Feature

Example: adding an A/B subject line test.

1. **Define the data** — add fields to `email_campaigns` migration
2. **Update the DTO** — update `CreateCampaignAction`'s input if needed
3. **Update the Action** — `DispatchCampaignAction` handles variant selection
4. **Update the Job** — `SendSingleEmailJob` picks the correct subject per recipient
5. **Update Filament** — add the field to `EmailCampaignResource` form schema
6. **Write tests** — assert variant selection logic, assert correct subject in `SendMessageData`

Do not add A/B logic into the provider adapter. The adapter only receives a final `SendMessageData`.

---

## Adding a New Filament Resource

1. Create the resource class in `app/Filament/App/Resources/`
2. Scope the list query to the current workspace:

```php
public static function getEloquentQuery(): Builder
{
    return parent::getEloquentQuery()
        ->forWorkspace(filament()->getTenant());
}
```

3. Form schema calls an Action on save — do not write model directly from `mutateFormDataBeforeSave()`
4. Table bulk actions call Actions, not models directly
5. Register the resource in the panel provider if auto-discovery is off

---

## Adding a New Feature Flag

1. Add the case to the `Feature` enum:

```php
enum Feature: string
{
    case EmailMarketing = 'email_marketing';
    case Sms            = 'sms';
    case MyNewFeature   = 'my_new_feature';  // add here
}
```

2. Add the default to the relevant plan's `features` JSON column (via seeder or migration)
3. Gate the feature in the Action:

```php
$this->featureFlags->check(Feature::MyNewFeature, $workspace);
```

4. If the feature has entitlements (limits), add the key to the plan's `entitlements` JSON and query it with:

```php
$limit = $this->featureFlags->entitlementValue('max_my_feature', $workspace);
```

---

## Adding a New Tenant-Aware Model

1. Create the model in the appropriate domain folder
2. Add `workspace_id` to the migration (not nullable, indexed, foreign key)
3. Use the `BelongsToWorkspace` trait:

```php
use App\Domains\Core\Tenancy\Concerns\BelongsToWorkspace;

class MyModel extends Model
{
    use BelongsToWorkspace;
}
```

4. Write a test asserting `forWorkspace()` scope filters correctly

---

## Adding a New Queued Job

1. Create the job in `app/Jobs/` (or inside a module's `Jobs/` folder)
2. Use the `HasTenantContext` trait if the job touches tenant data:

```php
class MyJob implements ShouldQueue
{
    use HasTenantContext;

    public function handle(): void
    {
        $workspace = $this->resolveWorkspace();
        // all queries use $workspace from here
    }
}
```

3. Dispatch with workspace context:

```php
MyJob::dispatch()->forWorkspace($workspace);
```

---

## Adding a New Domain Action

Actions follow a consistent pattern:

```php
class DoSomethingAction
{
    public function __construct(
        private FeatureFlagService $featureFlags,
        private SomeDomainService  $service,
    ) {}

    public function execute(SomethingData $data, Workspace $workspace): SomethingResult
    {
        $this->featureFlags->check(Feature::SomeFeature, $workspace);

        // orchestrate
        $result = $this->service->process($data, $workspace);

        return $result;
    }
}
```

- Constructor injection only (resolved from service container)
- `Workspace` is always an explicit parameter
- Feature flag check at the top if applicable
- Returns a typed result (not `void` unless truly fire-and-forget)

---

## Writing Tenant Boundary Tests

Every Action that reads or writes tenant data needs a boundary test:

```php
it('cannot read another workspace\'s data', function () {
    $workspaceA = Workspace::factory()->create();
    $workspaceB = Workspace::factory()->create();

    MyModel::factory()->for($workspaceA)->count(3)->create();
    MyModel::factory()->for($workspaceB)->count(2)->create();

    $result = app(ListMyModelsAction::class)->execute($workspaceA);

    expect($result)->toHaveCount(3);
    expect($result->pluck('workspace_id')->unique()->toArray())->toBe([$workspaceA->id]);
});
```

---

## Working With Provider Adapters in Tests

Use `Http::fake()` to test Brevo adapter calls without hitting the real API:

```php
Http::fake([
    'api.brevo.com/*' => Http::response(['messageId' => '<abc123@brevo.com>'], 201),
]);

$result = app(BrevoEmailProvider::class)->send($sendData, $config);

expect($result->success)->toBeTrue();
expect($result->providerMessageId)->toBe('<abc123@brevo.com>');
```

For SMTP adapter, use Laravel's `Mail::fake()` or inject a mock transport.

For webhook processing, use a fixture JSON file:

```php
$payload = json_decode(file_get_contents(base_path('tests/fixtures/brevo-webhook-delivered.json')), true);
$event = app(BrevoWebhookProcessor::class)->parse($payload);

expect($event->type)->toBe('delivered');
```

---

## Static Analysis

Run before pushing:

```bash
php artisan larastan:analyse
```

Do not use `@phpstan-ignore` without a comment explaining why.

---

## Code Style

```bash
./vendor/bin/pint
```

Pint is configured in `pint.json`. Run it before committing.
