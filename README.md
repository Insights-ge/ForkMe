<p align="center">
  <a href="https://insights.ge" target="_blank" rel="noopener noreferrer">
    <picture>
      <source media="(prefers-color-scheme: dark)" srcset="public/images/logo-light.avif">
      <source media="(prefers-color-scheme: light)" srcset="public/images/logo-dark.avif">
      <img src="public/images/logo-light.avif" alt="Insights Logo" width="180">
    </picture>
  </a>
</p>

<h1 align="center">ForkMe — Production-Ready Laravel Starter Kit</h1>

<p align="center">
  <strong>An opinionated, feature-packed Laravel 12 & Filament 5 boilerplate crafted by <a href="https://insights.ge" target="_blank" rel="noopener noreferrer">Insights</a>.</strong>
</p>

<p align="center">
  <a href="https://insights.ge"><img src="https://img.shields.io/badge/Website-insights.ge-007ACC?style=for-the-badge&logo=google-chrome&logoColor=white" alt="Insights Website"></a>
  <a href="https://laravel.com"><img src="https://img.shields.io/badge/Laravel-12.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white" alt="Laravel 12"></a>
  <a href="https://filamentphp.com"><img src="https://img.shields.io/badge/Filament-v5.x-F59E0B?style=for-the-badge&logo=filament&logoColor=white" alt="Filament 5"></a>
  <a href="https://php.net"><img src="https://img.shields.io/badge/PHP-8.2%2B%20%7C%208.4-777BB4?style=for-the-badge&logo=php&logoColor=white" alt="PHP 8.2+"></a>
  <a href="https://tailwindcss.com"><img src="https://img.shields.io/badge/Tailwind_CSS-v4.x-06B6D4?style=for-the-badge&logo=tailwindcss&logoColor=white" alt="Tailwind CSS v4"></a>
  <a href="https://pestphp.com"><img src="https://img.shields.io/badge/Pest-v3.x-B026FF?style=for-the-badge&logo=pest&logoColor=white" alt="Pest 3"></a>
  <a href="LICENSE"><img src="https://img.shields.io/badge/License-MIT-green.svg?style=for-the-badge" alt="License MIT"></a>
</p>

<p align="center">
  <a href="#-about-us--insights">About Insights</a> •
  <a href="#-key-features">Key Features</a> •
  <a href="#-built-in-customizations--improvements">Customizations</a> •
  <a href="#-packages-inventory">Packages Inventory</a> •
  <a href="#-quick-start">Quick Start</a> •
  <a href="#-useful-developer-commands">Developer Commands</a> •
  <a href="#-architecture--conventions">Conventions</a>
</p>

---

## 💡 About Us & Insights

