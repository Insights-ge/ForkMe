<?php

namespace App\Console\Commands;

use App\Enums\Locale;
use App\Settings\GeneralSettings;
use App\Support\Locales;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Url;

final class GenerateSitemap extends Command
{
    /**
     * @var string
     */
    protected $signature = 'sitemap:generate';

    /**
     * @var string
     */
    protected $description = 'Generate localized sitemaps, robots.txt, and llms.txt for the application';

    public function __construct(private readonly GeneralSettings $settings)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $this->info('Starting sitemap generation...');

        $baseUrl = config('app.url');
        if (! is_string($baseUrl) || $baseUrl === '') {
            $this->error('APP_URL is not set in your .env file.');

            return self::FAILURE;
        }

        $supportedLocales = Locales::enabled();

        /** @var list<string> $locales */
        $locales = array_map(fn (Locale $l) => $l->value, $supportedLocales);

        foreach ($locales as $locale) {
            $this->info("Generating sitemap for locale: {$locale}");
            $this->generateLocaleSitemap($locale, $locales);
        }

        $this->generateRobotsTxt($locales);
        $this->generateLlmsTxt();
        $this->generateHumansTxt($supportedLocales);
        $this->generateSecurityTxt($locales);

        $this->info('Sitemap generation completed successfully!');

