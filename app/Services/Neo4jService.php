<?php

namespace App\Services;

use Laudis\Neo4j\Authentication\Authenticate;
use Laudis\Neo4j\Client;
use Laudis\Neo4j\ClientBuilder;
use Laudis\Neo4j\Databags\SummarizedResult;

class Neo4jService
{
    private readonly Client $client;

    public function __construct()
    {
        $this->client = $this->createClient();
    }

    public function client(): Client
    {
        return $this->client;
    }

    /**
     * @param  array<string, mixed>  $params
     */
    public function run(string $query, array $params = []): SummarizedResult
    {
        return $this->client->run($query, $params);
    }

    private function createClient(): Client
    {
        return ClientBuilder::create()
            ->withDriver(
                'default',
                config('neo4j.url'),
                Authenticate::basic(
                    config('neo4j.username'),
                    config('neo4j.password'),
                ),
            )
            ->withDefaultDriver('default')
            ->build();
    }
}
