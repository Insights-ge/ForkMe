<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class GeneralSettings extends Settings
{
    // General
    public string $site_name;

    public ?string $site_tagline;

    public string $contact_email;

    // Localization
    /** @var list<string> */
    public array $enabled_locales;

    public string $default_locale;

    // Branding
    public ?string $branding_logo_main;

    public ?string $branding_logo_header;

    public ?string $branding_logo_footer;

    public ?string $filament_brand_logo;

    public ?string $filament_dark_mode_brand_logo;

    public ?string $filament_favicon;

    public ?string $filament_auth_page_bg_image;

    // SEO
    public ?string $gtm_id;

    public ?string $social_facebook;

    public ?string $social_instagram;

    public ?string $social_whatsapp;

    public ?string $og_image;

    public int $og_image_width;

    public int $og_image_height;

    public string $og_type;

    public ?string $default_meta_description;

    public ?string $default_meta_keywords;

    public static function group(): string
    {
        return 'general';
    }

    /** @return list<string> */
    public function enabledLocales(): array
    {
        return $this->enabled_locales;
    }
}
