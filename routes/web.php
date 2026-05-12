<?php

use App\Http\Controllers\HomeController;
use App\Http\Middleware\SyncLocaleFromSession;
use App\Settings\GeneralSettings;
use Illuminate\Support\Facades\Route;

// Redirect root to the default locale prefix — resolved lazily so settings
// are not loaded before migrations run (e.g. during console commands).
Route::get('/', static function () {
    $defaultLocale = app()->runningInConsole()
        ? (config('app.locale') ?? 'en')
        : app(GeneralSettings::class)->default_locale;

    return redirect("/{$defaultLocale}", 302);
});

Route::prefix('{locale}')
    ->middleware(SyncLocaleFromSession::class)
    ->where(['locale' => '[a-z]{2}(?:-[A-Z]{2})?'])
    ->group(function (): void {
        Route::get('/', HomeController::class)->name('home');
    });
