## Naming Conventions

### Variables & Collections

- Variables: camelCase, descriptive — `$profilePicture`, not `$pic`.
- Collections: plural camelCase — `$users`, `$activeOrders`.

### Classes

- Models: singular PascalCase — `User`, `BlogPost`.
- Controllers: singular PascalCase with `Controller` suffix — `PostController`, `UserController`.
- Form Requests: PascalCase, `ActionModelRequest` format — `StorePostRequest`, `UpdateUserRequest`.

### Methods & Routes

- Methods: camelCase — `updateTaskPosition`, `getUserProfile`.
- Route URIs: kebab-case, plural — `/active-users`, `/blog-posts`.
- Named routes: dot notation `resource.action` — `users.show_active`, `posts.index`.

### Database

- Tables: snake_case plural — `posts`, `user_profiles`.
- Pivot tables: snake_case, singular model names in alphabetical order — `post_user`, `category_post`.
- `hasOne`/`belongsTo` relationships: singular camelCase — `author()`, `category()`.
- All other relationships: plural camelCase — `comments()`, `tags()`.

## Controllers

### CRUD Method Names

Use standard CRUD method names. For non-CRUD operations use descriptive names (`login`, `register`, `logout`).

| Method | HTTP | URI |
|---|---|---|
| `index` | GET | `/posts` |
| `show` | GET | `/posts/{post}` |
| `create` | GET | `/posts/create` |
| `store` | POST | `/posts` |
| `edit` | GET | `/posts/{post}/edit` |
| `update` | PUT | `/posts/{post}` |
| `destroy` | DELETE | `/posts/{post}` |

### HTTP Status Codes

| Code | Meaning |
|---|---|
| 200 | Successful, returns data |
| 201 | Resource created |
| 204 | Successful, no content |
| 401 | Unauthenticated |
| 403 | Authenticated but unauthorized |
| 404 | Not found |
| 422 | Validation failure |

## Form Requests

- Name as `ActionModelRequest` — e.g., `StorePostRequest`, `UpdateUserRequest`.
- One `FormRequest` per controller method.
- When many `FormRequest` classes serve one model, group them in a subdirectory (e.g., `Requests/Post/`).

## Blade Templates

- Never write database queries or model calls directly in Blade templates. All data must be passed from the controller.

## Development Practices

### Filament: Resources Must Stay Thin

Never put business logic in `Resource`, `Page`, `Widget`, `RelationManager`, or any form/table closure callback. Closures are for UI concerns only — delegate everything else to an Action class.

```php
// Bad — business logic inside a closure
Action::make('approve')
    ->action(function (Order $record) {
        $record->approve();
        Mail::to($record->customer)->send(...);
    });

// Good
Action::make('approve')
    ->action(fn (Order $record) => app(ApproveOrderAction::class)->execute($record));
```

### Filament: No Repeated Closures

If the same closure logic appears more than once (e.g., `->visible(fn () => auth()->user()?->isAdmin())`), extract it into a `Policy`, a named method, or a service call.

### Filament: Modular Form Schemas

Form schemas longer than ~50 lines must be split into dedicated section classes.

```php
// Bad: 300-line inline schema

// Good
return $form->schema([
    GeneralInformationSection::schema(),
    PricingSection::schema(),
    InventorySection::schema(),
]);
```

### Filament: Eager Load All Relationship Columns

Any column that traverses a relationship must be eager-loaded in `getEloquentQuery()`. Never rely on lazy loading in tables.

```php
public static function getEloquentQuery(): Builder
{
    return parent::getEloquentQuery()->with(['customer']);
}
```

### One Action = One Use Case

Each Action represents a single, named business operation. Avoid generic catch-all methods.

- `PublishProductAction`, `ArchiveProductAction`, `DuplicateProductAction` — correct
- `ProductService::updateEverything()` — wrong

### DTOs Across All Layer Boundaries

Never pass raw arrays between layers. Always convert to a DTO at the boundary.

```php
// Bad
$service->create($request->validated());

// Good
$service->create(ProductData::from($request->validated()));
```

### No Static Service Calls

