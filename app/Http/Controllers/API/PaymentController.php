<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\Payment\StorePaymentRequest;
use App\Http\Resources\PaymentResource;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class PaymentController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $payments = Payment::where('user_id', Auth::id())->latest()->get();

        return PaymentResource::collection($payments);
    }

    public function store(StorePaymentRequest $request): JsonResponse
    {
        $order = Order::where('id', $request->order_id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        if ($order->status === 'completed') {
            return response()->json(['message' => 'Order is already paid'], 422);
        }

        // Simulate payment processing
        $success = (bool) rand(0, 1);

        $payment = Payment::create([
            'order_id'       => $order->id,
            'user_id'        => Auth::id(),
            'amount'         => $order->total,
            'status'         => $success ? 'successful' : 'failed',
            'payment_method' => $request->payment_method ?? 'credit_card',
            'transaction_id' => $success ? Str::uuid() : null,
        ]);

        if ($success) {
            $order->update(['status' => 'completed']);
        }

        return response()->json([
            'payment' => new PaymentResource($payment),
            'message' => $success ? 'Payment successful' : 'Payment failed',
        ], $success ? 201 : 422);
    }

    public function show(int $id): PaymentResource
    {
        $payment = Payment::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        return new PaymentResource($payment);
    }
}
