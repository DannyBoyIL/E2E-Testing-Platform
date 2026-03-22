<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentTest extends TestCase
{
    use RefreshDatabase;

    private function actingAsUser(): array
    {
        $user  = User::factory()->create();
        $token = $user->createToken('auth_token')->plainTextToken;

        return [$user, $token];
    }

    public function test_authenticated_user_can_list_their_payments(): void
    {
        [$user, $token] = $this->actingAsUser();
        $order = Order::factory()->create(['user_id' => $user->id]);
        Payment::factory()->count(3)->create([
            'user_id'  => $user->id,
            'order_id' => $order->id,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/payments');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');
    }

    public function test_user_can_process_a_payment_for_their_order(): void
    {
        [$user, $token] = $this->actingAsUser();
        $order = Order::factory()->create(['user_id' => $user->id]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/payments', [
                'order_id'       => $order->id,
                'payment_method' => 'credit_card',
            ]);

        $this->assertContains($response->status(), [201, 422]);

        if ($response->status() === 201) {
            $response->assertJsonStructure(['payment', 'message'])
                ->assertJsonFragment(['message' => 'Payment successful']);
        } else {
            $response->assertJsonStructure(['payment', 'message'])
                ->assertJsonFragment(['message' => 'Payment failed']);
        }
    }

    public function test_user_cannot_pay_for_another_users_order(): void
    {
        [$user, $token] = $this->actingAsUser();
        $otherUser      = User::factory()->create();
        $order          = Order::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/payments', [
                'order_id' => $order->id,
            ]);

        $response->assertStatus(404);
    }

    public function test_user_cannot_pay_for_already_completed_order(): void
    {
        [$user, $token] = $this->actingAsUser();
        $order = Order::factory()->create([
            'user_id' => $user->id,
            'status'  => 'completed',
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/payments', [
                'order_id' => $order->id,
            ]);

        $response->assertStatus(422)
            ->assertJsonFragment(['message' => 'Order is already paid']);
    }

    public function test_authenticated_user_can_view_a_payment(): void
    {
        [$user, $token] = $this->actingAsUser();
        $order   = Order::factory()->create(['user_id' => $user->id]);
        $payment = Payment::factory()->create([
            'user_id'  => $user->id,
            'order_id' => $order->id,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson("/api/payments/{$payment->id}");

        $response->assertStatus(200)
            ->assertJsonFragment(['id' => $payment->id]);
    }

    public function test_unauthenticated_user_cannot_access_payments(): void
    {
        $response = $this->getJson('/api/payments');

        $response->assertStatus(401);
    }
}
