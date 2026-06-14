<?php

namespace App\Observers;

use App\Models\ProductLike;
use App\Services\GraphSyncService;

class ProductLikeObserver
{
    public function __construct(
        private readonly GraphSyncService $graphSync,
    ) {}

    public function created(ProductLike $productLike): void
    {
        $this->graphSync->syncLike($productLike);
    }
}
