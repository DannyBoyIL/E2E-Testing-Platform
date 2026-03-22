<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\Order\StoreOrderRequest;
use App\Http\Requests\Order\UpdateOrderRequest;
use App\Http\Resources\OrderResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return OrderResource::collection(
            Auth::user()->orders()->latest()->get()
        );
    }

    public function store(StoreOrderRequest $request): OrderResource
    {
        $order = Auth::user()->orders()->create($request->validated());

        return new OrderResource($order);
    }

    public function show(int $id): OrderResource
    {
        $order = Auth::user()->orders()->findOrFail($id);

        return new OrderResource($order);
    }

    public function update(UpdateOrderRequest $request, int $id): OrderResource
    {
        $order = Auth::user()->orders()->findOrFail($id);
        $order->update($request->validated());

        return new OrderResource($order);
    }

    public function destroy(int $id): JsonResponse
    {
        $order = Auth::user()->orders()->findOrFail($id);
        $order->delete();

        return response()->json(['message' => 'Order deleted successfully']);
    }
}
