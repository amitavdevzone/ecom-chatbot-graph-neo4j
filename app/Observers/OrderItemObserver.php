<?php

namespace App\Observers;

use App\Models\OrderItem;
use App\Services\GraphSyncService;

class OrderItemObserver
{
    public function __construct(
        private readonly GraphSyncService $graphSync,
    ) {}

    public function created(OrderItem $orderItem): void
    {
        $this->graphSync->syncPurchase($orderItem->order);
    }
}
