<?php

use BinanceAPI\Controllers\CoinbaseAccountController;
use BinanceAPI\Contracts\ClientInterface;
use BinanceAPI\Config;
use PHPUnit\Framework\TestCase;

class CoinbaseAccountControllerTest extends TestCase
{
    private CoinbaseAccountController $controller;

    protected function setUp(): void
    {
        Config::fake([
            'COINBASE_API_KEY' => 'test-key',
            'COINBASE_API_SECRET' => 'test-secret',
        ]);
        $this->controller = new CoinbaseAccountController($this->createMockClient());
    }

    public function testAccountsWithMockClient(): void
    {
        $response = $this->controller->accounts([]);

        $this->assertTrue($response['success']);
        $this->assertIsArray($response['data']);
    }

    public function testAccountRequiresAccountUuid(): void
    {
        $response = $this->controller->account([]);

        $this->assertFalse($response['success']);
        $this->assertStringContainsString('account_uuid', $response['error']);
    }

    private function createMockClient(): ClientInterface
    {
        return new class implements ClientInterface {
            public function get(string $endpoint, array $params = []): array
            {
                return ['mock' => true, 'endpoint' => $endpoint, 'params' => $params];
            }

            public function post(string $endpoint, array $params = []): array
            {
                return ['mock' => true];
            }

            public function delete(string $endpoint, array $params = []): array
            {
                return ['mock' => true];
            }
        };
    }
}
