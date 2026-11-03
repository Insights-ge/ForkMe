<?php

namespace App\Filament\Resources\Infills;

use App\Filament\Resources\Infills\Pages\CreateInfill;
use App\Filament\Resources\Infills\Pages\EditInfill;
use App\Filament\Resources\Infills\Pages\ListInfills;
use App\Filament\Resources\Infills\Schemas\InfillForm;
use App\Filament\Resources\Infills\Tables\InfillsTable;
use App\Models\Infill;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class InfillResource extends Resource
{
    protected static ?string $model = Infill::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedViewColumns;

    protected static string|BackedEnum|null $activeNavigationIcon = Heroicon::ViewColumns;

    protected static ?int $navigationSort = 25;

    public static function getNavigationGroup(): ?string
    {
        return __('catalog.navigation_group');
    }

    public static function getModelLabel(): string
    {
        return __('infills.singular');
    }

    public static function getPluralModelLabel(): string
    {
        return __('infills.plural');
    }

    public static function hasTitleCaseModelLabel(): bool
    {
        return false;
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->withCount('incompatibleComponents');
    }

    public static function form(Schema $schema): Schema
    {
        return InfillForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return InfillsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListInfills::route('/'),
            'create' => CreateInfill::route('/create'),
            'edit' => EditInfill::route('/{record}/edit'),
        ];
    }
}
