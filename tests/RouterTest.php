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
}
