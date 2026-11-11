<?php

namespace App\Filament\Pages;

use App\Data\BuilderBrowserPayloadData;
use App\Enums\SlidingDoorBuilderStep;
use App\Services\BuilderCatalogReadService;
use App\Services\BuilderMetadataReadService;
use BackedEnum;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;

class SlidingDoorBuilder extends Page
{
    use HasPageShield;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedWrenchScrewdriver;

    protected static ?int $navigationSort = 0;

    protected string $view = 'filament.admin.pages.sliding-door-builder';

    public static function getNavigationLabel(): string
    {
        return __('sliding_door_builder.navigation_label');
    }

    public function getTitle(): string
    {
        return __('sliding_door_builder.title');
    }

    /**
     * @return array{steps: list<SlidingDoorBuilderStep>, initialStep: SlidingDoorBuilderStep, builderPayload: BuilderBrowserPayloadData}
     */
    protected function getViewData(): array
    {
        return [
            'steps' => SlidingDoorBuilderStep::cases(),
            'initialStep' => SlidingDoorBuilderStep::Size,
            'builderPayload' => new BuilderBrowserPayloadData(
                catalog: app(BuilderCatalogReadService::class)->read(),
                metadata: app(BuilderMetadataReadService::class)->read(),
            ),
        ];
    }
}
