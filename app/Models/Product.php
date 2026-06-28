<?php

namespace App\Models;

use App\Enums\ProductCategory;
use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Scout\Searchable;

#[Fillable(['name', 'category', 'price', 'description'])]
class Product extends Model
{
    /** @use HasFactory<ProductFactory> */
    use HasFactory, Searchable;

    /**
     * @return HasMany<OrderItem, $this>
     */
    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * @return HasMany<ProductLike, $this>
     */
    public function productLikes(): HasMany
    {
        return $this->hasMany(ProductLike::class);
    }

    /**
     * @return HasMany<Offer, $this>
     */
    public function triggerOffers(): HasMany
    {
        return $this->hasMany(Offer::class, 'trigger_product_id');
    }

    /**
     * @return array<string, mixed>
     */
    public function toSearchableArray(): array
    {
        return [
            'id' => (string) $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'category' => $this->category->value,
            'price' => (float) $this->price,
            'created_at' => $this->created_at->timestamp,
            'updated_at' => $this->updated_at?->timestamp,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function typesenseCollectionSchema(): array
    {
        return [
            'name' => 'products',
            'fields' => [
                ['name' => 'id', 'type' => 'string'],
                ['name' => 'name', 'type' => 'string'],
                ['name' => 'description', 'type' => 'string', 'optional' => true],
                ['name' => 'category', 'type' => 'string', 'facet' => true],
                ['name' => 'price', 'type' => 'float'],
                ['name' => 'created_at', 'type' => 'int32'],
                ['name' => 'updated_at', 'type' => 'int32', 'optional' => true],
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'category' => ProductCategory::class,
            'price' => 'decimal:2',
        ];
    }
}
