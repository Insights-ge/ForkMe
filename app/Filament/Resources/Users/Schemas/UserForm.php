<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label(__('panel.users.form.name'))
                    ->required(),
                TextInput::make('email')
                    ->label(__('panel.users.form.email'))
                    ->email()
                    ->required(),
                DateTimePicker::make('email_verified_at')
                    ->label(__('panel.users.form.email_verified_at')),
                TextInput::make('password')
                    ->label(__('panel.users.form.password'))
                    ->password()
                    ->required(),
                Select::make('user.roles')
                    ->label(__('panel.users.form.role'))
                    ->relationship('roles', 'name')
                    ->multiple()
                    ->preload()
                    ->searchable(),
            ]);
    }
}
