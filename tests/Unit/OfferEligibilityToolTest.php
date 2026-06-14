<?php

use App\Neuron\Tools\OfferEligibilityTool;
use App\Services\Neo4jService;
use Laudis\Neo4j\Databags\SummarizedResult;
use Laudis\Neo4j\Types\CypherMap;

it('returns offers for products the customer liked but never purchased', function () {
    $records = new SummarizedResult(
        summary: $summary,
        iterable: [
            new CypherMap(['product' => 'iPhone 15', 'offer' => '20% off']),
            new CypherMap(['product' => 'AirPods Pro', 'offer' => '20% off']),
        ],
    );

    $neo4j = Mockery::mock(Neo4jService::class);
    $neo4j->shouldReceive('run')
        ->once()
        ->with(Mockery::type('string'), ['customerId' => 1])
        ->andReturn($records);

    app()->instance(Neo4jService::class, $neo4j);

    $tool = new OfferEligibilityTool;
    $tool->setInputs(['customer_id' => 1]);
    $tool->execute();

    expect(json_decode($tool->getResult(), true))->toBe([
        'customer_id' => 1,
        'offers' => [
            ['product' => 'iPhone 15', 'offer' => '20% off'],
            ['product' => 'AirPods Pro', 'offer' => '20% off'],
        ],
    ]);
});

it('returns a message when no liked-but-not-purchased products exist', function () {
    $records = new SummarizedResult(summary: $summary, iterable: []);

    $neo4j = Mockery::mock(Neo4jService::class);
    $neo4j->shouldReceive('run')
        ->once()
        ->with(Mockery::type('string'), ['customerId' => 99])
        ->andReturn($records);

    app()->instance(Neo4jService::class, $neo4j);

    $tool = new OfferEligibilityTool;
    $tool->setInputs(['customer_id' => 99]);
    $tool->execute();

    expect(json_decode($tool->getResult(), true))->toBe([
        'customer_id' => 99,
        'offers' => [],
        'message' => 'No liked-but-not-purchased products found for this customer.',
    ]);
});
