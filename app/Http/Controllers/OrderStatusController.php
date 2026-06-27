<?php

namespace App\Http\Controllers;

use App\Http\Resources\OrderResource;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class OrderStatusController extends Controller
{
    public function __invoke(Request $request): AnonymousResourceCollection
    {
        $request->validate(['email' => ['required', 'email']]);

        $customer = Customer::where('email', $request->email)
            ->firstOrFail();

        $orders = $customer->orders()
            ->with('orderItems.product')
            ->latest()
            ->limit(5)
            ->get();

        return OrderResource::collection($orders);
    }
}
