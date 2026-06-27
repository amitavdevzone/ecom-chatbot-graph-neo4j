<?php

use App\Models\Product;
use App\Services\GraphSyncService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->mock(GraphSyncService::class)->shouldIgnoreMissing();
    config(['services.laravel-shop-token.access_token' => 'test-secret']);
});

it('returns matching products with correct fields for a valid query', function () {
    $product = Product::factory()->create([
        'name' => 'Samsung Galaxy A54',
        'description' => 'A great android phone',
        'price' => '349.99',
    ]);

    $this->postJson('/api/products/search', ['query' => 'Samsung'], ['X-Laravel-Auth-Token' => 'test-secret'])
        ->assertOk()
        ->assertJsonStructure(['data' => ['*' => ['id', 'name', 'price', 'description']]])
        ->assertJsonPath('data.0.id', $product->id)
        ->assertJsonPath('data.0.name', 'Samsung Galaxy A54');
});

it('returns a product matched by description only', function () {
    $product = Product::factory()->create([
        'name' => 'Generic Device',
        'description' => 'Perfect for mobile gaming sessions',
    ]);
    Product::factory()->create(['name' => 'Unrelated', 'description' => 'Nothing special']);

    $response = $this->postJson('/api/products/search', ['query' => 'mobile gaming'], ['X-Laravel-Auth-Token' => 'test-secret']);

    $response->assertOk();
    expect($response->json('data'))->toHaveCount(1)
        ->and($response->json('data.0.id'))->toBe($product->id);
});

it('returns an empty data array when no products match', function () {
    Product::factory()->create(['name' => 'Wireless Charger', 'description' => 'Fast charging']);

    $response = $this->postJson('/api/products/search', ['query' => 'zzznothing'], ['X-Laravel-Auth-Token' => 'test-secret']);

    $response->assertOk();
    expect($response->json('data'))->toBeEmpty();
});

it('caps results at 10 when more than 10 products match', function () {
    Product::factory()->count(15)->create(['name' => 'Matching Phone', 'description' => 'android device']);

    $response = $this->postJson('/api/products/search', ['query' => 'Matching'], ['X-Laravel-Auth-Token' => 'test-secret']);

    $response->assertOk();
    expect($response->json('data'))->toHaveCount(10);
});

it('returns 422 when query field is missing', function () {
    $this->postJson('/api/products/search', [], ['X-Laravel-Auth-Token' => 'test-secret'])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['query']);
});

it('returns 422 when query is an empty string', function () {
    $this->postJson('/api/products/search', ['query' => ''], ['X-Laravel-Auth-Token' => 'test-secret'])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['query']);
});

it('returns 401 when auth token is missing', function () {
    $this->postJson('/api/products/search', ['query' => 'phone'])
        ->assertUnauthorized();
});

it('returns 401 when auth token is invalid', function () {
    $this->postJson('/api/products/search', ['query' => 'phone'], ['X-Laravel-Auth-Token' => 'wrong-token'])
        ->assertUnauthorized();
});