**ForkMe** was created by the team at [**Insights**](https://insights.ge) ([insights.ge](https://insights.ge)) to revolutionize how modern web applications are launched. 

Instead of spending days setting up standard boilerplate features — such as admin authentication, role-based access control, multi-language localization, media management, SEO sitemaps, and security hardening — **ForkMe** provides a beautifully engineered, production-ready foundation.

> *"Fork, configure, and ship. Focus on building product value from day one."* — [Insights](https://insights.ge)

### Why ForkMe?

- ⚡ **Zero Setup Friction**: Run `composer run setup` and jump straight into building your business logic.
- 🎯 **Pre-built Admin Panel**: Powered by **Filament v5** with full Shield RBAC, Breezy profile controls, language switching, and auth UI enhancements.
- 🌍 **Native Multilingual Core**: Full translation support for model attributes, UI labels, and localized routing.
- 🔐 **Security & Quality First**: Integrated HTML sanitization, N+1 query detection, static analysis with Larastan, Pest 3 test suite, and automated code formatting with Duster & Pint.

---

## ✨ Key Features

- 🖥️ **Filament v5 Admin Panel**: Pre-configured admin portal with custom auth styling, profile session management, and dashboard widgets.
- 🛡️ **Role & Permission Management**: Visual RBAC powered by `spatie/laravel-permission` and `bezhansalleh/filament-shield`.
- 🌐 **Multi-Language Support (i18n)**: Out-of-the-box support for translatable models (`spatie/laravel-translatable`), language switching UI, and localized database translations.
- 🖼️ **Advanced Media Library**: Custom path generator organizing uploads into human-readable model folders instead of numeric IDs.
- 📊 **Apex Charts Dashboard**: Modern interactive charts pre-integrated into Filament via `leandrocfe/filament-apex-charts`.
- ⚡ **GUI Cache Control**: Dedicated Filament admin page (`ManageCacheTools`) to clear and rebuild caches directly from the UI.
- 💾 **Automated Backups**: Back up databases and application storage via Filament UI with `shuvroroy/filament-spatie-laravel-backup`.
- 🔍 **SEO & Structured Data**: Dynamic sitemap generation (`spatie/laravel-sitemap`) and Schema.org rich snippets (`spatie/schema-org`).
- 🧹 **Security & Sanitization**: Anti-XSS HTML cleaning with `stevebauman/purify` and Model strict mode enabled in non-production.
- 🤖 **Laravel Boost AI Integration**: Built-in agent skills, guidelines, and MCP server configuration for AI-assisted development.

---

## ⚡ Built-in Customizations & Improvements

### 📁 Custom Media Path Generator (`App\Support\MediaPathGenerator`)
Standard Spatie Media Library stores files in arbitrary numeric folders (`storage/app/public/1/`, `storage/app/public/2/`), making file auditing, S3 bucket browsing, and manual backups difficult.

Our custom `MediaPathGenerator`:
- **Organizes by Model Name**: Uploads are stored in clean, pluralized model directories (e.g. `storage/app/public/users/`, `storage/app/public/blog-posts/`).
- **Dynamic Per-Model Overrides**: Models can define a `mediaDirectory()` method (e.g. `return 'avatars';`) for custom paths.
- **Nested Conversions**: `/conversions/` and `/responsive/` image variants stay neatly grouped inside the model's directory.

### 🛡️ Model Strict Mode & Global Filament Defaults
Configured globally in `AppServiceProvider`:

```php
// Enforce strict Eloquent rules in local & testing environments
Model::shouldBeStrict(! app()->isProduction());

// Convert verbose table actions to clean icon buttons
ViewAction::configureUsing(fn (ViewAction $action) => $action->iconButton());
EditAction::configureUsing(fn (EditAction $action) => $action->iconButton());
DeleteAction::configureUsing(fn (DeleteAction $action) => $action->iconButton());

// Make table columns toggleable globally across the entire admin panel
TextColumn::configureUsing(fn (TextColumn $column) => $column->toggleable());
ImageColumn::configureUsing(fn (ImageColumn $column) => $column->toggleable());
IconColumn::configureUsing(fn (IconColumn $column) => $column->toggleable());
```

- **Strict Models**: Automatically halts execution on lazy-loading (N+1 queries), unfillable attribute assignments, or accessing missing model attributes in dev/test environments.
- **Sleek UI Action Defaults**: Action buttons default to compact icon buttons.
- **Global Column Toggleability**: Users can customize visible table columns across all Filament resources out of the box.

### ⚡ Admin GUI Cache Control (`App\Filament\Pages\ManageCacheTools`)
Administrators can clear and optimize application caches (config, routes, views, icons, and permissions) directly from the Filament Admin UI without requiring SSH terminal access.

### 🌐 Locales Management & FilePond Georgian Patch
- **`App\Support\Locales` & `App\Enums\Locale`**: Type-safe locale management with flag badges, native labels, and localized route generators (`Locales::route()`).
- **Automated FilePond Georgian (`ka`) Patch (`scripts/patch-filepond-ka.php`)**: FilePond does not natively ship a Georgian translation. We created an automated script linked to Composer's `post-update-cmd` that injects Georgian translations into FilePond JS assets upon package installation and updates.

---

## 📦 Packages Inventory

Here is the complete list of curated packages pre-installed and configured in **ForkMe**:

### 🛠️ Core Framework & Admin Panel

| Package | Version | Description |
| :--- | :--- | :--- |
| [`laravel/framework`](https://github.com/laravel/framework) | `^12.0` | The robust Laravel 12 PHP framework |
| [`filament/filament`](https://github.com/filamentphp/filament) | `^5.0` | Next-generation admin panel, form, table, and widget builder |
| [`tailwindcss`](https://tailwindcss.com) | `^4.2` | Utility-first CSS framework (v4 engine) |
| [`vite`](https://vitejs.dev) | `^7.0` | Next generation frontend tooling & asset bundler |

### 🎨 Filament Ecosystem & Extensions

| Package | Version | Description |
| :--- | :--- | :--- |
| [`bezhansalleh/filament-shield`](https://github.com/bezhanSalleh/filament-shield) | `*` | Complete visual Role & Permission management for Filament |
| [`bezhansalleh/filament-language-switch`](https://github.com/bezhanSalleh/filament-language-switch) | `^4.1` | Sleek locale switcher component for the Filament header |
| [`diogogpinto/filament-auth-ui-enhancer`](https://github.com/diogogpinto/filament-auth-ui-enhancer) | `^2.0` | Beautiful login, registration, and auth UI customizations |
| [`jeffgreco13/filament-breezy`](https://github.com/jeffgreco13/filament-breezy) | `^3.1` | User profile, password reset, 2FA, and session management |
| [`filament/spatie-laravel-media-library-plugin`](https://github.com/filamentphp/filament) | `^5.0` | Spatie Media Library form components & upload previews |
| [`filament/spatie-laravel-settings-plugin`](https://github.com/filamentphp/filament) | `^5.0` | Manage application settings models inside Filament forms |
| [`leandrocfe/filament-apex-charts`](https://github.com/leandrocfe/filament-apex-charts) | `^5.0` | ApexCharts integration for Filament dashboard widgets |
| [`shuvroroy/filament-spatie-laravel-backup`](https://github.com/shuvroroy/filament-spatie-laravel-backup) | `^3.4` | Monitor and trigger Spatie application backups from Filament |

### 🌿 Spatie Ecosystem & Data Tools

| Package | Version | Description |
| :--- | :--- | :--- |
| [`spatie/laravel-medialibrary`](https://github.com/spatie/laravel-medialibrary) | `^11.21` | Associate files with Eloquent models & process conversions |
| [`spatie/laravel-permission`](https://github.com/spatie/laravel-permission) | `^6.24` | Flexible user roles and granular permissions in database |
| [`spatie/laravel-translatable`](https://github.com/spatie/laravel-translatable) | `^6.11` | Make Eloquent attributes translatable (stored as JSON) |
| [`spatie/laravel-translation-loader`](https://github.com/spatie/laravel-translation-loader) | `^2.8` | Store language lines in database tables instead of files |
| [`spatie/laravel-sitemap`](https://github.com/spatie/laravel-sitemap) | `^7.4` | Generate crawler-friendly XML sitemaps automatically |
| [`spatie/schema-org`](https://github.com/spatie/schema-org) | `^4.0` | Fluent builder for Schema.org structured data JSON-LD |

### 🌐 Internationalization & Security

| Package | Version | Description |
| :--- | :--- | :--- |
| [`laravel-lang/common`](https://github.com/Laravel-Lang/common) | `^6.8` | 100+ community translations for Laravel system prompts & validation |
| [`stevebauman/purify`](https://github.com/stevebauman/purify) | `^6.3` | HTML input sanitizer powered by HTMLPurifier |

### 🧪 Development, Testing & Code Quality (`require-dev`)

| Package | Version | Purpose |
| :--- | :--- | :--- |
| [`pestphp/pest`](https://github.com/pestphp/pest) | `^3.8` | Elegant PHP testing framework with Laravel plugin |
| [`larastan/larastan`](https://github.com/larastan/larastan) | `^3.0` | Static analysis wrapper around PHPStan for Laravel |
| [`tightenco/duster`](https://github.com/tightenco/duster) | `^3.4` | Unified linter & code fixer (TLint, PHP_CodeSniffer, Pint) |
| [`laravel/pint`](https://github.com/laravel/pint) | `^1.24` | Opinionated PHP code style fixer built on PHP-CS-Fixer |
| [`laraveldaily/filacheck`](https://github.com/laraveldaily/filacheck) | `^1.1` | Static code checker tailored specifically for Filament resources |
| [`beyondcode/laravel-query-detector`](https://github.com/beyondcode/laravel-query-detector) | `^2.2` | Detect N+1 database query issues during local development |
| [`laravel/boost`](https://github.com/laravel/boost) | `^2.4` | AI guidelines, skills, and agent integration tools |
| [`laravel/pail`](https://github.com/laravel/pail) | `^1.2.2` | Real-time application log tailing directly in your terminal |
| [`laravel/sail`](https://github.com/laravel/sail) | `^1.41` | Lightweight Docker CLI environment for Laravel development |

---

## 🚀 Quick Start

### 1. Requirements

- **PHP**: `^8.2` or `8.4`
- **Composer**: `^2.2`
- **Node.js**: `^18.x` or `^20.x`
- **Database**: MySQL / PostgreSQL / SQLite

### 2. Installation Pipeline

Clone the repository and run the automated setup command:

```bash
git clone https://github.com/moodloi/ForkMe.git
cd ForkMe

# Automated initial setup script
composer run setup
```

The `composer run setup` pipeline automatically:
1. Installs PHP Composer dependencies
2. Copies `.env.example` to `.env`
3. Generates the application encryption key (`artisan key:generate`)
4. Runs database migrations (`artisan migrate --force`)
5. Installs NPM dependencies & compiles Vite frontend assets

### 3. Seed Database & Create Admin User

Seed default settings, permissions, and demo account:

```bash
php artisan migrate:fresh --seed
```

🔑 **Default Admin Credentials**:
- **URL**: `https://127.0.0.1:8000/admin`
- **Email**: `demo@demo.com`
- **Password**: `demo`

### 4. Start Development Server

Run backend server, queue worker, and Vite hot reload concurrently:

```bash
composer run dev
```

---

## 🔧 Useful Developer Commands

We maintain a suite of developer-friendly commands to keep your codebase clean and performing optimally:

```bash
# Run unit & feature test suite with Pest
composer test

# Format code with Duster (Pint + TLint + PHP-CS-Fixer)
./vendor/bin/duster fix

# Run static analysis check with Larastan
./vendor/bin/phpstan analyse --memory-limit=4G

# Check Filament resource conventions
vendor/bin/filacheck

# Tail logs in real time
php artisan pail

# Clear all caches (config, view, route, icons, filament)
php artisan optimize:clear
php artisan filament:optimize-clear
```

---

## 📐 Architecture & Conventions

**ForkMe** follows clean, maintainable Laravel architectural principles:

- **Thin Controllers & Filament Resources**: UI callbacks remain lightweight. Business logic lives in dedicated **Action** classes.
- **DTO Boundary Guards**: Data transfer objects (DTOs) pass structured data across layer boundaries.
- **Eager Loading Enforcement**: All relationship columns in Filament tables must be eager-loaded in `getEloquentQuery()` to prevent lazy loading bottlenecks.
- **Strict Enums**: No magic strings for status codes, types, or roles. Enums are strictly typed.
- **Policy Enforcement**: Resources use Filament Shield policies to govern access control cleanly.

---

## 📄 License

The **ForkMe** starter kit is open-sourced software licensed under the [MIT License](LICENSE).

---

<p align="center">
  Crafted with ❤️ by <a href="https://insights.ge" target="_blank" rel="noopener noreferrer"><strong>Insights</strong> (insights.ge)</a>
</p>