<?php

namespace App\Services;

use App\Enums\ProductCategory;
use App\Models\Customer;
use App\Models\Offer;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductLike;
use Illuminate\Support\Carbon;

class GraphSyncService
{
    public function __construct(
        private readonly Neo4jService $neo4j,
    ) {}

    public function syncCustomer(Customer $customer): void
    {
        $this->neo4j->run(
            <<<'CYPHER'
            MERGE (c:Customer {id: $id})
            SET c.name = $name
            CYPHER,
            [
                'id' => $customer->id,
                'name' => $customer->name,
            ],
        );
    }

    public function syncProduct(Product $product): void
    {
        $this->neo4j->run(
            <<<'CYPHER'
            MERGE (p:Product {id: $id})
            SET p.name = $name,
                p.category = $category,
                p.price = $price
            CYPHER,
            [
                'id' => $product->id,
                'name' => $product->name,
                'category' => $product->category->value,
                'price' => (float) $product->price,
            ],
        );
    }

    public function syncPurchase(Order $order): void
    {
        $order->loadMissing(['orderItems.product', 'customer']);

        $purchasedAt = $order->created_at instanceof Carbon
            ? $order->created_at->toIso8601String()
            : (string) $order->created_at;

        foreach ($order->orderItems as $orderItem) {
            $this->neo4j->run(
                <<<'CYPHER'
                MERGE (c:Customer {id: $customerId})
                MERGE (p:Product {id: $productId})
                MERGE (c)-[r:PURCHASED {order_id: $orderId}]->(p)
                SET r.purchased_at = $purchasedAt,
                    r.quantity = $quantity,
                    r.unit_price = $unitPrice
                CYPHER,
                [
                    'customerId' => $order->customer_id,
                    'productId' => $orderItem->product_id,
                    'orderId' => $order->id,
                    'purchasedAt' => $purchasedAt,
                    'quantity' => $orderItem->quantity,
                    'unitPrice' => (float) $orderItem->price,
                ],
            );
        }
    }

    public function syncLike(ProductLike $like): void
    {
        $likedAt = $like->created_at instanceof Carbon
            ? $like->created_at->toIso8601String()
            : (string) $like->created_at;

        $this->neo4j->run(
            <<<'CYPHER'
            MERGE (c:Customer {id: $customerId})
            MERGE (p:Product {id: $productId})
            MERGE (c)-[r:LIKED]->(p)
            SET r.liked_at = $likedAt
            CYPHER,
            [
                'customerId' => $like->customer_id,
                'productId' => $like->product_id,
                'likedAt' => $likedAt,
            ],
        );
    }

    public function syncCustomerLikes(Customer $customer): void
    {
        $this->neo4j->run(
            'MATCH (c:Customer {id: $customerId})-[r:LIKED]->() DELETE r',
            ['customerId' => $customer->id],
        );

        $customer->productLikes()->each(fn (ProductLike $like) => $this->syncLike($like));
    }

    public function wipeGraph(): void
    {
        $this->neo4j->run('MATCH (n) DETACH DELETE n');
    }

    public function syncAll(): void
    {
        Customer::query()->orderBy('id')->each(
            fn (Customer $customer) => $this->syncCustomer($customer),
        );

        Product::query()->orderBy('id')->each(
            fn (Product $product) => $this->syncProduct($product),
        );

        Order::query()
            ->with(['orderItems.product', 'customer'])
            ->orderBy('id')
            ->each(fn (Order $order) => $this->syncPurchase($order));

        ProductLike::query()->orderBy('id')->each(
            fn (ProductLike $like) => $this->syncLike($like),
        );

        $this->syncOfferEligibility();
    }

    public function syncOfferEligibility(): void
    {
        foreach (Offer::query()->get() as $offer) {
            $this->syncOffer($offer);
        }

        $this->neo4j->run('MATCH ()-[e:ELIGIBLE_FOR]->() DELETE e');

        $computedAt = now()->toIso8601String();

        foreach (Offer::query()->get() as $offer) {
            if ($offer->min_purchase_count !== null) {
                $this->syncLoyaltyOfferEligibility($offer, $computedAt);

                continue;
            }

            if ($offer->trigger_product_id !== null) {
                $this->syncBundleOfferEligibility($offer, $computedAt);

                continue;
            }

            $this->syncConversionOfferEligibility($offer, $computedAt);
        }
    }

    private function syncOffer(Offer $offer): void
    {
        $this->neo4j->run(
            <<<'CYPHER'
            MERGE (o:Offer {id: $id})
            SET o.title = $title,
                o.discount_percent = $discountPercent
            CYPHER,
            [
                'id' => $offer->id,
                'title' => $offer->title,
                'discountPercent' => $offer->discount_percent,
            ],
        );
    }

    private function syncConversionOfferEligibility(Offer $offer, string $computedAt): void
    {
        $this->neo4j->run(
            <<<'CYPHER'
            MATCH (c:Customer)-[:LIKED]->(m:Product {category: $likedCategory})
            WHERE NOT EXISTS {
              MATCH (c)-[:PURCHASED]->(:Product {category: $excludedCategory})
            }
            WITH DISTINCT c
            MATCH (o:Offer {id: $offerId})
            MERGE (c)-[e:ELIGIBLE_FOR]->(o)
            SET e.reason = $reason,
                e.computed_at = $computedAt
            CYPHER,
            [
                'offerId' => $offer->id,
                'likedCategory' => ProductCategory::Mobile->value,
                'excludedCategory' => ProductCategory::Case->value,
                'reason' => 'Liked a mobile but has not purchased a case',
                'computedAt' => $computedAt,
            ],
        );
    }

    private function syncBundleOfferEligibility(Offer $offer, string $computedAt): void
    {
        $this->neo4j->run(
            <<<'CYPHER'
            MATCH (c:Customer)-[:PURCHASED]->(trigger:Product {id: $triggerProductId})
            MATCH (c)-[:PURCHASED]->(e:Product {category: $requiredCategory})
            WITH DISTINCT c
            MATCH (o:Offer {id: $offerId})
            MERGE (c)-[e:ELIGIBLE_FOR]->(o)
            SET e.reason = $reason,
                e.computed_at = $computedAt
            CYPHER,
            [
                'offerId' => $offer->id,
                'triggerProductId' => $offer->trigger_product_id,
                'requiredCategory' => $offer->trigger_category?->value ?? ProductCategory::Earphone->value,
                'reason' => 'Purchased trigger mobile and earphone — bundle cross-sell',
                'computedAt' => $computedAt,
            ],
        );
    }

    private function syncLoyaltyOfferEligibility(Offer $offer, string $computedAt): void
    {
        $this->neo4j->run(
            <<<'CYPHER'
            MATCH (c:Customer)-[r:PURCHASED]->(p:Product {category: $category})
            WITH c, count(r) AS purchaseCount
            WHERE purchaseCount >= $minPurchaseCount
            MATCH (o:Offer {id: $offerId})
            MERGE (c)-[e:ELIGIBLE_FOR]->(o)
            SET e.reason = 'Purchased earphones ' + toString(purchaseCount) + ' times',
                e.computed_at = $computedAt
            CYPHER,
            [
                'offerId' => $offer->id,
                'category' => $offer->trigger_category?->value ?? ProductCategory::Earphone->value,
                'minPurchaseCount' => $offer->min_purchase_count,
                'computedAt' => $computedAt,
            ],
        );
    }
}
