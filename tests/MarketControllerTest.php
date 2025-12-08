<?php

use BinanceAPI\Controllers\MarketController;
use BinanceAPI\Config;
use PHPUnit\Framework\TestCase;

class MarketControllerTest extends TestCase
{
    private MarketController $controller;

    protected function setUp(): void
    {
        Config::fake([]);
        $this->controller = new MarketController();
    }

    public function testTickerRequiresSymbol(): void
    {
        $response = $this->controller->ticker([]);
        $this->assertFalse($response['success']);
        $this->assertStringContainsString('symbol', $response['error']);
    }

    public function testOrderBookRequiresSymbol(): void
    {
        $response = $this->controller->orderBook([]);
        $this->assertFalse($response['success']);
        $this->assertStringContainsString('symbol', $response['error']);
    }

    public function testTradesRequireSymbol(): void
    {
        $response = $this->controller->trades([]);
        $this->assertFalse($response['success']);
        $this->assertStringContainsString('symbol', $response['error']);
    }

    public function testAvgPriceRequiresSymbol(): void
    {
        $response = $this->controller->avgPrice([]);
        $this->assertFalse($response['success']);
        $this->assertStringContainsString('symbol', $response['error']);
    }

    public function testAggTradesRequiresSymbol(): void
    {
        $response = $this->controller->aggTrades([]);
        $this->assertFalse($response['success']);
        $this->assertStringContainsString('symbol', $response['error']);
    }

    public function testKlinesRequireSymbolAndInterval(): void
    {
        $response = $this->controller->klines(['symbol' => 'BTCUSDT']);
        $this->assertFalse($response['success']);
        $this->assertStringContainsString('interval', $response['error']);
    }

    public function testKlinesRequiresSymbol(): void
    {
        $response = $this->controller->klines(['interval' => '1h']);
        $this->assertFalse($response['success']);
        $this->assertStringContainsString('symbol', $response['error']);
    }

    public function testUiKlinesRequireSymbolAndInterval(): void
    {
        $response = $this->controller->uiKlines(['symbol' => 'BTCUSDT']);
        $this->assertFalse($response['success']);
        $this->assertStringContainsString('interval', $response['error']);
    }

    public function testUiKlinesRequiresSymbol(): void
    {
        $response = $this->controller->uiKlines(['interval' => '1h']);
        $this->assertFalse($response['success']);
        $this->assertStringContainsString('symbol', $response['error']);
    }

    public function testHistoricalTradesRequireSymbol(): void
    {
        $response = $this->controller->historicalTrades([]);
        $this->assertFalse($response['success']);
        $this->assertStringContainsString('symbol', $response['error']);
    }

    public function testFormatResponseSuccess(): void
    {
        $method = new ReflectionMethod(MarketController::class, 'formatResponse');
        $method->setAccessible(true);

        $result = $method->invoke($this->controller, ['symbol' => 'BTCUSDT', 'price' => '50000']);

        $this->assertTrue($result['success']);
        $this->assertSame(['symbol' => 'BTCUSDT', 'price' => '50000'], $result['data']);
    }

    public function testFormatResponsePropagatesError(): void
    {
        $method = new ReflectionMethod(MarketController::class, 'formatResponse');
        $method->setAccessible(true);

        $result = $method->invoke($this->controller, ['success' => false, 'error' => 'test error']);

        $this->assertFalse($result['success']);
        $this->assertSame('test error', $result['error']);
    }

    public function testRollingWindowTickerRequiresSymbol(): void
    {
        $response = $this->controller->rollingWindowTicker([]);
        $this->assertFalse($response['success']);
        $this->assertStringContainsString('symbol', $response['error']);
    }

    public function testTickerPriceNoParams(): void
    {
        // tickerPrice can be called without symbol (returns all)
        // Just verify it returns an array structure
        $response = $this->controller->tickerPrice([]);

        // Should either succeed or fail gracefully without API
        $this->assertIsArray($response);
    }

    public function testTicker24hNoParams(): void
    {
        // ticker24h can be called without symbol (returns all)
        $response = $this->controller->ticker24h([]);

        $this->assertIsArray($response);
    }

    public function testBookTickerNoParams(): void
    {
        // bookTicker can be called without symbol
        $response = $this->controller->bookTicker([]);

        $this->assertIsArray($response);
    }
}
