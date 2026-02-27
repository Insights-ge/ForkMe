<?php

namespace App\Providers;

use BezhanSalleh\LanguageSwitch\LanguageSwitch;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        LanguageSwitch::configureUsing(function (LanguageSwitch $switch) {

            /** @var array<string, array<string, string>> $supportedLocales */
            $supportedLocales = config('laravellocalization.supportedLocales', ['en' => []]);

            $locales = array_keys($supportedLocales);

            $labels = [];
            foreach ($supportedLocales as $code => $locale) {
                $labels[$code] = $locale['native'] ?? $locale['name'] ?? $code;
            }

            $flags = [];
            foreach ($locales as $code) {
                $flags[$code] = asset("images/langs/{$code}.webp");
            }

            $switch
                ->locales($locales)
                ->labels($labels)
                ->flags($flags)
                ->flagsOnly()
                ->circular()
                ->visible(outsidePanels: true);
        });

        // Very USEFULFRIENDLY
        ViewAction::configureUsing(fn (ViewAction $action) => $action->iconButton());
        EditAction::configureUsing(fn (EditAction $action) => $action->iconButton());
        DeleteAction::configureUsing(fn (DeleteAction $action) => $action->iconButton());
        TextColumn::configureUsing(fn (TextColumn $column) => $column->toggleable());
        ImageColumn::configureUsing(fn (ImageColumn $column) => $column->toggleable());
        IconColumn::configureUsing(fn (IconColumn $column) => $column->toggleable());
    }
}
