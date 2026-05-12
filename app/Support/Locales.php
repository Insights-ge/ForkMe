<?php

namespace App\Support;

use App\Enums\Locale;
use App\Settings\GeneralSettings;

class Locales
{
    /**
     * Returns the enabled Locale enum cases from settings.
     *
     * @return list<Locale>
     */
    public static function enabled(): array
    {
        $codes = app(GeneralSettings::class)->enabled_locales;

        return array_values(array_filter(
            array_map(fn (string $code) => Locale::tryFrom($code), $codes),
        ));
    }

    /**
     * Returns a [value => label] map of all Locale cases for use in Select fields.
     *
     * @return array<string, string>
     */
    public static function options(): array
    {
        $options = [];
        foreach (Locale::cases() as $locale) {
            $options[$locale->value] = $locale->label() . ' — ' . $locale->native();
        }

        return $options;
    }

    /**
     * Generates a localized URL for a named route with the given locale prefix.
     *
     * @param  array<string, mixed>  $parameters
     */
    public static function route(string $name, array $parameters = [], ?string $locale = null): string
    {
        $locale ??= app()->getLocale();

        return route($name, array_merge(['locale' => $locale], $parameters));
    }

    /**
     * Returns all enabled locales as [value => label] for the current locale context.
     *
     * @return array<string, string>
     */
    public static function enabledOptions(): array
    {
        $options = [];

        foreach (self::enabled() as $locale) {
            $options[$locale->value] = $locale->label();
        }

        return $options;
    }
}
