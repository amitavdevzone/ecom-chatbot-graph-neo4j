<?php

namespace App\Models;

use Database\Factories\CustomerFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'email'])]
class Customer extends Model
{
    /** @use HasFactory<CustomerFactory> */
    use HasFactory;

    public const UPDATED_AT = null;

    /**
     * @return HasMany<Order, $this>
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /**
     * @return HasMany<ProductLike, $this>
     */
    public function productLikes(): HasMany
    {
        return $this->hasMany(ProductLike::class);
    }

    /**
     * @return BelongsToMany<Product, $this>
     */
    public function likedProducts(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_likes');
    }
}
