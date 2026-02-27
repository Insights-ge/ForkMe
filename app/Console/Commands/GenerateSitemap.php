<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;
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
    protected $description = 'Generate localized sitemaps for the application';

    public function handle(): int
    {
        $this->info('Starting sitemap generation...');

        $baseUrl = config('app.url');
        if (! is_string($baseUrl) || $baseUrl === '') {
            $this->error('APP_URL is not set in your .env file.');

            return self::FAILURE;
        }

        /** @var array<string, mixed> $supportedLocales */
        $supportedLocales = LaravelLocalization::getSupportedLocales();

        /** @var list<string> $locales */
        $locales = array_keys($supportedLocales);

        foreach ($locales as $locale) {
            $this->info("Generating sitemap for locale: {$locale}");
            $this->generateLocaleSitemap($locale, $locales);
        }

        $this->info('Sitemap generation completed successfully!');

        return self::SUCCESS;
    }

    /**
     * @param  list<string>  $allLocales
     */
    private function generateLocaleSitemap(string $locale, array $allLocales): void
    {
        $sitemap = Sitemap::create();

        $this->addPages($sitemap, $locale, $allLocales);

        $filename = "sitemap-{$locale}.xml";
        $sitemap->writeToFile(public_path($filename));

        $this->info("Generated {$filename}");
    }

    /**
     * @param  list<string>  $allLocales
     */
    private function addPages(Sitemap $sitemap, string $locale, array $allLocales): void
    {
        /**
         * @var array<string, array{
         *   path: string,
         *   priority: float,
         *   frequency: string
         * }> $pages
         */
        $pages = [
            'welcome' => [
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

            $defaultLocale = LaravelLocalization::getDefaultLocale();
            $urlTag->addAlternate(
                $this->getLocalizedUrl($defaultLocale, $path),
                'x-default'
            );

            $sitemap->add($urlTag);
        }
    }

    private function getLocalizedUrl(string $locale, string $path): string
    {
        $appUrl = config('app.url');
        $baseUrl = rtrim(is_string($appUrl) ? $appUrl : '', '/');

        $path = $path !== '' ? ltrim($path, '/') : '';

        /** @var bool $hideDefault */
        $hideDefault = (bool) config('laravellocalization.hideDefaultLocaleInURL', false);

        $defaultLocale = LaravelLocalization::getDefaultLocale();

        if ($hideDefault && $locale === $defaultLocale) {
            return $path !== '' ? "{$baseUrl}/{$path}" : $baseUrl;
        }

        return $path !== '' ? "{$baseUrl}/{$locale}/{$path}" : "{$baseUrl}/{$locale}";
    }
}
