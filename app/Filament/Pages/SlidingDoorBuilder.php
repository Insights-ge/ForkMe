<?php

namespace App\Filament\Pages;

use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Pages\Page;

class SlidingDoorBuilder extends Page
{
    use HasPageShield;

    protected static ?string $title = 'Sliding door builder';

    protected string $view = 'filament.admin.pages.sliding-door-builder';

}
