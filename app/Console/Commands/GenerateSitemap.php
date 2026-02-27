<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Url;

class GenerateSitemap extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sitemap:generate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate localized sitemaps for the application';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting sitemap generation...');

        $baseUrl = config('app.url');
        if (! $baseUrl) {
            $this->error('APP_URL is not set in your .env file.');
            return 1;
        }

        $locales = array_keys(LaravelLocalization::getSupportedLocales());

        foreach ($locales as $locale) {
            $this->info("Generating sitemap for locale: {$locale}");
            $this->generateLocaleSitemap($locale, $locales);
        }

        $this->info('Sitemap generation completed successfully!');
        return 0;
    }

    /**
     * Generate sitemap for a specific locale
     */
    protected function generateLocaleSitemap(string $locale, array $allLocales): void
    {
        $sitemap = Sitemap::create();

        // Add static pages
        $this->addPages($sitemap, $locale, $allLocales);

        // Save locale-specific sitemap
        $filename = "sitemap-{$locale}.xml";
        $sitemap->writeToFile(public_path($filename));

        $this->info("Generated {$filename}");
    }

    /**
     * Add pages to sitemap
     */
    protected function addPages(Sitemap $sitemap, string $locale, array $allLocales): void
    {
        // Define your static pages here with their paths (no leading slash)
        $pages = [
            'welcome' => [
                'path' => '', 
                'priority' => 1.0,
                'frequency' => Url::CHANGE_FREQUENCY_DAILY,
            ],
            // Example of other potential pages
            'about' => [
                'path' => 'about',
                'priority' => 0.8,
                'frequency' => Url::CHANGE_FREQUENCY_MONTHLY,
            ],
        ];

        foreach ($pages as $pageId => $config) {
            $url = $this->getLocalizedUrl($locale, $config['path']);
            
            $urlTag = Url::create($url)
                ->setLastModificationDate(Carbon::now())
                ->setChangeFrequency($config['frequency'])
                ->setPriority($config['priority']);

            // Add alternate language links (xhtml:link)
            foreach ($allLocales as $altLocale) {
                $altUrl = $this->getLocalizedUrl($altLocale, $config['path']);
                $urlTag->addAlternate($altUrl, $altLocale);
            }
            
            // x-default
            $defaultLocale = LaravelLocalization::getDefaultLocale();
            $urlTag->addAlternate($this->getLocalizedUrl($defaultLocale, $config['path']), 'x-default');

            $sitemap->add($urlTag);
        }
    }

    /**
     * Get localized URL manually to avoid reliance on broken request context in CLI
     */
    protected function getLocalizedUrl(string $locale, string $path): string
    {
        $baseUrl = rtrim(config('app.url'), '/');
        $path = $path ? ltrim($path, '/') : '';

        $hideDefault = config('laravellocalization.hideDefaultLocaleInURL', false);
        $defaultLocale = LaravelLocalization::getDefaultLocale();

        if ($hideDefault && $locale === $defaultLocale) {
            return $path ? "{$baseUrl}/{$path}" : "{$baseUrl}";
        }

        return $path ? "{$baseUrl}/{$locale}/{$path}" : "{$baseUrl}/{$locale}";
    }
}