        return self::SUCCESS;
    }

    /**
     * @param  list<string>  $allLocales
     */
    private function generateLocaleSitemap(string $locale, array $allLocales): void
    {
        $sitemap = Sitemap::create();

        $this->addStaticPages($sitemap, $locale, $allLocales);

        $filename = "sitemap-{$locale}.xml";
        $sitemap->writeToFile(public_path($filename));

        $this->info("Generated {$filename}");
    }

    /**
     * @param  list<string>  $allLocales
     */
    private function addStaticPages(Sitemap $sitemap, string $locale, array $allLocales): void
    {
        /**
         * @var array<string, array{
         *   path: string,
         *   priority: float,
         *   frequency: string
         * }> $pages
         */
        $pages = [
            'home' => [
                'path' => '',
                'priority' => 1.0,
                'frequency' => Url::CHANGE_FREQUENCY_DAILY,
            ],
        ];

        $now = Carbon::now();

        foreach ($pages as $config) {
            $path = $config['path'];

            $urlTag = Url::create($this->getLocalizedUrl($locale, $path))
                ->setLastModificationDate($now)
                ->setChangeFrequency($config['frequency'])
                ->setPriority($config['priority']);

            foreach ($allLocales as $altLocale) {
                $urlTag->addAlternate(
                    $this->getLocalizedUrl($altLocale, $path),
                    $altLocale
                );
            }

            $defaultLocale = $this->settings->default_locale;
            $urlTag->addAlternate(
                $this->getLocalizedUrl($defaultLocale, $path),
                'x-default'
            );

            $sitemap->add($urlTag);
        }
    }

    /**
     * @param  list<string>  $locales
     */
    private function generateRobotsTxt(array $locales): void
    {
        $appUrl = $this->appUrl();

        $lines = [
            'User-agent: *',
            'Allow: /',
            '',
            '# Sitemaps',
        ];

        foreach ($locales as $locale) {
            $lines[] = "Sitemap: {$appUrl}/sitemap-{$locale}.xml";
        }

        $lines[] = '';
        $lines[] = '# LLMs.txt';
        $lines[] = "LLMs: {$appUrl}/llms.txt";

        file_put_contents(public_path('robots.txt'), implode("\n", $lines) . "\n");

        $this->info('Generated robots.txt');
    }

    private function generateLlmsTxt(): void
    {
        $defaultLocale = $this->settings->default_locale;

        /** @var list<array{label: string, url: string, notes: string}> $pages */
        $pages = [];

        foreach ($this->llmsPages() as $path => $page) {
            $pages[] = [
                'label' => $page['label'],
                'url' => $this->getLocalizedUrl($defaultLocale, $path),
                'notes' => $page['notes'],
            ];
        }

        $contents = view('seo.llms', [
            'siteName' => $this->siteName(),
            'tagline' => $this->settings->site_tagline ?? '',
            'about' => $this->settings->default_meta_description ?? '',
            'techStack' => $this->techStack(),
            'pages' => $pages,
        ])->render();

        file_put_contents(public_path('llms.txt'), $this->normalize($contents));

        $this->info('Generated llms.txt');
    }

    /**
     * Pages listed in llms.txt, keyed by locale-relative path.
     *
     * @return array<string, array{label: string, notes: string}>
     */
    private function llmsPages(): array
    {
        return [
            '' => ['label' => 'Home', 'notes' => 'Landing page.'],
        ];
    }

    /**
     * @param  list<Locale>  $supportedLocales
     */
    private function generateHumansTxt(array $supportedLocales): void
    {
        $contents = view('seo.humans', [
            'siteName' => $this->siteName(),
            'siteUrl' => $this->appUrl(),
            'contactEmail' => $this->settings->contact_email,
            'lastUpdate' => now()->year,
            'languages' => implode(', ', array_map(fn (Locale $l) => $l->native(), $supportedLocales)),
        ])->render();

        file_put_contents(public_path('humans.txt'), $this->normalize($contents));

        $this->info('Generated humans.txt');
    }

    /**
     * @param  list<string>  $locales
     */
    private function generateSecurityTxt(array $locales): void
    {
        $appUrl = $this->appUrl();
        $contact = $this->settings->contact_email ?? '';
        $preferredLanguages = implode(', ', $locales);
        $expires = now()->addYear()->toIso8601String();

        $lines = [
            "Contact: mailto:{$contact}",
            "Expires: {$expires}",
            "Preferred-Languages: {$preferredLanguages}",
            "Canonical: {$appUrl}/.well-known/security.txt",
            '',
        ];

        $content = implode("\n", $lines);

        file_put_contents(public_path('security.txt'), $content);
        file_put_contents(public_path('.well-known/security.txt'), $content);

        $this->info('Generated security.txt');
    }

    /**
     * Tech stack lines for llms.txt, grouped by section.
     *
     * Versions are read from composer.json and package.json; entries whose package
     * is not installed are dropped, so the list cannot drift from the manifests.
     *
     * @return array<string, list<string>>
     */
    private function techStack(): array
    {
        $versions = array_merge(
            $this->manifestVersions(base_path('composer.json'), ['require', 'require-dev']),
            $this->manifestVersions(base_path('package.json'), ['dependencies', 'devDependencies']),
        );

        $stack = [];

        foreach ($this->stackSections() as $section => $entries) {
            $lines = [];

            foreach ($entries as $package => $entry) {
                if (! array_key_exists($package, $versions)) {
                    continue;
                }

                $version = $package === 'php'
                    ? $this->minorVersion($versions[$package])
                    : $this->majorVersion($versions[$package]);

                $label = trim("{$entry['name']} {$version}");
                $notes = $entry['notes'] !== '' ? " ({$entry['notes']})" : '';

                $lines[] = "**{$entry['role']}**: {$label}{$notes}";
            }

            if ($lines !== []) {
                $stack[$section] = $lines;
            }
        }

        return $stack;
    }

    /**
     * Curated stack entries, keyed by section then by composer/npm package name.
     *
     * @return array<string, array<string, array{role: string, name: string, notes: string}>>
     */
    private function stackSections(): array
    {
        return [
            'Backend' => [
                'php' => ['role' => 'PHP', 'name' => '', 'notes' => ''],
                'laravel/framework' => ['role' => 'Framework', 'name' => 'Laravel', 'notes' => ''],
                'filament/filament' => ['role' => 'Admin Panel', 'name' => 'Filament', 'notes' => ''],
                'bezhansalleh/filament-shield' => ['role' => 'Roles & Permissions UI', 'name' => 'Filament Shield', 'notes' => ''],
                'spatie/laravel-permission' => ['role' => 'Authorization', 'name' => 'Spatie Laravel Permission', 'notes' => ''],
                'spatie/laravel-medialibrary' => ['role' => 'Media', 'name' => 'Spatie Media Library', 'notes' => ''],
                'spatie/laravel-translatable' => ['role' => 'Translatable Models', 'name' => 'Spatie Laravel Translatable', 'notes' => ''],
                'filament/spatie-laravel-settings-plugin' => ['role' => 'Settings', 'name' => 'Spatie Laravel Settings', 'notes' => 'Filament plugin'],
                'spatie/laravel-sitemap' => ['role' => 'Sitemap', 'name' => 'Spatie Laravel Sitemap', 'notes' => ''],
                'spatie/schema-org' => ['role' => 'Structured Data', 'name' => 'Spatie Schema.org', 'notes' => ''],
                'stevebauman/purify' => ['role' => 'HTML Sanitizing', 'name' => 'Purify', 'notes' => ''],
                'laravel-lang/common' => ['role' => 'Translations', 'name' => 'Laravel Lang', 'notes' => ''],
                'bezhansalleh/filament-language-switch' => ['role' => 'Language Switcher', 'name' => 'Filament Language Switch', 'notes' => ''],
            ],
            'Frontend' => [
                'vite' => ['role' => 'Build Tool', 'name' => 'Vite', 'notes' => ''],
                'tailwindcss' => ['role' => 'CSS', 'name' => 'Tailwind CSS', 'notes' => ''],
            ],
            'Testing' => [
                'pestphp/pest' => ['role' => 'Framework', 'name' => 'Pest', 'notes' => 'with Laravel plugin'],
                'phpunit/phpunit' => ['role' => 'Test Runner', 'name' => 'PHPUnit', 'notes' => ''],
            ],
            'Dev Tools' => [
                'larastan/larastan' => ['role' => 'Static Analysis', 'name' => 'Larastan', 'notes' => ''],
                'beyondcode/laravel-query-detector' => ['role' => 'Query Detector', 'name' => 'Laravel Query Detector', 'notes' => 'N+1 detection'],
                'laravel/pail' => ['role' => 'Log Viewer', 'name' => 'Laravel Pail', 'notes' => ''],
                'tightenco/duster' => ['role' => 'Code Style', 'name' => 'Duster', 'notes' => 'wraps Laravel Pint'],
                'laraveldaily/filacheck' => ['role' => 'Filament Linter', 'name' => 'Filacheck', 'notes' => ''],
                'laravel/boost' => ['role' => 'AI Tooling', 'name' => 'Laravel Boost', 'notes' => ''],
                'laravel/tinker' => ['role' => 'REPL', 'name' => 'Laravel Tinker', 'notes' => ''],
            ],
        ];
    }

    /**
     * Read package => version-constraint pairs out of a JSON manifest.
     *
     * @param  list<string>  $keys
     * @return array<string, string>
     */
    private function manifestVersions(string $path, array $keys): array
    {
        if (! is_file($path)) {
            return [];
        }

        $contents = file_get_contents($path);

        if ($contents === false) {
            return [];
        }

        $manifest = json_decode($contents, true);

        if (! is_array($manifest)) {
            return [];
        }

        $versions = [];

        foreach ($keys as $key) {
            if (! isset($manifest[$key]) || ! is_array($manifest[$key])) {
                continue;
            }

            foreach ($manifest[$key] as $package => $constraint) {
                if (is_string($package) && is_string($constraint)) {
                    $versions[$package] = $constraint;
                }
            }
        }

        return $versions;
    }

    /**
     * Major version from a constraint such as `^13.0`, or an empty string for `*`.
     */
    private function majorVersion(string $constraint): string
    {
        preg_match('/(\d+)/', $constraint, $matches);

        return $matches[1] ?? '';
    }

    /**
     * Major and minor version from a constraint such as `^8.4`.
     */
    private function minorVersion(string $constraint): string
    {
        preg_match('/(\d+)(?:\.(\d+))?/', $constraint, $matches);

        if (! isset($matches[1])) {
            return '';
        }

        return isset($matches[2]) ? "{$matches[1]}.{$matches[2]}" : $matches[1];
    }

    /**
     * Collapse blank-line runs left by Blade directives and end with a single newline.
     */
    private function normalize(string $contents): string
    {
        $collapsed = preg_replace("/\n{3,}/", "\n\n", trim($contents));

        return ($collapsed ?? '') . "\n";
    }

    private function appUrl(): string
    {
        $appUrl = config('app.url');

        return rtrim(is_string($appUrl) ? $appUrl : '', '/');
    }

    /**
     * Site name from settings, falling back to the configured application name.
     */
    private function siteName(): string
    {
        if ($this->settings->site_name !== '') {
            return $this->settings->site_name;
        }

        $appName = config('app.name');

        return is_string($appName) && $appName !== '' ? $appName : 'Laravel';
    }

    private function getLocalizedUrl(string $locale, string $path): string
    {
        $baseUrl = $this->appUrl();

        $path = $path !== '' ? ltrim($path, '/') : '';

        return $path !== '' ? "{$baseUrl}/{$locale}/{$path}" : "{$baseUrl}/{$locale}";
    }
}
