<?php

use App\Enums\ProductCategory;
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
        'description' => 'A great mobile phone',
        'price' => '349.99',
    ]);

    $this->postJson('/api/products/search', ['query' => 'mobile'], ['X-Laravel-Auth-Token' => 'test-secret'])
        ->assertOk()
        ->assertJsonStructure(['data' => ['*' => ['id', 'name', 'price', 'description']]])
        ->assertJsonPath('data.0.id', $product->id)
        ->assertJsonPath('data.0.name', 'Samsung Galaxy A54');
});

it('passes the query through without a hardcoded override', function () {
    $laptop = Product::factory()->create([
        'name' => 'Dell XPS Laptop',
        'description' => 'A powerful laptop for professionals',
    ]);
    Product::factory()->create([
        'name' => 'Samsung Mobile',
        'description' => 'A great mobile phone',
    ]);

    $response = $this->postJson('/api/products/search', ['query' => 'laptop'], ['X-Laravel-Auth-Token' => 'test-secret'])
        ->assertOk();

    $ids = collect($response->json('data'))->pluck('id');
    expect($ids)->toContain($laptop->id);
});

it('returns a product matched by description only', function () {
    $product = Product::factory()->create([
        'name' => 'Generic Device',
        'description' => 'Perfect for mobile gaming sessions',
    ]);
    Product::factory()->create(['name' => 'Unrelated', 'description' => 'Nothing special', 'category' => ProductCategory::Charger]);

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
    Product::factory()->count(15)->create(['name' => 'Mobile Phone', 'description' => 'android mobile device']);

    $response = $this->postJson('/api/products/search', ['query' => 'mobile'], ['X-Laravel-Auth-Token' => 'test-secret']);

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

it('accepts filter_by without a validation error', function () {
    Product::factory()->create(['name' => 'Wireless Headphones', 'description' => 'noise cancelling headphones']);

    $this->postJson('/api/products/search', [
        'query' => 'headphones',
        'filter_by' => 'price:<500',
    ], ['X-Laravel-Auth-Token' => 'test-secret'])
        ->assertOk()
        ->assertJsonStructure(['data']);
});

it('accepts sort_by without a validation error', function () {
    Product::factory()->create(['name' => 'Gaming Mouse', 'description' => 'precision gaming mouse']);

    $this->postJson('/api/products/search', [
        'query' => 'mouse',
        'sort_by' => 'price:asc',
    ], ['X-Laravel-Auth-Token' => 'test-secret'])
        ->assertOk()
        ->assertJsonStructure(['data']);
});

it('accepts both filter_by and sort_by without a validation error', function () {
    Product::factory()->create(['name' => 'USB-C Charger', 'description' => 'fast charging USB-C charger']);

    $this->postJson('/api/products/search', [
        'query' => 'charger',
        'filter_by' => 'price:<100',
        'sort_by' => 'price:asc',
    ], ['X-Laravel-Auth-Token' => 'test-secret'])
        ->assertOk()
        ->assertJsonStructure(['data']);
});

it('performs a search with only query when filter_by and sort_by are omitted', function () {
    $product = Product::factory()->create([
        'name' => 'Bluetooth Speaker',
        'description' => 'portable bluetooth speaker',
    ]);

    $response = $this->postJson('/api/products/search', [
        'query' => 'bluetooth',
    ], ['X-Laravel-Auth-Token' => 'test-secret'])
        ->assertOk()
        ->assertJsonStructure(['data']);

    $ids = collect($response->json('data'))->pluck('id');
    expect($ids)->toContain($product->id);
});
