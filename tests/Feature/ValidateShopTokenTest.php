<?php

use App\Services\GraphSyncService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->mock(GraphSyncService::class)->shouldIgnoreMissing();
    config(['services.laravel-shop-token.access_token' => 'test-secret']);
});

it('rejects requests with a missing token', function () {
    $this->postJson('/api/order-status')
        ->assertUnauthorized()
        ->assertJson(['message' => 'Unauthorized']);
});

it('rejects requests with a wrong token', function () {
    $this->postJson('/api/order-status', [], ['X-Laravel-Auth-Token' => 'wrong-token'])
        ->assertUnauthorized()
        ->assertJson(['message' => 'Unauthorized']);
});

it('allows requests with the correct token to reach the controller', function () {
    $this->postJson('/api/order-status', [], ['X-Laravel-Auth-Token' => 'test-secret'])
        ->assertUnprocessable(); // 422 from validation — middleware passed
});
