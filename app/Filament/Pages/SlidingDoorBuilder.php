<?php

namespace App\Filament\Pages;

use BackedEnum;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;

class SlidingDoorBuilder extends Page
{
    use HasPageShield;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedWrenchScrewdriver;

    protected string $view = 'filament.admin.pages.sliding-door-builder';

    public static function getNavigationLabel(): string
    {
        return __('sliding_door_builder.navigation_label');
    }

    public function getTitle(): string
    {
        return __('sliding_door_builder.title');
    }
}
