<?php

namespace App\Filament\Resources\Infills\Pages;

use App\Filament\Resources\Infills\InfillResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListInfills extends ListRecords
{
    protected static string $resource = InfillResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
