<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Qameta\Allure\Attribute\DisplayName;
use Qameta\Allure\Attribute\ParentSuite;
use Qameta\Allure\Attribute\SubSuite;
use Qameta\Allure\Attribute\Suite;
use Tests\TestCase;

#[ParentSuite('PHPUnit')]
#[Suite(OrderTest::class)]
#[SubSuite('Orders')]
class OrderTest extends TestCase
{
    use RefreshDatabase;

    private function actingAsUser(): array
    {
        $user = User::factory()->create();
        $token = $user->createToken('auth_token')->plainTextToken;

        return [$user, $token];
    }

    #[DisplayName("Authenticated user can list their orders")]
    public function test_authenticated_user_can_list_their_orders(): void
    {
        [$user, $token] = $this->actingAsUser();
        Order::factory()->count(3)->create(['user_id' => $user->id]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/orders');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');
    }

    #[DisplayName("Authenticated user can create an order")]
    public function test_authenticated_user_can_create_an_order(): void
    {
        [$user, $token] = $this->actingAsUser();

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/orders', [
                'total' => 99.99,
                'notes' => 'Test order',
            ]);

        $response->assertStatus(201)
            ->assertJsonFragment(['status' => 'pending'])->assertJsonPath('data.total', '99.99');
    }

    #[DisplayName("Authenticated user can view an order")]
    public function test_authenticated_user_can_view_an_order(): void
    {
        [$user, $token] = $this->actingAsUser();
        $order = Order::factory()->create(['user_id' => $user->id]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson("/api/orders/$order->id");

        $response->assertStatus(200)
            ->assertJsonFragment(['id' => $order->id]);
    }

    #[DisplayName("Authenticated user can update an order")]
    public function test_authenticated_user_can_update_an_order(): void
    {
        [$user, $token] = $this->actingAsUser();
        $order = Order::factory()->create(['user_id' => $user->id]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->putJson("/api/orders/$order->id", [
                'status' => 'completed',
            ]);

        $response->assertStatus(200)
            ->assertJsonFragment(['status' => 'completed']);
    }

    #[DisplayName("Authenticated user can delete an order")]
    public function test_authenticated_user_can_delete_an_order(): void
    {
        [$user, $token] = $this->actingAsUser();
        $order = Order::factory()->create(['user_id' => $user->id]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->deleteJson("/api/orders/$order->id");

        $response->assertStatus(200)
            ->assertJsonFragment(['message' => 'Order deleted successfully']);

        $this->assertDatabaseMissing('orders', ['id' => $order->id]);
    }

    #[DisplayName("User cannot access another users order")]
    public function test_user_cannot_access_another_users_order(): void
    {
        [$user, $token] = $this->actingAsUser();
        $otherUser = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson("/api/orders/$order->id");

        $response->assertStatus(404);
    }

    #[DisplayName("Unauthenticated user cannot access orders")]
    public function test_unauthenticated_user_cannot_access_orders(): void
    {
        $response = $this->getJson('/api/orders');

        $response->assertStatus(401);
    }
}