Use `app(ServiceClass::class)` or constructor injection. Never call services statically — static calls make unit testing impossible.

```php
// Bad
ProductService::sync();

// Good
app(ProductService::class)->sync();
```

### Method Length

Methods should rarely exceed 30–40 lines. If a method grows larger, extract private helper methods, a new Action, or a dedicated Service.

### No Boolean Arguments

Boolean parameters hide intent. Replace with descriptively named methods or enum values.

```php
// Bad — nobody knows what `true` means
sendEmail($user, true);

// Good
sendVerificationEmail($user);
sendEmail($user, EmailType::Verification);
```

### Explicit Collection Generic Types

Always annotate the item type of returned collections so Larastan can infer types correctly.

```php
/** @return Collection<int, Product> */
public function products(): Collection
```

### Domain Events for Side Effects

Actions must not chain multiple side effects inline. Dispatch a domain event and handle each side effect in a separate Listener. This keeps Actions small and side effects independently testable.

```
// Bad: CreateOrderAction directly calls SendEmail + CreateInvoice + NotifyAdmin

// Good: CreateOrderAction dispatches OrderCreated
//   Listeners: SendOrderEmailListener, CreateInvoiceListener, NotifyAdminListener
```

### No Magic Strings

Never compare against raw string literals for statuses, types, providers, roles, permissions, or feature flags. Enums are mandatory for all of these.

```php
// Bad
if ($status === 'approved')

// Good
if ($status === OrderStatus::Approved)
```

### Action Test Coverage

Every Action must have four tests:

1. **Success** — happy path, assert expected state change.
2. **Validation** — assert invalid input is rejected.
3. **Authorization** — assert unauthorized users cannot execute.
4. **Tenant boundary** — assert the Action cannot access another workspace's data.

### Architectural Dependency Direction

Dependencies must only flow in one direction:

```
Filament → Action → Service → Model
```

These directions are forbidden:

- `Service` → Filament
- `Model` → Filament
- `Action` → Resource
- Domain → Integration (use contracts only)

## Filament

Use `search-docs` before making Filament changes. Always use Filament-specific Artisan commands to create files — run `php artisan list` to discover them.

### Correct Namespaces

| Component | Namespace |
|---|---|
| Form fields (`TextInput`, `Select`, `Repeater`, etc.) | `Filament\Forms\Components\` |
| Infolist entries (`TextEntry`, `IconEntry`, etc.) | `Filament\Infolists\Components\` |
| Layout (`Grid`, `Section`, `Fieldset`, `Tabs`, `Wizard`, etc.) | `Filament\Schemas\Components\` |
| Schema utilities (`Get`, `Set`) | `Filament\Schemas\Components\Utilities\` |
| Table columns (`TextColumn`, `IconColumn`, etc.) | `Filament\Tables\Columns\` |
| Table filters (`SelectFilter`, `Filter`, etc.) | `Filament\Tables\Filters\` |
| Actions (`DeleteAction`, `CreateAction`, etc.) | `Filament\Actions\` — **never** a sub-namespace |
| Icons | `Filament\Support\Icons\Heroicon` (enum, not string) |

### Patterns

Use `Get $get` to read other form field values for conditional logic:

```php
Select::make('type')
    ->options(ContactType::class)
    ->live(),

TextInput::make('company_name')
    ->required()
    ->visible(fn (Get $get): bool => $get('type') === 'business'),
```

Use `Set $set` inside `->afterStateUpdated()`. Prefer `->live(onBlur: true)` on text inputs to avoid per-keystroke requests:

```php
TextInput::make('name')
    ->live(onBlur: true)
    ->afterStateUpdated(fn (Set $set, ?string $state) => $set('slug', Str::slug($state ?? ''))),

TextInput::make('slug')->required(),
```

Compose layout by nesting `Section` and `Grid`. Children need explicit `->columnSpan()` or `->columnSpanFull()`:

```php
Section::make('Details')
    ->schema([
        Grid::make(2)->schema([
            TextInput::make('first_name')->columnSpan(1),
            TextInput::make('last_name')->columnSpan(1),
            TextInput::make('bio')->columnSpanFull(),
        ]),
    ]),
