<?php

namespace App\Filament\Resources\Infills\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class InfillsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')->label(__('infills.code'))->searchable()->sortable(),
                TextColumn::make('description')->label(__('infills.description'))->searchable()->wrap(),
                TextColumn::make('unique_code')->label(__('infills.unique_code'))->searchable()->copyable(),
                TextColumn::make('incompatible_components_count')->label(__('infills.incompatible_components'))->sortable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
