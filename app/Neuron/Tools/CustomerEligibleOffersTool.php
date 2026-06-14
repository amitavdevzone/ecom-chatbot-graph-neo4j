<?php

namespace App\Neuron\Tools;

use NeuronAI\Tools\PropertyType;
use NeuronAI\Tools\ToolProperty;

class CustomerEligibleOffersTool extends Neo4jRecommendationTool
{
    private const QUERY = <<<'CYPHER'
        MATCH (c:Customer {id: $customerId})-[e:ELIGIBLE_FOR]->(o:Offer)
        RETURN o.title AS offer, o.discount_percent AS discount, e.reason AS reason
        CYPHER;

    public function __construct()
    {
        parent::__construct(
            'customer_eligible_offers',
            'Find all pre-computed offers a customer is eligible for, including the reason they qualify (e.g. loyalty, bundle, conversion).',
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
                description: 'The ID of the customer to fetch eligible offers for.',
                required: true,
            ),
        ];
    }

    /**
     * @return array{customer_id: int, offers: list<array{offer: mixed, discount: int, reason: mixed}>, message?: string}
     */
    public function __invoke(int $customer_id): array
    {
        $result = $this->runCypher(self::QUERY, [
            'customerId' => $customer_id,
        ]);

        $offers = [];

        foreach ($result as $record) {
            $offers[] = [
                'offer' => $record['offer'],
                'discount' => (int) $record['discount'],
                'reason' => $record['reason'],
            ];
        }

        if ($offers === []) {
            return [
                'customer_id' => $customer_id,
                'offers' => [],
                'message' => 'This customer is not eligible for any pre-computed offers.',
            ];
        }

        return [
            'customer_id' => $customer_id,
            'offers' => $offers,
        ];
    }
}
