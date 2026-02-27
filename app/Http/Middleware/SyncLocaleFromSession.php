<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cookie;
use Symfony\Component\HttpFoundation\Response;

class SyncLocaleFromSession
{
    /**
     * Sync the locale between mcamara/laravel-localization session,
     * the Filament Language Switch cookie, and the application locale.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 1. Get available locales
        $supportedLocales = config('laravellocalization.supportedLocales', []);
        $supportedLocales = is_array($supportedLocales) ? $supportedLocales : [];
        $supportedKeys = array_keys($supportedLocales);

        // 2. Identify potential locale sources
        $sessionLocale = session('locale');
        $sessionLocale = is_string($sessionLocale) ? $sessionLocale : null;

        $cookieName = 'filament_language_switch_locale';
        $cookieLocale = $request->cookie($cookieName);
        $cookieLocale = is_string($cookieLocale) ? $cookieLocale : null;

        // 3. Determine context
        $previousUrl = url()->previous();
        $isInternalNavigation = str_contains($previousUrl, '/admin');

        $localeToUse = null;

        // 4. Resolve Locale Priority
        if (! $isInternalNavigation && $sessionLocale && in_array($sessionLocale, $supportedKeys, true)) {
            $localeToUse = $sessionLocale;
        } elseif ($cookieLocale && in_array($cookieLocale, $supportedKeys, true)) {
            $localeToUse = $cookieLocale;
        } elseif ($sessionLocale && in_array($sessionLocale, $supportedKeys, true)) {
            $localeToUse = $sessionLocale;
        }

        // 5. Apply & Sync
        if ($localeToUse !== null) {
            App::setLocale($localeToUse);

            $localeData = $supportedLocales[$localeToUse] ?? null;
            $regionalLocale = is_array($localeData) ? ($localeData['regional'] ?? null) : null;
            if (is_string($regionalLocale)) {
                setlocale(LC_TIME, $regionalLocale);
            }

            if (class_exists(\Carbon\Carbon::class)) {
                \Carbon\Carbon::setLocale($localeToUse);
            }

            if ($sessionLocale !== $localeToUse) {
                session(['locale' => $localeToUse]);
            }

            if ($cookieLocale !== $localeToUse) {
                Cookie::queue($cookieName, $localeToUse, 60 * 24 * 365);
            }
        }

        return $next($request);
    }
}
