<?php

use BinanceAPI\Controllers\CoinbaseMarketController;
use BinanceAPI\Contracts\ClientInterface;
use BinanceAPI\Config;
use PHPUnit\Framework\TestCase;

class CoinbaseMarketControllerTest extends TestCase
{
    protected function setUp(): void
    {
        Config::fake([]);
    }

    public function testProductRequiresProductId(): void
    {
        $controller = new CoinbaseMarketController();
        $response = $controller->product([]);

        $this->assertFalse($response['success']);
        $this->assertStringContainsString('product_id', $response['error']);
    }

    public function testProductBookRequiresProductId(): void
    {
        $controller = new CoinbaseMarketController();
        $response = $controller->productBook([]);

        $this->assertFalse($response['success']);
        $this->assertStringContainsString('product_id', $response['error']);
    }

    public function testTickerRequiresProductId(): void
    {
        $controller = new CoinbaseMarketController();
        $response = $controller->ticker([]);

        $this->assertFalse($response['success']);
        $this->assertStringContainsString('product_id', $response['error']);
    }

    public function testCandlesRequiresFields(): void
    {
        $controller = new CoinbaseMarketController();
        $response = $controller->candles(['product_id' => 'BTC-USD']);

        $this->assertFalse($response['success']);
        $this->assertStringContainsString('start', $response['error']);
    }

    public function testProductsWithMockClient(): void
    {
        $controller = new CoinbaseMarketController($this->createMockClient());
        $response = $controller->products([]);

        $this->assertTrue($response['success']);
        $this->assertIsArray($response['data']);
    }

    public function testProductWithMockClient(): void
    {
        $controller = new CoinbaseMarketController($this->createMockClient());
        $response = $controller->product(['product_id' => 'BTC-USD']);

        $this->assertTrue($response['success']);
        $this->assertIsArray($response['data']);
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
