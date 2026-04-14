<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Qameta\Allure\Attribute\DisplayName;
use Qameta\Allure\Attribute\ParentSuite;
use Qameta\Allure\Attribute\SubSuite;
use Qameta\Allure\Attribute\Suite;
use Tests\TestCase;

#[ParentSuite('PHPUnit')]
#[Suite(AuthTest::class)]
#[SubSuite('Authentication')]
class AuthTest extends TestCase
{
    use RefreshDatabase;

    #[DisplayName("User can register")]
    public function test_user_can_register(): void
    {
        $password = env('TEST_USER_PASSWORD')
            ?? throw new \RuntimeException('TEST_USER_PASSWORD is not set. Add it to your .env file.');

        $response = $this->postJson('/api/auth/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => $password,
            'password_confirmation' => $password,
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure(['user', 'token']);
    }

    #[DisplayName("User can login")]
    public function test_user_can_login(): void
    {
        $password = env('TEST_USER_PASSWORD')
            ?? throw new \RuntimeException('TEST_USER_PASSWORD is not set. Add it to your .env file.');
        $user = User::factory()->create([
            'password' => bcrypt($password),
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => $user->email,
            'password' => $password,
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure(['user', 'token']);
    }

    #[DisplayName("Login fails with wrong credentials")]
    public function test_login_fails_with_wrong_credentials(): void
    {
        $email = env('TEST_WRONG_EMAIL')
            ?? throw new \RuntimeException('TEST_WRONG_EMAIL is not set. Add it to your .env file.');
        $password = env('TEST_WRONG_PASSWORD')
            ?? throw new \RuntimeException('TEST_WRONG_PASSWORD is not set. Add it to your .env file.');

        $response = $this->postJson('/api/auth/login', [
            'email' => $email,
            'password' => $password,
        ]);

        $response->assertStatus(401);
    }

    #[DisplayName("User can logout")]
    public function test_user_can_logout(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('auth_token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/auth/logout');

        $response->assertStatus(200);
    }

    #[DisplayName("Authenticated user can fetch their profile")]
    public function test_authenticated_user_can_fetch_their_profile(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('auth_token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/auth/me');

        $response->assertStatus(200)
            ->assertJsonFragment(['email' => $user->email]);
    }
}
