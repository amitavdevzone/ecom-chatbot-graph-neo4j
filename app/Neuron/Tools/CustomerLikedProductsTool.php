<?php

namespace App\Neuron\Tools;

use NeuronAI\Tools\PropertyType;
use NeuronAI\Tools\ToolProperty;

class CustomerLikedProductsTool extends Neo4jRecommendationTool
{
    private const QUERY = <<<'CYPHER'
        MATCH (c:Customer {id: $customerId})-[r:LIKED]->(p:Product)
        RETURN p.id AS product_id, p.name AS product, p.category AS category
        ORDER BY r.liked_at DESC
        CYPHER;

    public function __construct()
    {
        parent::__construct(
            'customer_liked_products',
            'Fetch the products a customer has liked, including their IDs so they can be used with liked_also_bought.',
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
                description: 'The ID of the customer whose liked products to load.',
                required: true,
            ),
        ];
    }

    /**
     * @return array{customer_id: int, liked_products: list<array{product_id: int, product: mixed, category: mixed}>, message?: string}
     */
    public function __invoke(int $customer_id): array
    {
        $result = $this->runCypher(self::QUERY, [
            'customerId' => $customer_id,
        ]);

        $likedProducts = [];

        foreach ($result as $record) {
            $likedProducts[] = [
                'product_id' => (int) $record['product_id'],
                'product' => $record['product'],
                'category' => $record['category'],
            ];
        }

        if ($likedProducts === []) {
            return [
                'customer_id' => $customer_id,
                'liked_products' => [],
                'message' => 'This customer has not liked any products.',
            ];
        }

        return [
            'customer_id' => $customer_id,
            'liked_products' => $likedProducts,
        ];
    }
}
