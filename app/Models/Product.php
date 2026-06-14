<?php

namespace App\Models;

use App\Enums\ProductCategory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'category', 'price', 'description'])]
class Product extends Model
{
    public const UPDATED_AT = null;

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
