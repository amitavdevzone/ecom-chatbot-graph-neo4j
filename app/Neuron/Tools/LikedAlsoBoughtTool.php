<?php

namespace App\Neuron\Tools;

use NeuronAI\Tools\PropertyType;
use NeuronAI\Tools\ToolProperty;

class LikedAlsoBoughtTool extends Neo4jRecommendationTool
{
    private const QUERY = <<<'CYPHER'
        MATCH (c:Customer)-[:LIKED]->(p:Product {id: $productId})
        MATCH (c)-[:PURCHASED]->(other:Product)
        RETURN other.name, COUNT(*) AS frequency
        ORDER BY frequency DESC
        LIMIT 5
        CYPHER;

    public function __construct()
    {
        parent::__construct(
            'liked_also_bought',
            'Find products that customers who liked a given product also purchased (like-to-purchase signal).',
        );
    }

    /**
     * @return ToolProperty[]
     */
    protected function properties(): array
    {
        return [
            new ToolProperty(
                name: 'product_id',
                type: PropertyType::INTEGER,
                description: 'The ID of the liked product to find related purchase recommendations for.',
                required: true,
            ),
        ];
    }

    /**
     * @return array{product_id: int, recommendations: list<array{name: mixed, frequency: int}>, message?: string}
     */
    public function __invoke(int $product_id): array
    {
        $result = $this->runCypher(self::QUERY, [
            'productId' => $product_id,
        ]);

        $recommendations = [];

        foreach ($result as $record) {
            $recommendations[] = [
                'name' => $record['other.name'],
                'frequency' => (int) $record['frequency'],
            ];
        }

        if ($recommendations === []) {
            return [
                'product_id' => $product_id,
                'recommendations' => [],
                'message' => 'No like-to-purchase data found for this product.',
            ];
        }

        return [
            'product_id' => $product_id,
            'recommendations' => $recommendations,
        ];
    }
}
