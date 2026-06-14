<?php

namespace App\Filament\Resources\Offers\Schemas;

use App\Enums\ProductCategory;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class OfferForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->required(),
                Textarea::make('description')
                    ->columnSpanFull(),
                TextInput::make('discount_percent')
                    ->required()
                    ->numeric(),
                Select::make('trigger_product_id')
                    ->relationship('triggerProduct', 'name')
                    ->searchable()
                    ->preload(),
                Select::make('trigger_category')
                    ->options(ProductCategory::class),
                TextInput::make('min_purchase_count')
                    ->numeric(),
            ]);
    }
}
