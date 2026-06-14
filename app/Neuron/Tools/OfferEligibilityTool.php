<?php

namespace App\Neuron\Tools;

use NeuronAI\Tools\PropertyType;
use NeuronAI\Tools\ToolProperty;

class OfferEligibilityTool extends Neo4jRecommendationTool
{
    private const QUERY = <<<'CYPHER'
        MATCH (c:Customer {id: $customerId})-[:LIKED]->(p:Product)
        WHERE NOT (c)-[:PURCHASED]->(p)
        RETURN p.name AS product, '20% off' AS offer
        CYPHER;

    public function __construct()
    {
        parent::__construct(
            'offer_eligibility',
            'Find products a customer liked but never purchased, with discount offers to convert interest.',
        );
    }

    /**
     * @return ToolProperty[]
     */
    protected function properties(): array
    {
        return [
            new ToolProperty(
                name: 'customer_id',
                type: PropertyType::INTEGER,
                description: 'The ID of the customer to check for liked-but-not-purchased offer eligibility.',
                required: true,
            ),
        ];
    }

    /**
     * @return array{customer_id: int, offers: list<array{product: mixed, offer: mixed}>, message?: string}
     */
    public function __invoke(int $customer_id): array
    {
        $result = $this->runCypher(self::QUERY, [
            'customerId' => $customer_id,
        ]);

        $offers = [];

        foreach ($result as $record) {
            $offers[] = [
                'product' => $record['product'],
                'offer' => $record['offer'],
            ];
        }

        if ($offers === []) {
            return [
                'customer_id' => $customer_id,
                'offers' => [],
                'message' => 'No liked-but-not-purchased products found for this customer.',
            ];
        }

        return [
            'customer_id' => $customer_id,
            'offers' => $offers,
        ];
    }
}