```

Use `Repeater` for inline `HasMany` management. `->relationship()` with no arguments binds to the relationship matching the field name:

```php
Repeater::make('qualifications')
    ->relationship()
    ->schema([
        TextInput::make('institution')->required(),
        TextInput::make('qualification')->required(),
    ])
    ->columns(2),
```

Use `->state()` with a closure to compute derived column values:

```php
TextColumn::make('full_name')
    ->state(fn (Contact $record): string => "{$record->first_name} {$record->last_name}"),
```

### Testing

Always call `$this->actingAs(User::factory()->create())` before testing panel functionality. Initialize tenancy when testing tenant-scoped pages.

```php
// Table test
livewire(ListContacts::class)
    ->assertCanSeeTableRecords($contacts)
    ->searchTable($contacts->first()->email)
    ->assertCanSeeTableRecords($contacts->take(1))
    ->assertCanNotSeeTableRecords($contacts->skip(1));
```

```php
// Create resource — ends with assertRedirect()
livewire(CreateContact::class)
    ->fillForm(['email' => 'jane@example.com', 'status' => 'subscribed'])
    ->call('create')
    ->assertHasNoFormErrors()
    ->assertNotified()
    ->assertRedirect();

assertDatabaseHas(Contact::class, ['email' => 'jane@example.com']);
```

```php
// Edit resource — use call('save'), not call('create'); no assertRedirect()
livewire(EditContact::class, ['record' => $contact->id])
    ->fillForm(['email' => 'updated@example.com'])
    ->call('save')
    ->assertHasNoFormErrors()
    ->assertNotified();

assertDatabaseHas(Contact::class, ['id' => $contact->id, 'email' => 'updated@example.com']);
```

```php
// Page action test
use Filament\Actions\Testing\TestAction;

livewire(ListContacts::class)
    ->callAction(TestAction::make('addField'), [
        'name' => 'Birthday', 'key' => 'birthday', 'type' => 'date',
    ])
    ->assertNotified();
```

```php
// Table row action test
livewire(ListContacts::class)
    ->callAction(TestAction::make('approve')->table($contact))
    ->assertNotified();
```

```php
// Validation test
livewire(CreateContact::class)
    ->fillForm(['email' => 'not-an-email'])
    ->call('create')
    ->assertHasFormErrors(['email' => 'email'])
    ->assertNotNotified();
```

### Common Mistakes

- **`$navigationIcon`** must be typed `string | BackedEnum | null`, not `?string` — Livewire will throw a type error at runtime.
- **Never use `->dehydrated(false)`** on a field that needs to be saved. It silently strips the value before the save handler runs. Only use it for UI-only helper fields.
- **`Grid`, `Section`, `Fieldset`, and `Repeater` do not span full width by default.** Always add `->columnSpanFull()` explicitly when needed.
- **Use `Select::make('relation_id')->relationship('relation', 'name')`** for BelongsTo fields. `BelongsToSelect` does not exist in v5.
- **Never import actions from `Filament\Tables\Actions\` or `Filament\Forms\Actions\`.** Always use `Filament\Actions\`.

## Tinker

Always use **single quotes** for the outer shell argument to prevent shell variable expansion:

```bash
php artisan tinker --execute 'Contact::query()->count();'
```

Use double quotes for PHP strings **inside**:

```bash
php artisan tinker --execute 'Contact::where("status", "subscribed")->count();'
```

## Artisan Tips

Filter `route:list` output with: `--method=GET`, `--name=contacts`, `--path=api`, `--except-vendor`.

Read config values with dot notation: `php artisan config:show database.default`, `php artisan config:show app.name`.

## Pest Notes

When creating tests, do **not** include the test suite directory in the `{name}` argument:

```bash
# Correct
php artisan make:test --pest CreateContactActionTest

# Wrong — Pest will create a nested directory
php artisan make:test --pest Feature/CreateContactActionTest
```

## Deployment

Laravel applications can be deployed using [Laravel Cloud](https://cloud.laravel.com/), which is the fastest way to deploy and scale production Laravel applications.
