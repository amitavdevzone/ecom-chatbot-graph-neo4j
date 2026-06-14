<?php

use App\Neuron\Tools\LikedAlsoBoughtTool;
use App\Services\Neo4jService;
use Laudis\Neo4j\Databags\SummarizedResult;
use Laudis\Neo4j\Types\CypherMap;

it('returns products purchased by customers who liked the given product', function () {
    $records = new SummarizedResult(
        summary: $summary,
        iterable: [
            new CypherMap(['other.name' => 'Leather Case', 'frequency' => 4]),
            new CypherMap(['other.name' => '65W GaN Charger', 'frequency' => 2]),
        ],
    );

    $neo4j = Mockery::mock(Neo4jService::class);
    $neo4j->shouldReceive('run')
        ->once()
        ->with(Mockery::type('string'), ['productId' => 1])
        ->andReturn($records);

    app()->instance(Neo4jService::class, $neo4j);

    $tool = new LikedAlsoBoughtTool;
    $tool->setInputs(['product_id' => 1]);
    $tool->execute();

    expect(json_decode($tool->getResult(), true))->toBe([
        'product_id' => 1,
        'recommendations' => [
            ['name' => 'Leather Case', 'frequency' => 4],
            ['name' => '65W GaN Charger', 'frequency' => 2],
        ],
    ]);
});

it('returns a message when no like-to-purchase data exists', function () {
    $records = new SummarizedResult(summary: $summary, iterable: []);

    $neo4j = Mockery::mock(Neo4jService::class);
    $neo4j->shouldReceive('run')
        ->once()
        ->with(Mockery::type('string'), ['productId' => 99])
        ->andReturn($records);

    app()->instance(Neo4jService::class, $neo4j);

    $tool = new LikedAlsoBoughtTool;
    $tool->setInputs(['product_id' => 99]);
    $tool->execute();

    expect(json_decode($tool->getResult(), true))->toBe([
        'product_id' => 99,
        'recommendations' => [],
        'message' => 'No like-to-purchase data found for this product.',
    ]);
});
