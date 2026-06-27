<?php

use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Services\GraphSyncService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->mock(GraphSyncService::class)->shouldIgnoreMissing();
    config(['services.laravel-shop-token.access_token' => 'test-secret']);
});

it('returns orders with items and product details for a valid email', function () {
    $customer = Customer::factory()->create();
    $product = Product::factory()->create(['name' => 'Test Phone', 'category' => 'mobile']);
    $order = Order::factory()->create(['customer_id' => $customer->id]);
    OrderItem::factory()->create([
        'order_id' => $order->id,
        'product_id' => $product->id,
        'quantity' => 2,
        'price' => '49.99',
    ]);

    $this->postJson('/api/order-status', ['email' => $customer->email], ['X-Laravel-Auth-Token' => 'test-secret'])
        ->assertSuccessful()
        ->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'total_amount',
                    'created_at',
                    'items' => [
                        '*' => [
                            'quantity',
                            'price',
                            'product' => ['name', 'category'],
                        ],
                    ],
                ],
            ],
        ])
        ->assertJsonPath('data.0.items.0.product.name', 'Test Phone')
        ->assertJsonPath('data.0.items.0.quantity', 2);
});

it('returns only the latest 5 orders', function () {
    $customer = Customer::factory()->create();
    Order::factory()->count(7)->create(['customer_id' => $customer->id]);

    $response = $this->postJson('/api/order-status', ['email' => $customer->email], ['X-Laravel-Auth-Token' => 'test-secret']);

    $response->assertSuccessful();
    expect($response->json('data'))->toHaveCount(5);
});

it('returns 404 for an unknown email', function () {
    $this->postJson('/api/order-status', ['email' => 'nobody@example.com'], ['X-Laravel-Auth-Token' => 'test-secret'])
        ->assertNotFound();
});

it('returns 422 when email is missing', function () {
    $this->postJson('/api/order-status', [], ['X-Laravel-Auth-Token' => 'test-secret'])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['email']);
});
