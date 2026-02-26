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
        $supportedKeys = array_keys($supportedLocales);

        // 2. Identify potential locale sources
        $sessionLocale = session('locale');
        $cookieName = 'filament_language_switch_locale';
        $cookieLocale = $request->cookie($cookieName);

        // 3. Determine context (Are we navigating within the admin panel?)
        // Adjust 'admin' if your panel path changes.
        // url()->previous() returns the Referer or root URL if missing.
        $previousUrl = url()->previous();
        $isInternalNavigation = str_contains($previousUrl, '/admin');

        $localeToUse = null;

        // 4. Resolve Locale Priority
        if (! $isInternalNavigation && $sessionLocale && in_array($sessionLocale, $supportedKeys)) {
            // Coming from outside (Frontend) -> Session is truth
            $localeToUse = $sessionLocale;
        } elseif ($cookieLocale && in_array($cookieLocale, $supportedKeys)) {
            // Internal navigation or Session missing -> Cookie is truth
            $localeToUse = $cookieLocale;
        } elseif ($sessionLocale && in_array($sessionLocale, $supportedKeys)) {
            // Fallback to session
            $localeToUse = $sessionLocale;
        }

        // 5. Apply & Sync
        if ($localeToUse) {
            // Set Application Locale
            App::setLocale($localeToUse);

            // Set System/Carbon Locale (Good practice)
            if (isset($supportedLocales[$localeToUse]['regional'])) {
                setlocale(LC_TIME, $supportedLocales[$localeToUse]['regional']);
            }
            if (class_exists(\Carbon\Carbon::class)) {
                \Carbon\Carbon::setLocale($localeToUse);
            }

            // Sync: Update Session (so Frontend reflects change)
            if ($sessionLocale !== $localeToUse) {
                session(['locale' => $localeToUse]);
            }

            // Sync: Update Cookie (so Filament Language Switcher reflects change)
            if ($cookieLocale !== $localeToUse) {
                Cookie::queue($cookieName, $localeToUse, 60 * 24 * 365);
            }
        }

        return $next($request);
    }
}
