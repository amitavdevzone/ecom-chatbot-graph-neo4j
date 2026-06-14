<?php

namespace App\Neuron\Tools;

use NeuronAI\Tools\PropertyType;
use NeuronAI\Tools\ToolProperty;

class CustomerPurchaseHistoryTool extends Neo4jRecommendationTool
{
    private const QUERY = <<<'CYPHER'
        MATCH (c:Customer {id: $customerId})-[r:PURCHASED]->(p:Product)
        RETURN p.name AS product,
               p.category AS category,
               r.order_id AS order_id,
               r.purchased_at AS purchased_at,
               r.quantity AS quantity,
               r.unit_price AS unit_price
        ORDER BY r.purchased_at DESC
        CYPHER;

    public function __construct()
    {
        parent::__construct(
            'customer_purchase_history',
            'Fetch a customer\'s purchase history from the graph for recommendation context.',
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
                description: 'The ID of the customer whose purchase history to load.',
                required: true,
            ),
        ];
    }

    /**
     * @return array{customer_id: int, purchases: list<array{product: mixed, category: mixed, order_id: int, purchased_at: mixed, quantity: int, unit_price: float}>, message?: string}
     */
    public function __invoke(int $customer_id): array
    {
        $result = $this->runCypher(self::QUERY, [
            'customerId' => $customer_id,
        ]);

        $purchases = [];

        foreach ($result as $record) {
            $purchases[] = [
                'product' => $record['product'],
                'category' => $record['category'],
                'order_id' => (int) $record['order_id'],
                'purchased_at' => $record['purchased_at'],
                'quantity' => (int) $record['quantity'],
                'unit_price' => (float) $record['unit_price'],
            ];
        }

        if ($purchases === []) {
            return [
                'customer_id' => $customer_id,
                'purchases' => [],
                'message' => 'No purchase history found for this customer.',
            ];
        }

        return [
            'customer_id' => $customer_id,
            'purchases' => $purchases,
        ];
    }
}
