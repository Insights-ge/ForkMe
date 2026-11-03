<?php

namespace App\Filament\Resources\Infills\Pages;

use App\Filament\Resources\Infills\InfillResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditInfill extends EditRecord
{
    protected static string $resource = InfillResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
