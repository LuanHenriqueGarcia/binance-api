<?php

use BinanceAPI\Controllers\CoinbaseTradingController;
use BinanceAPI\Contracts\ClientInterface;
use BinanceAPI\Config;
use PHPUnit\Framework\TestCase;

class CoinbaseTradingControllerTest extends TestCase
{
    private CoinbaseTradingController $controller;

    protected function setUp(): void
    {
        Config::fake([
            'COINBASE_API_KEY' => 'test-key',
            'COINBASE_API_SECRET' => 'test-secret',
        ]);
        $this->controller = new CoinbaseTradingController($this->createMockClient());
    }

    public function testCreateOrderRequiresProductId(): void
    {
        $response = $this->controller->createOrder([
            'side' => 'BUY',
            'type' => 'MARKET',
            'quote_size' => '10'
        ]);

        $this->assertFalse($response['success']);
        $this->assertStringContainsString('product_id', $response['error']);
    }

    public function testCreateOrderRequiresSide(): void
    {
        $response = $this->controller->createOrder([
            'product_id' => 'BTC-USD',
            'type' => 'MARKET',
            'quote_size' => '10'
        ]);

        $this->assertFalse($response['success']);
        $this->assertStringContainsString('side', $response['error']);
    }

    public function testCreateOrderRequiresType(): void
    {
        $response = $this->controller->createOrder([
            'product_id' => 'BTC-USD',
            'side' => 'BUY',
            'quote_size' => '10'
        ]);

        $this->assertFalse($response['success']);
        $this->assertStringContainsString('type', $response['error']);
    }

    public function testCancelOrderRequiresOrderId(): void
    {
        $response = $this->controller->cancelOrder([]);

        $this->assertFalse($response['success']);
        $this->assertStringContainsString('order', $response['error']);
    }

    public function testGetOrderRequiresOrderId(): void
    {
        $response = $this->controller->getOrder([]);

        $this->assertFalse($response['success']);
        $this->assertStringContainsString('order_id', $response['error']);
    }

    public function testListOrdersWithMockClient(): void
    {
        $response = $this->controller->listOrders([]);

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
                return ['mock' => true, 'endpoint' => $endpoint, 'params' => $params];
            }

            public function delete(string $endpoint, array $params = []): array
            {
                return ['mock' => true, 'endpoint' => $endpoint, 'params' => $params];
            }
        };
    }
}
