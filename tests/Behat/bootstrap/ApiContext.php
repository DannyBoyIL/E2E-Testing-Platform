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
        $this->client = new Client([
            'base_uri'    => self::BASE_URL,
            'http_errors' => false,
        ]);
    }

    private function headers(): array
    {
        $headers = [
            'Accept'       => 'application/json',
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
     * @When I POST to :endpoint with:
     */
    public function iPostToWith(string $endpoint, TableNode $table): void
    {
        $response = $this->client->post($endpoint, [
            'json'    => $table->getRowsHash(),
            'headers' => $this->headers(),
        ]);
        $this->statusCode   = $response->getStatusCode();
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
        $this->statusCode   = $response->getStatusCode();
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
        $this->statusCode   = $response->getStatusCode();
        $this->responseBody = json_decode($response->getBody()->getContents(), true);
    }

    // ─── Auth steps ──────────────────────────────────────────────────────────

    /**
     * @Given I am authenticated as :email with password :password
     */
    public function iAmAuthenticatedAs(string $email, string $password): void
    {
        $response = $this->client->post('/api/auth/login', [
            'json'    => compact('email', 'password'),
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
     * @Then the response field :field should be :value
     */
    public function theResponseFieldShouldBe(string $field, string $value): void
    {
        $actual = $this->responseBody[$field] ?? null;
        if ((string) $actual !== $value) {
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
