<?php

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('calls scout:import for the Product model and reports success', function () {
    Product::factory()->count(3)->create();

    $this->artisan('typesense:sync-products')
        ->assertSuccessful()
        ->expectsOutputToContain('Syncing products to Typesense')
        ->expectsOutputToContain('Products synced to Typesense successfully');
});
