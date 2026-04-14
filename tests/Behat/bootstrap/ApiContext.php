<?php

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\TableNode;
use GuzzleHttp\Client;

class ApiContext implements Context
{
    private Client $client;
    private ?array $responseBody = null;
    private int $statusCode = 0;
    private string $token = '';

    private const BASE_URL = 'http://127.0.0.1:8000';

    public function __construct()
    {
        // Behat runs as a standalone process and does not boot Laravel, so the
        // .env file is never loaded automatically. Load it here so that
        // getenv() can read TEST_* variables the same way env() does in Laravel.
        $root = dirname(__DIR__, 3);
        if (file_exists($root . '/.env')) {
            \Dotenv\Dotenv::createUnsafeImmutable($root)->load();
        }

        $this->client = new Client([
            'base_uri'    => self::BASE_URL,
            'http_errors' => false,
        ]);
    }

    private function headers(): array
    {
        $headers = [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ];
        if ($this->token) {
            $headers['Authorization'] = 'Bearer ' . $this->token;
        }
        return $headers;
    }

    // ─── Hooks ────────────────────────────────────────────────────────────────

    /** @BeforeSuite */
    public static function cleanupBefore(): void
    {
        (new Client(['base_uri' => self::BASE_URL, 'http_errors' => false]))
            ->post('/api/test/cleanup');
    }

    /** @AfterSuite */
    public static function cleanupAfter(): void
    {
        (new Client(['base_uri' => self::BASE_URL, 'http_errors' => false]))
            ->post('/api/test/cleanup');
    }

    // ─── Request steps ───────────────────────────────────────────────────────

    /**
     * @When I register a new test user
     */
    public function iRegisterNewTestUser(): void
    {
        $password = $this->testUserPassword();
        $response = $this->client->post('/api/auth/register', [
            'json'    => [
                'name'                  => 'Behat User',
                'email'                 => 'behatuser_' . time() . '@test.com',
                'password'              => $password,
                'password_confirmation' => $password,
            ],
            'headers' => $this->headers(),
        ]);
        $this->statusCode   = $response->getStatusCode();
        $this->responseBody = json_decode($response->getBody()->getContents(), true);
    }

    /**
     * @When I POST to :endpoint with:
     */
    public function iPostToWith(string $endpoint, TableNode $table): void
    {
        $response = $this->client->post($endpoint, [
            'json' => $table->getRowsHash(),
            'headers' => $this->headers(),
        ]);
        $this->statusCode = $response->getStatusCode();
        $this->responseBody = json_decode($response->getBody()->getContents(), true);
    }

    /**
     * @When I POST to :endpoint
     */
    public function iPostTo(string $endpoint): void
    {
        $response = $this->client->post($endpoint, [
            'headers' => $this->headers(),
        ]);
        $this->statusCode = $response->getStatusCode();
        $this->responseBody = json_decode($response->getBody()->getContents(), true);
    }

    /**
     * @When I GET :endpoint
     */
    public function iGet(string $endpoint): void
    {
        $response = $this->client->get($endpoint, [
            'headers' => $this->headers(),
        ]);
        $this->statusCode = $response->getStatusCode();
        $this->responseBody = json_decode($response->getBody()->getContents(), true);
    }

    // ─── Auth steps ──────────────────────────────────────────────────────────

    // ─── Private helpers ─────────────────────────────────────────────────────

    private function testUserEmail(): string
    {
        return getenv('TEST_USER_EMAIL')
            ?: throw new \RuntimeException('TEST_USER_EMAIL is not set. Add it to your .env file.');
    }

    private function testUserPassword(): string
    {
        return getenv('TEST_USER_PASSWORD')
            ?: throw new \RuntimeException('TEST_USER_PASSWORD is not set. Add it to your .env file.');
    }

    private function testWrongEmail(): string
    {
        return getenv('TEST_WRONG_EMAIL')
            ?: throw new \RuntimeException('TEST_WRONG_EMAIL is not set. Add it to your .env file.');
    }

