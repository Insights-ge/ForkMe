<?php

namespace App\Filament\Resources\TranslationLines\Schemas;

use App\Support\Locales;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class TranslationLineForm
{
    public static function configure(Schema $schema): Schema
    {
        $locales = Locales::enabled();

        $localeFields = array_map(
            fn ($locale) => Textarea::make("text.{$locale->value}")
                ->label($locale->label())
                ->rows(2)
                ->required(),
            $locales
        );

        return $schema
            ->components([
                TextInput::make('group')
                    ->label(__('panel.translation_lines.form.group'))
                    ->required()
                    ->maxLength(255),

                TextInput::make('key')
                    ->label(__('panel.translation_lines.form.key'))
                    ->required()
                    ->maxLength(255),

                Section::make(__('panel.translation_lines.form.translations'))
                    ->schema($localeFields)
                    ->columns(count($locales)),
            ]);
    }
}
