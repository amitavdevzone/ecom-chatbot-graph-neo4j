<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('typesense:sync-products')]
#[Description('Import all products into the Typesense search index')]
class SyncToTypesense extends Command
{
    public function handle(): int
    {
        $this->components->info('Syncing products to Typesense...');

        $this->call('scout:import', ['model' => 'App\\Models\\Product']);

        $this->components->success('Products synced to Typesense successfully.');

        return self::SUCCESS;
    }
}
