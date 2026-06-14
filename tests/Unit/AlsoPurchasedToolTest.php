<?php

use App\Neuron\Tools\AlsoPurchasedTool;
use App\Services\Neo4jService;
use Laudis\Neo4j\Databags\SummarizedResult;
use Laudis\Neo4j\Types\CypherMap;

it('returns co-purchased products ranked by frequency', function () {
    $records = new SummarizedResult(
        summary: $summary,
        iterable: [
            new CypherMap(['other.name' => 'AirPods Pro', 'frequency' => 5]),
            new CypherMap(['other.name' => 'USB-C Cable', 'frequency' => 3]),
        ],
    );

    $neo4j = Mockery::mock(Neo4jService::class);
    $neo4j->shouldReceive('run')
        ->once()
        ->with(Mockery::type('string'), ['productId' => 1])
        ->andReturn($records);

    app()->instance(Neo4jService::class, $neo4j);

    $tool = new AlsoPurchasedTool;
    $tool->setInputs(['product_id' => 1]);
    $tool->execute();

    expect(json_decode($tool->getResult(), true))->toBe([
        'product_id' => 1,
        'recommendations' => [
            ['name' => 'AirPods Pro', 'frequency' => 5],
            ['name' => 'USB-C Cable', 'frequency' => 3],
        ],
    ]);
});

it('returns a message when no co-purchase data exists', function () {
    $records = new SummarizedResult(summary: $summary, iterable: []);

    $neo4j = Mockery::mock(Neo4jService::class);
    $neo4j->shouldReceive('run')
        ->once()
        ->with(Mockery::type('string'), ['productId' => 99])
        ->andReturn($records);

    app()->instance(Neo4jService::class, $neo4j);

    $tool = new AlsoPurchasedTool;
    $tool->setInputs(['product_id' => 99]);
    $tool->execute();

    expect(json_decode($tool->getResult(), true))->toBe([
        'product_id' => 99,
        'recommendations' => [],
        'message' => 'No co-purchase data found for this product.',
    ]);
});
