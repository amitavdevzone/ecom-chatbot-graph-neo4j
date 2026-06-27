<?php

namespace Database\Seeders;

use App\Enums\ProductCategory;
use App\Models\Customer;
use App\Models\Offer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductLike;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class MobileStoreSeeder extends Seeder
{
    public function run(): void
    {
        $this->truncateStoreData();

        $products = $this->seedProducts();
        $customers = $this->seedCustomers();
        $this->seedOffers($products);
        $this->seedOrders($customers, $products);
        $this->seedProductLikes($customers, $products);
    }

    private function truncateStoreData(): void
    {
        DB::statement('TRUNCATE TABLE order_items, product_likes, orders, offers, products, customers RESTART IDENTITY CASCADE');
    }

    /**
     * @return array<string, Product>
     */
    private function seedProducts(): array
    {
        $catalog = [
            ProductCategory::Mobile->value => [
                ['name' => 'iPhone 15', 'price' => 799.00, 'description' => 'Apple iPhone 15, 128GB.'],
                ['name' => 'Samsung Galaxy S24', 'price' => 849.00, 'description' => 'Samsung flagship smartphone.'],
                ['name' => 'Google Pixel 8', 'price' => 699.00, 'description' => 'Google Pixel 8 with pure Android.'],
            ],
            ProductCategory::Case->value => [
                ['name' => 'Leather Case for iPhone 15', 'price' => 49.99, 'description' => 'Premium leather case for iPhone 15.'],
                ['name' => 'Clear Case for Galaxy S24', 'price' => 24.99, 'description' => 'Transparent protective case for S24.'],
                ['name' => 'Silicone Case for Pixel 8', 'price' => 29.99, 'description' => 'Soft-touch silicone case for Pixel 8.'],
            ],
            ProductCategory::Charger->value => [
                ['name' => '65W GaN Charger', 'price' => 39.99, 'description' => 'Compact USB-C GaN fast charger.'],
                ['name' => 'MagSafe Charger', 'price' => 34.99, 'description' => 'Apple MagSafe wireless charger.'],
                ['name' => 'Dual Port Car Charger', 'price' => 19.99, 'description' => 'USB-A and USB-C car charger.'],
            ],
            ProductCategory::Earphone->value => [
                ['name' => 'AirPods Pro', 'price' => 249.00, 'description' => 'Apple AirPods Pro with ANC.'],
                ['name' => 'Galaxy Buds 2', 'price' => 149.00, 'description' => 'Samsung Galaxy Buds 2 earbuds.'],
                ['name' => 'Pixel Buds Pro', 'price' => 199.00, 'description' => 'Google Pixel Buds Pro.'],
            ],
            ProductCategory::Cable->value => [
                ['name' => 'USB-C Braided Cable', 'price' => 14.99, 'description' => '2m braided USB-C cable.'],
                ['name' => 'Lightning Cable', 'price' => 19.99, 'description' => 'Apple MFi Lightning cable.'],
                ['name' => 'USB-C to USB-C Cable', 'price' => 12.99, 'description' => '1m USB-C charging cable.'],
            ],
        ];

        $products = [];

        foreach ($catalog as $category => $items) {
            foreach ($items as $item) {
                $product = Product::query()->create([
                    'name' => $item['name'],
                    'category' => $category,
                    'price' => $item['price'],
                    'description' => $item['description'],
                ]);

                $products[$item['name']] = $product;
            }
        }

        return $products;
    }

    /**
     * @return Collection<int, Customer>
     */
    private function seedCustomers(): Collection
    {
        $demoCustomers = [
            ['name' => 'Alice Chen', 'email' => 'alice@demo.store'],
            ['name' => 'Bob Martinez', 'email' => 'bob@demo.store'],
            ['name' => 'Carol Singh', 'email' => 'carol@demo.store'],
            ['name' => 'Amitav Roy', 'email' => 'reachme@amitavroy.com'],
        ];

        $customers = collect($demoCustomers)->map(
            fn (array $data): Customer => Customer::query()->create($data),
        );

        $customers = $customers->merge(
            Customer::factory()
                ->count(22)
                ->create(),
        );

        return $customers;
    }

    /**
     * @param  array<string, Product>  $products
     */
    private function seedOffers(array $products): void
    {
        Offer::query()->create([
            'title' => '20% off Case',
            'description' => 'Liked a mobile but have not purchased a case yet — nudge to convert.',
            'discount_percent' => 20,
            'trigger_product_id' => null,
            'trigger_category' => ProductCategory::Mobile,
            'min_purchase_count' => null,
        ]);

        Offer::query()->create([
            'title' => 'Bundle Deal: Charger + Cable',
            'description' => 'Purchased a mobile and earphones — cross-sell charger and cable bundle.',
            'discount_percent' => 15,
            'trigger_product_id' => $products['iPhone 15']->id,
            'trigger_category' => ProductCategory::Earphone,
            'min_purchase_count' => null,
        ]);

        Offer::query()->create([
            'title' => 'Loyal Audio Fan Reward',
            'description' => 'Purchased from the earphone category three or more times.',
            'discount_percent' => 25,
            'trigger_product_id' => null,
            'trigger_category' => ProductCategory::Earphone,
            'min_purchase_count' => 3,
        ]);
    }

    /**
     * @param  Collection<int, Customer>  $customers
     * @param  array<string, Product>  $products
     */
    private function seedOrders(Collection $customers, array $products): void
    {
        $alice = $customers->firstWhere('email', 'alice@demo.store');
        $bob = $customers->firstWhere('email', 'bob@demo.store');
        $carol = $customers->firstWhere('email', 'carol@demo.store');

        $this->createOrder($alice, [
            [$products['iPhone 15'], 1],
            [$products['Leather Case for iPhone 15'], 1],
            [$products['AirPods Pro'], 1],
        ]);

        $this->createOrder($alice, [
            [$products['USB-C Braided Cable'], 2],
        ]);

        $this->createOrder($bob, [
            [$products['Samsung Galaxy S24'], 1],
            [$products['Clear Case for Galaxy S24'], 1],
            [$products['Galaxy Buds 2'], 1],
            [$products['65W GaN Charger'], 1],
        ]);

        for ($i = 0; $i < 3; $i++) {
            $this->createOrder($carol, [
                [$products['Galaxy Buds 2'], 1],
            ]);
        }

        $this->createOrder($carol, [
            [$products['Google Pixel 8'], 1],
            [$products['Pixel Buds Pro'], 1],
        ]);

        $bundleTemplates = [
            ['iPhone 15', 'Leather Case for iPhone 15', 'MagSafe Charger'],
            ['Samsung Galaxy S24', 'Clear Case for Galaxy S24', 'Galaxy Buds 2'],
            ['Google Pixel 8', 'Silicone Case for Pixel 8', 'Pixel Buds Pro'],
            ['iPhone 15', 'AirPods Pro', 'Lightning Cable'],
            ['65W GaN Charger', 'USB-C Braided Cable'],
            ['MagSafe Charger', 'Lightning Cable'],
        ];

        $otherCustomers = $customers->reject(
            fn (Customer $customer): bool => in_array($customer->email, ['alice@demo.store', 'bob@demo.store', 'carol@demo.store'], true),
        );

        foreach ($otherCustomers as $customer) {
            $orderCount = fake()->numberBetween(1, 3);

            for ($i = 0; $i < $orderCount; $i++) {
                if (fake()->boolean(40)) {
                    $template = fake()->randomElement($bundleTemplates);
                    $lines = collect($template)
                        ->map(fn (string $name): array => [$products[$name], fake()->numberBetween(1, 2)])
                        ->all();
                    $this->createOrder($customer, $lines);

                    continue;
                }

                $randomProducts = $this->randomProducts($products, fake()->numberBetween(1, min(3, count($products))))
                    ->map(fn (Product $product): array => [$product, fake()->numberBetween(1, 2)])
                    ->values()
                    ->all();

                $this->createOrder($customer, $randomProducts);
            }
        }
    }

    /**
     * @param  array<int, array{0: Product, 1: int}>  $lines
     */
    private function createOrder(Customer $customer, array $lines): Order
    {
        $total = collect($lines)->sum(
            fn (array $line): float => (float) $line[0]->price * $line[1],
        );

        $order = Order::query()->create([
            'customer_id' => $customer->id,
            'total_amount' => $total,
        ]);

        foreach ($lines as [$product, $quantity]) {
            OrderItem::query()->create([
                'order_id' => $order->id,
                'product_id' => $product->id,
                'quantity' => $quantity,
                'price' => $product->price,
            ]);
        }

        return $order;
    }

    /**
     * @param  Collection<int, Customer>  $customers
     * @param  array<string, Product>  $products
     */
    private function seedProductLikes(Collection $customers, array $products): void
    {
        $alice = $customers->firstWhere('email', 'alice@demo.store');
        $bob = $customers->firstWhere('email', 'bob@demo.store');

        $this->likeProduct($bob, $products['iPhone 15']);
        $this->likeProduct($bob, $products['Google Pixel 8']);

        $this->likeProduct($alice, $products['Google Pixel 8']);

        $mobileProducts = collect($products)->filter(
            fn (Product $product): bool => $product->category === ProductCategory::Mobile,
        );

        $otherCustomers = $customers->reject(
            fn (Customer $customer): bool => in_array($customer->email, ['alice@demo.store', 'bob@demo.store', 'carol@demo.store'], true),
        );

        foreach ($otherCustomers as $customer) {
            $likesCount = fake()->numberBetween(1, 4);
            $likedProducts = $this->randomProducts($products, min($likesCount, count($products)));

            foreach ($likedProducts as $product) {
                $this->likeProduct($customer, $product);
            }

            if (fake()->boolean(60)) {
                $unpurchasedMobile = $mobileProducts->first(
                    fn (Product $product): bool => ! $this->customerPurchasedProduct($customer, $product),
                );

                if ($unpurchasedMobile !== null) {
                    $this->likeProduct($customer, $unpurchasedMobile);
                }
            }
        }
    }

    /**
     * @param  array<string, Product>  $products
     * @return Collection<int, Product>
     */
    private function randomProducts(array $products, int $count): Collection
    {
        $picked = collect($products)->random($count);

        return collect($picked instanceof Product ? [$picked] : $picked)
            ->unique(fn (Product $product): int => $product->id)
            ->values();
    }

    private function likeProduct(Customer $customer, Product $product): void
    {
        ProductLike::query()->firstOrCreate([
            'customer_id' => $customer->id,
            'product_id' => $product->id,
        ]);
    }

    private function customerPurchasedProduct(Customer $customer, Product $product): bool
    {
        return OrderItem::query()
            ->where('product_id', $product->id)
            ->whereHas('order', fn ($query) => $query->where('customer_id', $customer->id))
            ->exists();
    }
}
