<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    private function actingAsUser(): array
    {
        $user = User::factory()->create();
        $token = $user->createToken('auth_token')->plainTextToken;

        return [$user, $token];
    }

    public function test_authenticated_user_can_list_users(): void
    {
        [$user, $token] = $this->actingAsUser();
        User::factory()->count(3)->create();

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/users');

        $response->assertStatus(200)
            ->assertJsonStructure(['data' => [['id', 'name', 'email']]]);
    }

    public function test_authenticated_user_can_view_a_user(): void
    {
        [$user, $token] = $this->actingAsUser();

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson("/api/users/$user->id");

        $response->assertStatus(200)
            ->assertJsonFragment(['email' => $user->email]);
    }

    public function test_authenticated_user_can_update_a_user(): void
    {
        [$user, $token] = $this->actingAsUser();

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->putJson("/api/users/$user->id", [
                'name' => 'Updated Name',
            ]);

        $response->assertStatus(200)
            ->assertJsonFragment(['name' => 'Updated Name']);
    }

    public function test_authenticated_user_can_delete_a_user(): void
    {
        [$user, $token] = $this->actingAsUser();
        $target = User::factory()->create();

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->deleteJson("/api/users/$target->id");

        $response->assertStatus(200)
            ->assertJsonFragment(['message' => 'User deleted successfully']);

        $this->assertDatabaseMissing('users', ['id' => $target->id]);
    }

    public function test_unauthenticated_user_cannot_access_users(): void
    {
        $response = $this->getJson('/api/users');

        $response->assertStatus(401);
    }
}
