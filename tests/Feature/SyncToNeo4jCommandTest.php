<?php

use App\Services\GraphSyncService;
use Illuminate\Support\Facades\Artisan;

it('wipes the graph and runs a full backfill', function () {
    $graphSync = Mockery::mock(GraphSyncService::class);
    $graphSync->shouldReceive('wipeGraph')->once()->ordered();
    $graphSync->shouldReceive('syncAll')->once()->ordered();

    $this->app->instance(GraphSyncService::class, $graphSync);

    Artisan::call('sync:neo4j');

    expect(Artisan::output())->toContain('Neo4j graph sync complete');
});
