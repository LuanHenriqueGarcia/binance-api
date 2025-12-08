<?php

use BinanceAPI\Router;
use BinanceAPI\Config;
use PHPUnit\Framework\TestCase;

class RouterTest extends TestCase
{
    protected function setUp(): void
    {
        Config::fake([]);
    }

    public function testSendResponseSuccessSets200(): void
    {
        $router = new Router('GET', '/test', []);
        $output = $this->invokeSendResponse($router, ['success' => true, 'data' => []]);

        $this->assertSame(200, http_response_code());
        $this->assertStringContainsString('"success": true', $output);
    }

    public function testSendResponseErrorSets400(): void
    {
        $router = new Router('GET', '/test', []);
        $output = $this->invokeSendResponse($router, ['success' => false, 'error' => 'fail']);

        $this->assertSame(400, http_response_code());
        $this->assertStringContainsString('"success": false', $output);
    }

    public function testSendResponseUsesCustomCode(): void
    {
        $router = new Router('GET', '/test', []);
        $output = $this->invokeSendResponse($router, ['success' => false, 'error' => 'rate', 'code' => 429]);

        $this->assertSame(429, http_response_code());
        $this->assertStringContainsString('"code": 429', $output);
    }

    public function testAuthRequired(): void
    {
        Config::fake([
            'BASIC_AUTH_USER' => 'u',
            'BASIC_AUTH_PASSWORD' => 'p'
        ]);

        $_SERVER['PHP_AUTH_USER'] = null;
        $_SERVER['PHP_AUTH_PW'] = null;

        $router = new Router('GET', '/api/general/ping', []);

        ob_start();
        $router->dispatch();
        $output = (string)ob_get_clean();

        $this->assertSame(401, http_response_code());
        $decoded = json_decode($output, true);
        $this->assertFalse($decoded['success']);
        $this->assertSame('Não autorizado', $decoded['error']);
    }

    public function testAuthPasses(): void
    {
        Config::fake([
            'BASIC_AUTH_USER' => 'u',
            'BASIC_AUTH_PASSWORD' => 'p'
        ]);

        $_SERVER['PHP_AUTH_USER'] = 'u';
        $_SERVER['PHP_AUTH_PW'] = 'p';

        $router = new Router('GET', '/', []);

        ob_start();
        $router->dispatch();
        $output = (string)ob_get_clean();

        $this->assertSame(200, http_response_code());
        $this->assertStringContainsString('Binance API REST', $output);
    }

    public function testDispatchRootReturnsMessage(): void
    {
        Config::fake([]);
        $router = new Router('GET', '/', []);

        ob_start();
        $router->dispatch();
        $output = (string)ob_get_clean();

        $this->assertSame(200, http_response_code());
        $this->assertStringContainsString('Binance API REST', $output);
    }

    public function testDispatchUnknownReturns404(): void
    {
        Config::fake([]);
        $router = new Router('GET', '/api/unknown', []);

        ob_start();
        $router->dispatch();
        $output = (string)ob_get_clean();

        $this->assertSame(404, http_response_code());
        $decoded = json_decode($output, true);
        $this->assertFalse($decoded['success']);
        $this->assertSame('Endpoint não encontrado', $decoded['error']);
    }

    /**
     * @param array<string,mixed> $data
     */
    private function invokeSendResponse(Router $router, array $data): string
    {
        $method = new ReflectionMethod(Router::class, 'sendResponse');
        $method->setAccessible(true);

        ob_start();
        $method->invoke($router, $data);
        return (string)ob_get_clean();
    }

    public function testDispatchHealthEndpoint(): void
    {
        Config::fake([]);
        $router = new Router('GET', '/api/health', []);

        ob_start();
        $router->dispatch();
        $output = (string)ob_get_clean();

        $decoded = json_decode($output, true);
        $this->assertArrayHasKey('success', $decoded);
        $this->assertArrayHasKey('storage_writable', $decoded);
    }

    public function testDispatchMetricsDisabled(): void
    {
        Config::fake(['METRICS_ENABLED' => false]);
        $router = new Router('GET', '/api/metrics', []);

        ob_start();
        $router->dispatch();
        $output = (string)ob_get_clean();

        $this->assertSame(404, http_response_code());
        $decoded = json_decode($output, true);
        $this->assertFalse($decoded['success']);
        $this->assertStringContainsString('disabled', $decoded['error']);
    }

