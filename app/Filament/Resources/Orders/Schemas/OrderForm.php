<?php

namespace App\Filament\Resources\Orders\Schemas;

use App\Models\Product;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class OrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('customer_id')
                    ->relationship('customer', 'name')
                    ->searchable(['name', 'email'])
                    ->preload()
                    ->required(),
                Repeater::make('orderItems')
                    ->relationship()
                    ->schema([
                        Select::make('product_id')
                            ->relationship('product', 'name')
                            ->searchable()
                            ->required()
                            ->live()
                            ->afterStateUpdated(function (Set $set, ?string $state): void {
                                if ($state === null) {
                                    return;
                                }

                                $set('price', Product::query()->find($state)?->price);
                            }),
                        TextInput::make('quantity')
                            ->numeric()
                            ->required()
                            ->default(1)
                            ->minValue(1),
                        TextInput::make('price')
                            ->numeric()
                            ->required()
                            ->prefix('$'),
                    ])
                    ->columns(3)
                    ->defaultItems(1)
                    ->columnSpanFull()
                    ->addActionLabel('Add product'),
                TextInput::make('total_amount')
                    ->numeric()
                    ->prefix('$')
                    ->default(0)
                    ->disabled()
                    ->dehydrated(),
            ]);
    }
}
