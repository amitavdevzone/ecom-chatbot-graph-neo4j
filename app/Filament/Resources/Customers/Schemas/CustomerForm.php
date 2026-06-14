<?php

namespace App\Filament\Resources\Customers\Schemas;

use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class CustomerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                TextInput::make('email')
                    ->label('Email address')
                    ->email()
                    ->required(),
                CheckboxList::make('likedProducts')
                    ->label('Liked products')
                    ->relationship(titleAttribute: 'name')
                    ->columns(2)
                    ->searchable(),
            ]);
    }
}
