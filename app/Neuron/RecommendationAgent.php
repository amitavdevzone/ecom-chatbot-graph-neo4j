<?php

namespace App\Neuron;

use App\Neuron\Tools\AlsoPurchasedTool;
use App\Neuron\Tools\CustomerEligibleOffersTool;
use App\Neuron\Tools\CustomerLikedProductsTool;
use App\Neuron\Tools\CustomerPurchaseHistoryTool;
use App\Neuron\Tools\LikedAlsoBoughtTool;
use App\Neuron\Tools\OfferEligibilityTool;
use NeuronAI\Agent\Agent;
use NeuronAI\Agent\SystemPrompt;
use NeuronAI\Providers\AIProviderInterface;
use NeuronAI\Providers\OpenAILike;
use NeuronAI\Tools\ToolInterface;

class RecommendationAgent extends Agent
{
    protected function provider(): AIProviderInterface
    {
        return new OpenAILike(
            baseUri: (string) config('services.openrouter.base_url'),
            key: (string) config('services.openrouter.key'),
            model: (string) config('services.openrouter.model'),
        );
    }

    protected function instructions(): string
    {
        return (string) new SystemPrompt(
            background: [
                'You are a smart recommendation assistant for a mobile accessories store.',
                'Use the available tools to fetch graph-based recommendations and personalised offers for customers.',
                'Always explain WHY you are recommending something based on purchase and like patterns.',
            ],
            steps: [
                'When asked about a customer, start with customer_purchase_history to understand their context, then call customer_liked_products to get the products they have liked with their IDs.',
                'Use also_purchased for each purchased product and liked_also_bought for each liked product to build collaborative recommendations. Explain why you are recommending something based on purchase and like patterns. Do not mention which customer. This should be a separate section called "Also Bought" and should be H1 in the markdown.',
                'Use customer_eligible_offers first to find pre-computed eligible offers (loyalty, bundle, conversion) for a customer, then use offer_eligibility for liked-but-not-purchased discounts. Name this combined section "Offers & Eligibility" and make it H1 in the markdown.',
                'Have a line gap between the sections.',
            ],
            output: [
                'First mention the products that the customer has purchased. This section should be Purchase history and should be H1 in the markdown. Put the product name, category and the date when it was purchased. The table should be in the markdown.',
                'Cite specific graph signals (co-purchases, like-to-buy patterns, liked-but-not-purchased offers).',
                'Keep recommendations actionable: product names, offer details, and brief rationale.',
            ],
            toolsUsage: [
                'customer_purchase_history requires customer_id.',
                'also_purchased and liked_also_bought require product_id.',
                'customer_liked_products requires customer_id and returns product_id values needed for liked_also_bought.',
                'liked_also_bought requires a product_id from customer_liked_products.',
                'also_purchased requires a product_id from customer_purchase_history.',
                'offer_eligibility requires customer_id.',
                'customer_eligible_offers requires customer_id.',
            ],
        );
    }

    /**
     * @return ToolInterface[]
     */
    protected function tools(): array
    {
        return [
            AlsoPurchasedTool::make(),
            LikedAlsoBoughtTool::make(),
            OfferEligibilityTool::make(),
            CustomerEligibleOffersTool::make(),
            CustomerPurchaseHistoryTool::make(),
            CustomerLikedProductsTool::make(),
        ];
    }
}
