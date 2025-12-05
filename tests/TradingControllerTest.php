<?php

use BinanceAPI\Controllers\TradingController;
use PHPUnit\Framework\TestCase;

class TradingControllerTest extends TestCase
{
    private TradingController $controller;

    protected function setUp(): void
    {
        $this->controller = new TradingController();
    }

    public function testRequiresCredentials(): void
    {
        $response = $this->controller->createOrder([]);
        $this->assertFalse($response['success']);
        $this->assertStringContainsString('api_key', $response['error']);
    }

    public function testRejectsInvalidType(): void
    {
        $response = $this->controller->createOrder([
            'api_key' => 'k',
            'secret_key' => 's',
            'symbol' => 'BTCUSDT',
            'side' => 'BUY',
            'type' => 'UNKNOWN',
            'quantity' => '1'
        ]);

        $this->assertFalse($response['success']);
        $this->assertStringContainsString('type', $response['error']);
    }

    public function testRejectsInvalidSide(): void
    {
        $response = $this->controller->createOrder([
            'api_key' => 'k',
            'secret_key' => 's',
            'symbol' => 'BTCUSDT',
            'side' => 'HOLD',
            'type' => 'LIMIT',
            'quantity' => '1',
            'price' => '10'
        ]);

        $this->assertFalse($response['success']);
        $this->assertStringContainsString('side', $response['error']);
    }

    public function testMarketRequiresQuantityOrQuote(): void
    {
        $response = $this->controller->createOrder([
            'api_key' => 'k',
            'secret_key' => 's',
            'symbol' => 'BTCUSDT',
            'side' => 'BUY',
            'type' => 'MARKET'
        ]);

        $this->assertFalse($response['success']);
        $this->assertStringContainsString('quantity', $response['error']);
    }

    public function testLimitRequiresPrice(): void
    {
        $response = $this->controller->createOrder([
            'api_key' => 'k',
            'secret_key' => 's',
            'symbol' => 'BTCUSDT',
            'side' => 'BUY',
            'type' => 'LIMIT',
            'quantity' => '0.001'
        ]);

        $this->assertFalse($response['success']);
        $this->assertStringContainsString('price', $response['error']);
    }

    public function testInvalidSymbol(): void
    {
        $response = $this->controller->createOrder([
            'api_key' => 'k',
            'secret_key' => 's',
            'symbol' => 'BTC',
            'side' => 'BUY',
            'type' => 'LIMIT',
            'quantity' => '0.001',
            'price' => '10'
        ]);

        $this->assertFalse($response['success']);
        $this->assertStringContainsString('symbol', $response['error']);
    }

    public function testInvalidTimeInForce(): void
    {
        $response = $this->controller->createOrder([
            'api_key' => 'k',
            'secret_key' => 's',
            'symbol' => 'BTCUSDT',
            'side' => 'BUY',
            'type' => 'LIMIT',
            'quantity' => '0.001',
            'price' => '10',
            'timeInForce' => 'ABC'
        ]);

        $this->assertFalse($response['success']);
        $this->assertStringContainsString('timeInForce', $response['error']);
    }

    public function testStopRequiresStopPrice(): void
    {
        $response = $this->controller->createOrder([
            'api_key' => 'k',
            'secret_key' => 's',
            'symbol' => 'BTCUSDT',
            'side' => 'BUY',
            'type' => 'STOP_LOSS',
            'quantity' => '0.001'
        ]);

        $this->assertFalse($response['success']);
        $this->assertStringContainsString('stopPrice', $response['error']);
    }

    public function testQueryOrderRequiresIdentifier(): void
    {
        $response = $this->controller->queryOrder([
            'api_key' => 'k',
            'secret_key' => 's',
            'symbol' => 'BTCUSDT',
        ]);

        $this->assertFalse($response['success']);
        $this->assertStringContainsString('orderId', $response['error']);
    }

    public function testCancelOpenOrdersRequiresSymbol(): void
    {
        $response = $this->controller->cancelOpenOrders([
            'api_key' => 'k',
            'secret_key' => 's',
        ]);

        $this->assertFalse($response['success']);
        $this->assertStringContainsString('symbol', $response['error']);
    }

    public function testCreateOcoRequiresStopPrice(): void
    {
        $response = $this->controller->createOco([
            'api_key' => 'k',
            'secret_key' => 's',
            'symbol' => 'BTCUSDT',
            'side' => 'BUY',
            'quantity' => '0.1',
            'price' => '1.0',
        ]);

        $this->assertFalse($response['success']);
        $this->assertStringContainsString('stopPrice', $response['error']);
    }

    public function testCommissionRateRequiresSymbol(): void
    {
        $response = $this->controller->commissionRate([
            'api_key' => 'k',
            'secret_key' => 's',
        ]);

        $this->assertFalse($response['success']);
        $this->assertStringContainsString('symbol', $response['error']);
    }

    public function testCancelReplaceRequiresCancelIdentifier(): void
    {
        $response = $this->controller->cancelReplace([
            'api_key' => 'k',
            'secret_key' => 's',
            'symbol' => 'BTCUSDT',
            'side' => 'BUY',
            'type' => 'LIMIT',
            'quantity' => '0.001',
            'price' => '10',
            'cancelReplaceMode' => 'STOP_ON_FAILURE'
        ]);

        $this->assertFalse($response['success']);
        $this->assertStringContainsString('cancelOrderId', $response['error']);
    }

    public function testCancelReplaceRequiresMode(): void
    {
        $response = $this->controller->cancelReplace([
            'api_key' => 'k',
            'secret_key' => 's',
            'symbol' => 'BTCUSDT',
            'side' => 'BUY',
            'type' => 'LIMIT',
            'quantity' => '0.001',
            'price' => '10',
            'cancelOrderId' => '1',
        ]);

        $this->assertFalse($response['success']);
        $this->assertStringContainsString('cancelReplaceMode', $response['error']);
    }
}
