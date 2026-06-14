<?php

namespace App\Console\Commands;

use App\Services\GraphSyncService;
use Illuminate\Console\Command;

class SyncToNeo4j extends Command
{
    protected $signature = 'sync:neo4j';

    protected $description = 'Wipe the Neo4j graph and backfill from PostgreSQL';

    public function handle(GraphSyncService $graphSync): int
    {
        $this->components->info('Wiping Neo4j graph...');
        $graphSync->wipeGraph();

        $this->components->info('Syncing customers, products, orders, likes, and offer eligibility...');
        $graphSync->syncAll();

        $this->components->success('Neo4j graph sync complete.');

        return self::SUCCESS;
    }
}