    public function testDispatchMetricsEnabled(): void
    {
        Config::fake(['METRICS_ENABLED' => true]);
        $router = new Router('GET', '/api/metrics', []);

        ob_start();
        $router->dispatch();
        $output = (string)ob_get_clean();

        $this->assertSame(200, http_response_code());
        $decoded = json_decode($output, true);
        $this->assertTrue($decoded['success']);
        $this->assertArrayHasKey('data', $decoded);
    }

    public function testNormalizeUppercasesSymbolViaParseParams(): void
    {
        // Test normalize with actual parseParams path
        $_GET = ['symbol' => 'btcusdt'];
        $router = new Router('GET', '/', null);

        $paramsProperty = new ReflectionProperty(Router::class, 'params');
        $paramsProperty->setAccessible(true);
        $params = $paramsProperty->getValue($router);

        $this->assertSame('BTCUSDT', $params['symbol']);
        $_GET = [];
    }

    public function testSendErrorMethod(): void
    {
        $router = new Router('GET', '/test', []);
        $method = new ReflectionMethod(Router::class, 'sendError');
        $method->setAccessible(true);

        ob_start();
        $method->invoke($router, 'Test error', 400);
        $output = (string)ob_get_clean();

        $this->assertSame(400, http_response_code());
        $decoded = json_decode($output, true);
        $this->assertFalse($decoded['success']);
        $this->assertSame('Test error', $decoded['error']);
    }

    public function testDispatchGeneralUnknownAction(): void
    {
        Config::fake([]);
        $router = new Router('GET', '/api/general/unknown', []);

        ob_start();
        $router->dispatch();
        $output = (string)ob_get_clean();

        $this->assertSame(404, http_response_code());
        $decoded = json_decode($output, true);
        $this->assertFalse($decoded['success']);
    }

    public function testDispatchMarketUnknownAction(): void
    {
        Config::fake([]);
        $router = new Router('GET', '/api/market/unknown', []);

        ob_start();
        $router->dispatch();
        $output = (string)ob_get_clean();

        $this->assertSame(404, http_response_code());
    }

    public function testDispatchAccountUnknownAction(): void
    {
        Config::fake([]);
        $router = new Router('GET', '/api/account/unknown', []);

        ob_start();
        $router->dispatch();
        $output = (string)ob_get_clean();

        $this->assertSame(404, http_response_code());
    }

    public function testDispatchTradingUnknownAction(): void
    {
        Config::fake([]);
        $router = new Router('GET', '/api/trading/unknown', []);

        ob_start();
        $router->dispatch();
        $output = (string)ob_get_clean();

        $this->assertSame(404, http_response_code());
    }

    public function testRateLimitNotEnabledByDefault(): void
    {
        Config::fake([]);
        $router = new Router('GET', '/api/account/info', []);

        $method = new ReflectionMethod(Router::class, 'isRateLimited');
        $method->setAccessible(true);

        $result = $method->invoke($router, 'account');

        $this->assertFalse($result);
    }

    public function testRateLimitNotAppliedToGeneralEndpoint(): void
    {
        Config::fake(['RATE_LIMIT_ENABLED' => 'true']);
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';

        $router = new Router('GET', '/api/general/ping', []);

        $method = new ReflectionMethod(Router::class, 'isRateLimited');
        $method->setAccessible(true);

        $result = $method->invoke($router, 'general');

        $this->assertFalse($result);
    }

    public function testParseParamsGet(): void
    {
        $_GET = ['test' => 'value'];
        $router = new Router('GET', '/test', null);

        $paramsProperty = new ReflectionProperty(Router::class, 'params');
        $paramsProperty->setAccessible(true);
        $params = $paramsProperty->getValue($router);

        $this->assertSame('value', $params['test']);
        $_GET = [];
    }

    public function testCorrelationIdSetFromHeader(): void
    {
        $_SERVER['HTTP_X_CORRELATION_ID'] = 'test-correlation-id';
        Config::fake([]);
        new Router('GET', '/', []);

        $this->assertSame('test-correlation-id', Config::getRequestId());

        unset($_SERVER['HTTP_X_CORRELATION_ID']);
    }
}
