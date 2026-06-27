<?php

namespace App\Http\Controllers;

use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ProductSearchController extends Controller
{
    public function __invoke(Request $request): AnonymousResourceCollection
    {
        $request->validate(['query' => ['required', 'string']]);

        $query = $request->string('query');

        $query = "mobile";

        $products = Product::query()
            ->whereLike('name', "%{$query}%")
            ->orWhereLike('description', "%{$query}%")
            ->limit(10)
            ->get();

        return ProductResource::collection($products);
    }
}
