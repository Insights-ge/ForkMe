<?php

namespace App\Http\Middleware;

use App\Enums\Locale;
use App\Settings\GeneralSettings;
use App\Support\Locales;
use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\URL;
use Symfony\Component\HttpFoundation\Response;

class SyncLocaleFromSession
{
    /**
     * Resolve and apply the active locale from route, cookie, session, or browser preference.
     * Redirects the root path to the user's preferred non-default locale when applicable.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $defaultLocale = app(GeneralSettings::class)->default_locale;
        $supportedKeys = array_map(fn (Locale $l) => $l->value, Locales::enabled());

        $routeLocale = $request->route('locale');
        $routeLocale = is_string($routeLocale) ? $routeLocale : null;

        $sessionLocale = session('locale');
        $sessionLocale = is_string($sessionLocale) ? $sessionLocale : null;

        $cookieName = 'filament_language_switch_locale';
        $cookieLocale = $request->cookie($cookieName);
        $cookieLocale = is_string($cookieLocale) ? $cookieLocale : null;

        if ($routeLocale && in_array($routeLocale, $supportedKeys, true)) {
            $localeToUse = $routeLocale;
        } elseif ($cookieLocale && in_array($cookieLocale, $supportedKeys, true)) {
            $localeToUse = $cookieLocale;
        } elseif ($sessionLocale && in_array($sessionLocale, $supportedKeys, true)) {
            $localeToUse = $sessionLocale;
        } else {
            // On the root path, honour browser language preference for first-time visitors.
            $browser = $request->path() === '/' ? $request->getPreferredLanguage($supportedKeys) : null;
            $localeToUse = (is_string($browser) && in_array($browser, $supportedKeys, true))
                ? $browser
                : $defaultLocale;
        }

        // Always redirect root to a localized home (/{locale}/).
        if ($request->path() === '/') {
            return redirect(Locales::route('home', [], $localeToUse), 302);
        }

        App::setLocale($localeToUse);

        $routeHasLocaleParameter = $request->route()?->hasParameter('locale') ?? false;
        URL::defaults($routeHasLocaleParameter ? ['locale' => $localeToUse] : []);

        $localeEnum = Locale::from($localeToUse);
        setlocale(LC_TIME, $localeEnum->regional());

        if (class_exists(Carbon::class)) {
            Carbon::setLocale($localeToUse);
        }

        if ($sessionLocale !== $localeToUse) {
            session(['locale' => $localeToUse]);
        }

        if ($cookieLocale !== $localeToUse) {
            Cookie::queue($cookieName, $localeToUse, 60 * 24 * 365);
        }

        return $next($request);
    }
}
