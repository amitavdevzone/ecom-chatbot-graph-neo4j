<?php

namespace App\Observers;

use App\Models\Order;
use App\Services\GraphSyncService;

class OrderObserver
{
    public function __construct(
        private readonly GraphSyncService $graphSync,
    ) {}

    public function created(Order $order): void
    {
        $this->graphSync->syncPurchase($order);
    }
}
