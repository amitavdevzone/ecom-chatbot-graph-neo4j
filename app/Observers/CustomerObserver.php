<?php

namespace App\Observers;

use App\Models\Customer;
use App\Services\GraphSyncService;

class CustomerObserver
{
    public function __construct(
        private readonly GraphSyncService $graphSync,
    ) {}

    public function created(Customer $customer): void
    {
        $this->graphSync->syncCustomer($customer);
    }
}
