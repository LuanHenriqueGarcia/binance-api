<?php

use BinanceAPI\Controllers\MarketController;
use PHPUnit\Framework\TestCase;

class MarketControllerTest extends TestCase
{
    private MarketController $controller;

    protected function setUp(): void
    {
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

    public function testUiKlinesRequireSymbolAndInterval(): void
    {
        $response = $this->controller->uiKlines(['symbol' => 'BTCUSDT']);
        $this->assertFalse($response['success']);
        $this->assertStringContainsString('interval', $response['error']);
    }

    public function testHistoricalTradesRequireSymbol(): void
    {
        $response = $this->controller->historicalTrades([]);
        $this->assertFalse($response['success']);
        $this->assertStringContainsString('symbol', $response['error']);
    }
}
