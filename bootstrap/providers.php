<?php

use App\Providers\AppServiceProvider;
use App\Providers\Filament\AdminPanelProvider;
use Spatie\TranslationLoader\TranslationServiceProvider;

return [
    AppServiceProvider::class,
    AdminPanelProvider::class,
    TranslationServiceProvider::class,
];
