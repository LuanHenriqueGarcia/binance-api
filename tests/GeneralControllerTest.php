<?php

use BinanceAPI\Controllers\GeneralController;
use BinanceAPI\Config;
use PHPUnit\Framework\TestCase;

class GeneralControllerTest extends TestCase
{
    private GeneralController $controller;

    protected function setUp(): void
    {
        Config::fake([]);
        $this->controller = new GeneralController();
    }

    public function testFormatResponseSuccessWrap(): void
    {
        $method = new ReflectionMethod(GeneralController::class, 'formatResponse');
        $method->setAccessible(true);

        $response = $method->invoke($this->controller, ['hello' => 'world']);
        $this->assertTrue($response['success']);
        $this->assertSame(['hello' => 'world'], $response['data']);
    }

    public function testFormatResponsePropagatesError(): void
    {
        $method = new ReflectionMethod(GeneralController::class, 'formatResponse');
        $method->setAccessible(true);

        $error = ['success' => false, 'error' => 'fail'];
        $response = $method->invoke($this->controller, $error);
        $this->assertFalse($response['success']);
        $this->assertSame('fail', $response['error']);
    }

    public function testPingReturnsArray(): void
    {
        $response = $this->controller->ping();

        $this->assertIsArray($response);
        // Either success or error format
        $this->assertTrue(
            isset($response['success']) || isset($response['error']),
            'Response should have success or error key'
        );
    }

    public function testTimeReturnsArray(): void
    {
        $response = $this->controller->time();

        $this->assertIsArray($response);
    }

    public function testExchangeInfoReturnsArray(): void
    {
        $response = $this->controller->exchangeInfo([]);

        $this->assertIsArray($response);
    }

    public function testExchangeInfoWithSymbol(): void
    {
        $response = $this->controller->exchangeInfo(['symbol' => 'BTCUSDT']);

        $this->assertIsArray($response);
    }

    public function testExchangeInfoWithSymbols(): void
    {
        $response = $this->controller->exchangeInfo(['symbols' => ['BTCUSDT', 'ETHUSDT']]);

        $this->assertIsArray($response);
    }

    public function testExchangeInfoWithPermissions(): void
    {
        $response = $this->controller->exchangeInfo(['permissions' => 'SPOT']);

        $this->assertIsArray($response);
    }

    public function testExchangeInfoWithMarket(): void
    {
        $response = $this->controller->exchangeInfo(['market' => 'spot']);

        $this->assertIsArray($response);
    }

    public function testExchangeInfoWithNoCache(): void
    {
        $response = $this->controller->exchangeInfo(['noCache' => true]);

        $this->assertIsArray($response);
    }

    public function testExchangeInfoWithNoCacheString(): void
    {
        $response = $this->controller->exchangeInfo(['noCache' => 'true']);

        $this->assertIsArray($response);
    }

    public function testExchangeInfoWithNoCacheInt(): void
    {
        $response = $this->controller->exchangeInfo(['noCache' => 1]);

        $this->assertIsArray($response);
    }
}
