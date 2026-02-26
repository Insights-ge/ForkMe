<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use BezhanSalleh\LanguageSwitch\LanguageSwitch;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Actions\DeleteAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;

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
    public function boot()
    {
        LanguageSwitch::configureUsing(function (LanguageSwitch $switch) {
            $switch
                ->locales(['en','ka']);
        });

        //Very USEFULFRIENDLY
        ViewAction::configureUsing(fn (ViewAction $action) => $action->iconButton());
        EditAction::configureUsing(fn (EditAction $action) => $action->iconButton());
        DeleteAction::configureUsing(fn (DeleteAction $action) => $action->iconButton());
        TextColumn::configureUsing(fn (TextColumn $column) => $column->toggleable());
        ImageColumn::configureUsing(fn (ImageColumn $column) => $column->toggleable());
        IconColumn::configureUsing(fn (IconColumn $column) => $column->toggleable());
    }
}
