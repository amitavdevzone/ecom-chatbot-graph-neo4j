<?php

use App\Support\RunnableCypher;

it('inlines integer parameters for manual neo4j execution', function () {
    $cypher = <<<'CYPHER'
        MATCH (c:Customer)-[:PURCHASED]->(p:Product {id: $productId})
        WHERE other.id <> $productId
        CYPHER;

    expect(RunnableCypher::format($cypher, ['productId' => 42]))
        ->toContain('{id: 42}')
        ->toContain('<> 42');
});

it('quotes string parameters', function () {
    expect(RunnableCypher::literal("O'Brien"))->toBe("'O\\'Brien'");
});
