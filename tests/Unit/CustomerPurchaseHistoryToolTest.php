<?php

use App\Neuron\Tools\CustomerPurchaseHistoryTool;
use App\Services\Neo4jService;
use Laudis\Neo4j\Databags\SummarizedResult;
use Laudis\Neo4j\Types\CypherMap;

it('returns purchase history ordered by purchased_at', function () {
    $records = new SummarizedResult(
        summary: $summary,
        iterable: [
            new CypherMap([
                'product' => 'iPhone 15',
                'category' => 'mobile',
                'order_id' => 10,
                'purchased_at' => '2024-06-01T12:00:00+00:00',
                'quantity' => 1,
                'unit_price' => 999,
            ]),
            new CypherMap([
                'product' => 'Leather Case',
                'category' => 'case',
                'order_id' => 10,
                'purchased_at' => '2024-06-01T12:00:00+00:00',
                'quantity' => 1,
                'unit_price' => 49.99,
            ]),
        ],
    );

    $neo4j = Mockery::mock(Neo4jService::class);
    $neo4j->shouldReceive('run')
        ->once()
        ->with(Mockery::type('string'), ['customerId' => 1])
        ->andReturn($records);

    app()->instance(Neo4jService::class, $neo4j);

    $tool = new CustomerPurchaseHistoryTool;
    $tool->setInputs(['customer_id' => 1]);
    $tool->execute();

    expect(json_decode($tool->getResult(), true))->toBe([
        'customer_id' => 1,
        'purchases' => [
            [
                'product' => 'iPhone 15',
                'category' => 'mobile',
                'order_id' => 10,
                'purchased_at' => '2024-06-01T12:00:00+00:00',
                'quantity' => 1,
                'unit_price' => 999,
            ],
            [
                'product' => 'Leather Case',
                'category' => 'case',
                'order_id' => 10,
                'purchased_at' => '2024-06-01T12:00:00+00:00',
                'quantity' => 1,
                'unit_price' => 49.99,
            ],
        ],
    ]);
});

it('returns a message when the customer has no purchases', function () {
    $records = new SummarizedResult(summary: $summary, iterable: []);

    $neo4j = Mockery::mock(Neo4jService::class);
    $neo4j->shouldReceive('run')
        ->once()
        ->with(Mockery::type('string'), ['customerId' => 99])
        ->andReturn($records);

    app()->instance(Neo4jService::class, $neo4j);

    $tool = new CustomerPurchaseHistoryTool;
    $tool->setInputs(['customer_id' => 99]);
    $tool->execute();

    expect(json_decode($tool->getResult(), true))->toBe([
        'customer_id' => 99,
        'purchases' => [],
        'message' => 'No purchase history found for this customer.',
    ]);
});
