<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GeneralSettingsSeeder extends Seeder
{
    public function run(): void
    {
        $defaults = [
            'site_name' => config('app.name', 'ForkMe'),
            'site_tagline' => null,
            'contact_email' => 'hello@example.com',
            'enabled_locales' => ['ka', 'en'],
            'default_locale' => 'ka',
            'branding_logo_main' => null,
            'branding_logo_header' => null,
            'branding_logo_footer' => null,
            'filament_brand_logo' => null,
            'filament_dark_mode_brand_logo' => null,
            'filament_favicon' => null,
            'gtm_id' => null,
            'social_facebook' => null,
            'social_instagram' => null,
            'social_whatsapp' => null,
            'og_image' => null,
            'og_image_width' => 1200,
            'og_image_height' => 630,
            'og_type' => 'website',
            'default_meta_description' => null,
            'default_meta_keywords' => null,
            'filament_auth_page_bg_image' => null,
        ];

        $table = 'settings';

        foreach ($defaults as $name => $value) {
            $payload = json_encode($value);
            $exists = DB::table($table)
                ->where('group', 'general')
                ->where('name', $name)
                ->exists();

            if ($exists) {
                DB::table($table)
                    ->where('group', 'general')
                    ->where('name', $name)
                    ->update(['payload' => $payload, 'updated_at' => now()]);
            } else {
                DB::table($table)->insert([
                    'group' => 'general',
                    'name' => $name,
                    'payload' => $payload,
                    'locked' => false,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