    private function testWrongPassword(): string
    {
        return getenv('TEST_WRONG_PASSWORD')
            ?: throw new \RuntimeException('TEST_WRONG_PASSWORD is not set. Add it to your .env file.');
    }

    // ─── Auth steps ──────────────────────────────────────────────────────────

    /**
     * @Given I am authenticated as the admin user
     */
    public function iAmAuthenticatedAsAdminUser(): void
    {
        $this->iAmAuthenticatedAs($this->testUserEmail(), $this->testUserPassword());
    }

    /**
     * @When I POST invalid login credentials to :endpoint
     */
    public function iPostInvalidLoginTo(string $endpoint): void
    {
        $email    = $this->testWrongEmail();
        $password = $this->testWrongPassword();
        $response = $this->client->post($endpoint, [
            'json'    => compact('email', 'password'),
            'headers' => $this->headers(),
        ]);
        $this->statusCode   = $response->getStatusCode();
        $this->responseBody = json_decode($response->getBody()->getContents(), true);
    }

    /**
     * @When I POST the admin login to :endpoint
     */
    public function iPostAdminLoginTo(string $endpoint): void
    {
        $email = $this->testUserEmail();
        $password = $this->testUserPassword();
        $response = $this->client->post($endpoint, [
            'json' => compact('email', 'password'),
            'headers' => $this->headers(),
        ]);
        $this->statusCode = $response->getStatusCode();
        $this->responseBody = json_decode($response->getBody()->getContents(), true);
    }

    /**
     * @Given I am authenticated as :email with password :password
     */
    public function iAmAuthenticatedAs(string $email, string $password): void
    {
        $response = $this->client->post('/api/auth/login', [
            'json' => compact('email', 'password'),
            'headers' => ['Accept' => 'application/json', 'Content-Type' => 'application/json'],
        ]);
        $body = json_decode($response->getBody()->getContents(), true);
        if (!isset($body['token'])) {
            throw new \RuntimeException('Login failed — no token in response. Body: ' . json_encode($body));
        }
        $this->token = $body['token'];
    }

    // ─── Assertion steps ─────────────────────────────────────────────────────

    /**
     * @Then the response status should be :status
     */
    public function theResponseStatusShouldBe(int $status): void
    {
        if ($this->statusCode !== $status) {
            throw new \RuntimeException(sprintf(
                'Expected status %d, got %d. Body: %s',
                $status, $this->statusCode, json_encode($this->responseBody)
            ));
        }
    }

    /**
     * @Then the response should contain :field
     */
    public function theResponseShouldContain(string $field): void
    {
        if (!array_key_exists($field, $this->responseBody ?? [])) {
            throw new \RuntimeException(
                "Response does not contain '{$field}'. Body: " . json_encode($this->responseBody)
            );
        }
    }

    /**
     * @Then the response field :field should match the admin user email
     */
    public function theResponseFieldShouldMatchAdminEmail(string $field): void
    {
        $expected = $this->testUserEmail();
        $actual = $this->responseBody[$field] ?? null;
        if ((string)$actual !== $expected) {
            throw new \RuntimeException("Expected '{$field}' to equal '{$expected}', got '{$actual}'");
        }
    }

    /**
     * @Then the response field :field should be :value
     */
    public function theResponseFieldShouldBe(string $field, string $value): void
    {
        $actual = $this->responseBody[$field] ?? null;
        if ((string)$actual !== $value) {
            throw new \RuntimeException("Expected '{$field}' to equal '{$value}', got '{$actual}'");
        }
    }

    /**
     * @Then the response data should contain :field
     */
    public function theResponseDataShouldContain(string $field): void
    {
        $data = $this->responseBody['data'] ?? null;
        if (!is_array($data) || !array_key_exists($field, $data)) {
            throw new \RuntimeException(
                "Response data does not contain '{$field}'. Body: " . json_encode($this->responseBody)
            );
        }
    }

    /**
     * @Then the response should not be empty
     */
    public function theResponseShouldNotBeEmpty(): void
    {
        if (empty($this->responseBody)) {
            throw new \RuntimeException('Response body is empty');
        }
    }
}
