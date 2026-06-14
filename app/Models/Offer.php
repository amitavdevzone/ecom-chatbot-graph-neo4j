<?php

namespace App\Models;

use App\Enums\ProductCategory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'title',
    'description',
    'discount_percent',
    'trigger_product_id',
    'trigger_category',
    'min_purchase_count',
])]
class Offer extends Model
{
    public $timestamps = false;

    /**
     * @return BelongsTo<Product, $this>
     */
    public function triggerProduct(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'trigger_product_id');
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'trigger_category' => ProductCategory::class,
        ];
    }
}
