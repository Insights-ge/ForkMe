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
        $appUrl = rtrim(is_string(config('app.url')) ? config('app.url') : '', '/');

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
        $siteName = $this->settings->site_name ?? 'DXF2';
        $tagline = $this->settings->site_tagline ?? 'Precision Laser Cutting Service';
        $defaultLocale = $this->settings->default_locale;

        $lines = [
            "# {$siteName}",
            '',
            "> {$tagline}",
            '',
            '## About',
            '',
            "{$siteName} is an online laser and CNC cutting order platform. Users upload DXF files, select material and thickness, receive instant pricing, and complete checkout. A Filament-powered admin panel manages materials, orders, and content.",
            '',
            '## Pages',
            '',
        ];

        $staticPages = [
            '' => 'Home — landing page with hero, materials, testimonials, FAQ, and contact.',
        ];

        foreach ($staticPages as $path => $label) {
            $url = $this->getLocalizedUrl($defaultLocale, $path);
            $lines[] = "- [{$label}]({$url})";
        }

        file_put_contents(public_path('llms.txt'), implode("\n", $lines) . "\n");

        $this->info('Generated llms.txt');
    }

    /**
     * @param  list<Locale>  $supportedLocales
     */
    private function generateHumansTxt(array $supportedLocales): void
    {
        $languageList = implode(', ', array_map(fn (Locale $l) => $l->native(), $supportedLocales));

        $appUrl = rtrim(is_string(config('app.url')) ? config('app.url') : '', '/');

        $lines = [
            '/* TEAM */',
            '  Name: ' . ($this->settings->site_name ?? 'DXF2'),
            '  Site: ' . $appUrl,
            '  Contact: ' . ($this->settings->contact_email ?? ''),
            '',
            '/* SITE */',
            '  Last update: ' . now()->year,
            '  Language: ' . $languageList,
            '  Standards: HTML5, CSS3, Laravel, TailwindCSS',
            '  Components: Livewire, Alpine.js',
            '  Doctype: HTML5',
            '',
        ];

        file_put_contents(public_path('humans.txt'), implode("\n", $lines));

        $this->info('Generated humans.txt');
    }

    /**
     * @param  list<string>  $locales
     */
    private function generateSecurityTxt(array $locales): void
    {
        $appUrl = rtrim(is_string(config('app.url')) ? config('app.url') : '', '/');
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

    private function getLocalizedUrl(string $locale, string $path): string
    {
        $appUrl = config('app.url');
        $baseUrl = rtrim(is_string($appUrl) ? $appUrl : '', '/');

        $path = $path !== '' ? ltrim($path, '/') : '';

        return $path !== '' ? "{$baseUrl}/{$locale}/{$path}" : "{$baseUrl}/{$locale}";
    }
}
