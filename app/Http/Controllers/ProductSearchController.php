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
        $request->validate([
            'query' => ['required', 'string'],
            'filter_by' => ['nullable', 'string'],
            'sort_by' => ['nullable', 'string'],
        ]);

        $query = $request->string('query');

        $options = array_filter([
            'filter_by' => $request->input('filter_by'),
            'sort_by' => $request->input('sort_by'),
        ]);

        logger()->info('search data', [
            'query' => $query,
            'filter_by' => $request->input('filter_by'),
            'sort_by' => $request->input('sort_by'),
        ]);

        $builder = Product::search($query);

        if (! empty($options)) {
            $builder->options($options);
        }

        return ProductResource::collection($builder->take(10)->get());
    }
}
