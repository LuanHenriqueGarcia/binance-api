<?php

use BinanceAPI\Config;
use BinanceAPI\Http\Request;
use BinanceAPI\Http\Response;
use BinanceAPI\Http\Middleware\AuthMiddleware;
use BinanceAPI\Http\Middleware\CorsMiddleware;
use BinanceAPI\Http\Middleware\LoggingMiddleware;
use BinanceAPI\Http\Middleware\RateLimitMiddleware;
use BinanceAPI\RateLimiter;
use PHPUnit\Framework\TestCase;

class MiddlewareTest extends TestCase
{
    protected function setUp(): void
    {
        Config::fake([]);
        // Clear superglobals
        unset($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']);
        unset($_SERVER['HTTP_ORIGIN']);
        unset($_SERVER['HTTP_X_FORWARDED_FOR'], $_SERVER['HTTP_X_REAL_IP'], $_SERVER['REMOTE_ADDR']);
    }

    // ========== AuthMiddleware Tests ==========

    public function testAuthMiddlewarePassesWithoutConfig(): void
    {
        Config::fake([]);
        $middleware = new AuthMiddleware();
        $request = new Request('GET', '/test', []);

        $nextCalled = false;
        $next = function (Request $req) use (&$nextCalled): Response {
            $nextCalled = true;
            return Response::success(['test' => 'data']);
        };

        $response = $middleware->handle($request, $next);

        $this->assertTrue($nextCalled);
        $this->assertSame(200, $response->getStatusCode());
    }

    public function testAuthMiddlewarePassesWithValidCredentials(): void
    {
        Config::fake([
            'BASIC_AUTH_USER' => 'admin',
            'BASIC_AUTH_PASSWORD' => 'secret123'
        ]);

        $_SERVER['PHP_AUTH_USER'] = 'admin';
        $_SERVER['PHP_AUTH_PW'] = 'secret123';

        $middleware = new AuthMiddleware();
        $request = new Request('GET', '/test', []);

        $nextCalled = false;
        $next = function (Request $req) use (&$nextCalled): Response {
            $nextCalled = true;
            return Response::success(['authenticated' => true]);
        };

        $response = $middleware->handle($request, $next);

        $this->assertTrue($nextCalled);
        $this->assertSame(200, $response->getStatusCode());
    }

    public function testAuthMiddlewareBlocksWithInvalidCredentials(): void
    {
        Config::fake([
            'BASIC_AUTH_USER' => 'admin',
            'BASIC_AUTH_PASSWORD' => 'secret123'
        ]);

        $_SERVER['PHP_AUTH_USER'] = 'wronguser';
        $_SERVER['PHP_AUTH_PW'] = 'wrongpass';

        $middleware = new AuthMiddleware();
        $request = new Request('GET', '/test', []);

        $nextCalled = false;
        $next = function (Request $req) use (&$nextCalled): Response {
            $nextCalled = true;
            return Response::success();
        };

        $response = $middleware->handle($request, $next);

        $this->assertFalse($nextCalled);
        $this->assertSame(401, $response->getStatusCode());
    }

    public function testAuthMiddlewareBlocksWithMissingCredentials(): void
    {
        Config::fake([
            'BASIC_AUTH_USER' => 'admin',
            'BASIC_AUTH_PASSWORD' => 'secret123'
        ]);

        // Not setting PHP_AUTH_USER and PHP_AUTH_PW

        $middleware = new AuthMiddleware();
        $request = new Request('GET', '/test', []);

        $nextCalled = false;
        $next = function (Request $req) use (&$nextCalled): Response {
            $nextCalled = true;
            return Response::success();
        };

        $response = $middleware->handle($request, $next);

        $this->assertFalse($nextCalled);
        $this->assertSame(401, $response->getStatusCode());
    }

    public function testAuthMiddlewareBlocksWithPartialCredentials(): void
    {
        Config::fake([
            'BASIC_AUTH_USER' => 'admin',
            'BASIC_AUTH_PASSWORD' => 'secret123'
        ]);

        $_SERVER['PHP_AUTH_USER'] = 'admin';
        // Missing PHP_AUTH_PW

        $middleware = new AuthMiddleware();
        $request = new Request('GET', '/test', []);

        $nextCalled = false;
        $next = function (Request $req) use (&$nextCalled): Response {
            $nextCalled = true;
            return Response::success();
        };

        $response = $middleware->handle($request, $next);

        $this->assertFalse($nextCalled);
        $this->assertSame(401, $response->getStatusCode());
    }

    public function testAuthMiddlewarePassesWithOnlyUserConfigured(): void
    {
        Config::fake([
            'BASIC_AUTH_USER' => 'admin'
            // No password configured
        ]);

        $middleware = new AuthMiddleware();
        $request = new Request('GET', '/test', []);

        $nextCalled = false;
        $next = function (Request $req) use (&$nextCalled): Response {
            $nextCalled = true;
            return Response::success();
        };

        $response = $middleware->handle($request, $next);

        // Should pass because auth is not fully configured
        $this->assertTrue($nextCalled);
    }

    // ========== CorsMiddleware Tests ==========

    public function testCorsMiddlewareHandlesPreflightRequest(): void
    {
        $middleware = new CorsMiddleware();
        $request = new Request('OPTIONS', '/test', []);

        $nextCalled = false;
        $next = function (Request $req) use (&$nextCalled): Response {
            $nextCalled = true;
            return Response::success();
        };

        $response = $middleware->handle($request, $next);

        $this->assertFalse($nextCalled); // Preflight doesn't call next
        $this->assertSame(204, $response->getStatusCode());
    }

    public function testCorsMiddlewareAddsHeadersToResponse(): void
    {
        $middleware = new CorsMiddleware();
        $request = new Request('GET', '/test', []);

        $next = function (Request $req): Response {
            return Response::success(['test' => 'data']);
        };

        $response = $middleware->handle($request, $next);

        $this->assertSame(200, $response->getStatusCode());
    }

    public function testCorsMiddlewareWithCustomOrigins(): void
    {
        $middleware = new CorsMiddleware(
            ['https://example.com'],
            ['GET', 'POST'],
            ['Content-Type']
        );

        $request = new Request('GET', '/test', []);

        $next = function (Request $req): Response {
            return Response::success();
        };

        $response = $middleware->handle($request, $next);

        $this->assertSame(200, $response->getStatusCode());
    }

    public function testCorsMiddlewareWithWildcardOrigin(): void
    {
        $middleware = new CorsMiddleware(['*']);
        $request = new Request('GET', '/test', []);

        $next = function (Request $req): Response {
            return Response::success();
        };

        $response = $middleware->handle($request, $next);

        $this->assertSame(200, $response->getStatusCode());
    }

    public function testCorsMiddlewareWithSpecificOriginHeader(): void
    {
        $_SERVER['HTTP_ORIGIN'] = 'https://specific.com';

        $middleware = new CorsMiddleware(['https://specific.com', 'https://other.com']);
        $request = new Request('GET', '/test', []);

        $next = function (Request $req): Response {
            return Response::success();
        };

        $response = $middleware->handle($request, $next);

        $this->assertSame(200, $response->getStatusCode());

        unset($_SERVER['HTTP_ORIGIN']);
    }

    public function testCorsMiddlewarePreflightWithCustomMethods(): void
    {
        $middleware = new CorsMiddleware(
            ['*'],
            ['GET', 'POST', 'PUT', 'DELETE', 'PATCH'],
            ['Content-Type', 'Authorization', 'X-Custom-Header']
        );

        $request = new Request('OPTIONS', '/test', []);

        $next = function (Request $req): Response {
            return Response::success();
        };

        $response = $middleware->handle($request, $next);

        $this->assertSame(204, $response->getStatusCode());
    }

    // ========== LoggingMiddleware Tests ==========

    public function testLoggingMiddlewareLogsRequestAndResponse(): void
    {
        $middleware = new LoggingMiddleware();
        $request = new Request('GET', '/api/test', []);

        $next = function (Request $req): Response {
            return Response::success(['logged' => true]);
        };

        $response = $middleware->handle($request, $next);

        $this->assertSame(200, $response->getStatusCode());
    }

    public function testLoggingMiddlewarePassesRequestToNext(): void
    {
        $middleware = new LoggingMiddleware();
        $request = new Request('POST', '/api/data', ['key' => 'value']);

        $receivedRequest = null;
        $next = function (Request $req) use (&$receivedRequest): Response {
            $receivedRequest = $req;
            return Response::success();
        };

        $middleware->handle($request, $next);

        $this->assertSame($request, $receivedRequest);
    }

    public function testLoggingMiddlewareReturnsResponseFromNext(): void
    {
        $middleware = new LoggingMiddleware();
        $request = new Request('GET', '/test', []);

        $expectedData = ['custom' => 'response'];
        $next = function (Request $req) use ($expectedData): Response {
            return Response::success($expectedData);
        };

        $response = $middleware->handle($request, $next);

        $data = $response->getData();
        $this->assertTrue($data['success']);
        $this->assertSame($expectedData, $data['data']);
    }

    public function testLoggingMiddlewareWithErrorResponse(): void
    {
        $middleware = new LoggingMiddleware();
        $request = new Request('GET', '/test', []);

        $next = function (Request $req): Response {
            return Response::error('Something went wrong', 500);
        };

        $response = $middleware->handle($request, $next);

        $this->assertSame(500, $response->getStatusCode());
    }

    public function testLoggingMiddlewareWithCorrelationId(): void
    {
        $_SERVER['HTTP_X_CORRELATION_ID'] = 'test-correlation-123';

        $middleware = new LoggingMiddleware();
        $request = new Request('GET', '/test', []);

        $next = function (Request $req): Response {
            return Response::success();
        };

        $response = $middleware->handle($request, $next);

        $this->assertSame(200, $response->getStatusCode());

        unset($_SERVER['HTTP_X_CORRELATION_ID']);
    }

    // ========== RateLimitMiddleware Tests ==========

    public function testRateLimitMiddlewarePassesWhenDisabled(): void
    {
        Config::fake(['RATE_LIMIT_ENABLED' => false]);

        $middleware = new RateLimitMiddleware();
        $request = new Request('GET', '/api/account/info', []);

        $nextCalled = false;
        $next = function (Request $req) use (&$nextCalled): Response {
            $nextCalled = true;
            return Response::success();
        };

        $response = $middleware->handle($request, $next);

        $this->assertTrue($nextCalled);
        $this->assertSame(200, $response->getStatusCode());
    }

    public function testRateLimitMiddlewarePassesForUnprotectedEndpoints(): void
    {
        Config::fake(['RATE_LIMIT_ENABLED' => 'true']);

        $middleware = new RateLimitMiddleware();
        $request = new Request('GET', '/api/general/ping', []);

        $nextCalled = false;
        $next = function (Request $req) use (&$nextCalled): Response {
            $nextCalled = true;
            return Response::success();
        };

        $response = $middleware->handle($request, $next);

        $this->assertTrue($nextCalled);
        $this->assertSame(200, $response->getStatusCode());
    }

    public function testRateLimitMiddlewarePassesForMarketEndpoint(): void
    {
        Config::fake(['RATE_LIMIT_ENABLED' => 'true']);

        $middleware = new RateLimitMiddleware();
        $request = new Request('GET', '/api/market/ticker', []);

        $nextCalled = false;
        $next = function (Request $req) use (&$nextCalled): Response {
            $nextCalled = true;
            return Response::success();
        };

        $response = $middleware->handle($request, $next);

        $this->assertTrue($nextCalled);
        $this->assertSame(200, $response->getStatusCode());
    }

    public function testRateLimitMiddlewareChecksAccountEndpoint(): void
    {
        Config::fake(['RATE_LIMIT_ENABLED' => 'true']);
        $_SERVER['REMOTE_ADDR'] = '192.168.1.100';

        // Create a mock rate limiter that always allows
        $mockRateLimiter = $this->createMock(RateLimiter::class);
        $mockRateLimiter->method('hit')->willReturn(['allowed' => true, 'retryAfter' => null]);

        $middleware = new RateLimitMiddleware($mockRateLimiter);
        $request = new Request('GET', '/api/account/info', []);

        $nextCalled = false;
        $next = function (Request $req) use (&$nextCalled): Response {
            $nextCalled = true;
            return Response::success();
        };

        $response = $middleware->handle($request, $next);

        $this->assertTrue($nextCalled);
        $this->assertSame(200, $response->getStatusCode());

        unset($_SERVER['REMOTE_ADDR']);
    }

    public function testRateLimitMiddlewareChecksTradingEndpoint(): void
    {
        Config::fake(['RATE_LIMIT_ENABLED' => 'true']);
        $_SERVER['REMOTE_ADDR'] = '192.168.1.100';

        $mockRateLimiter = $this->createMock(RateLimiter::class);
        $mockRateLimiter->method('hit')->willReturn(['allowed' => true, 'retryAfter' => null]);

        $middleware = new RateLimitMiddleware($mockRateLimiter);
        $request = new Request('POST', '/api/trading/create-order', []);

        $nextCalled = false;
        $next = function (Request $req) use (&$nextCalled): Response {
            $nextCalled = true;
            return Response::success();
        };

        $response = $middleware->handle($request, $next);

        $this->assertTrue($nextCalled);
        $this->assertSame(200, $response->getStatusCode());

        unset($_SERVER['REMOTE_ADDR']);
    }

    public function testRateLimitMiddlewareBlocksWhenLimitExceeded(): void
    {
        Config::fake(['RATE_LIMIT_ENABLED' => 'true']);
        $_SERVER['REMOTE_ADDR'] = '192.168.1.100';

        $mockRateLimiter = $this->createMock(RateLimiter::class);
        $mockRateLimiter->method('hit')->willReturn(['allowed' => false, 'retryAfter' => 30]);

        $middleware = new RateLimitMiddleware($mockRateLimiter);
        $request = new Request('GET', '/api/account/info', []);

        $nextCalled = false;
        $next = function (Request $req) use (&$nextCalled): Response {
            $nextCalled = true;
            return Response::success();
        };

        $response = $middleware->handle($request, $next);

        $this->assertFalse($nextCalled);
        $this->assertSame(429, $response->getStatusCode());

        unset($_SERVER['REMOTE_ADDR']);
    }

    public function testRateLimitMiddlewareBlocksWithDefaultRetryAfter(): void
    {
        Config::fake(['RATE_LIMIT_ENABLED' => 'true']);
        $_SERVER['REMOTE_ADDR'] = '192.168.1.100';

        $mockRateLimiter = $this->createMock(RateLimiter::class);
        $mockRateLimiter->method('hit')->willReturn(['allowed' => false, 'retryAfter' => null]);

        $middleware = new RateLimitMiddleware($mockRateLimiter);
        $request = new Request('GET', '/api/trading/query-order', []);

        $nextCalled = false;
        $next = function (Request $req) use (&$nextCalled): Response {
            $nextCalled = true;
            return Response::success();
        };

        $response = $middleware->handle($request, $next);

        $this->assertFalse($nextCalled);
        $this->assertSame(429, $response->getStatusCode());

        unset($_SERVER['REMOTE_ADDR']);
    }

    public function testRateLimitMiddlewareWithoutApiPrefix(): void
    {
        Config::fake(['RATE_LIMIT_ENABLED' => 'true']);
        $_SERVER['REMOTE_ADDR'] = '192.168.1.100';

        $mockRateLimiter = $this->createMock(RateLimiter::class);
        $mockRateLimiter->method('hit')->willReturn(['allowed' => true, 'retryAfter' => null]);

        $middleware = new RateLimitMiddleware($mockRateLimiter);
        // Path without /api prefix
        $request = new Request('GET', '/account/info', []);

        $nextCalled = false;
        $next = function (Request $req) use (&$nextCalled): Response {
            $nextCalled = true;
            return Response::success();
        };

        $response = $middleware->handle($request, $next);

        $this->assertTrue($nextCalled);
        $this->assertSame(200, $response->getStatusCode());

        unset($_SERVER['REMOTE_ADDR']);
    }

    public function testRateLimitMiddlewareWithEmptyPath(): void
    {
        Config::fake(['RATE_LIMIT_ENABLED' => 'true']);

        $middleware = new RateLimitMiddleware();
        $request = new Request('GET', '/', []);

        $nextCalled = false;
        $next = function (Request $req) use (&$nextCalled): Response {
            $nextCalled = true;
            return Response::success();
        };

        $response = $middleware->handle($request, $next);

        $this->assertTrue($nextCalled);
        $this->assertSame(200, $response->getStatusCode());
    }

    public function testRateLimitMiddlewareUsesClientIpFromRequest(): void
    {
        Config::fake(['RATE_LIMIT_ENABLED' => 'true']);
        $_SERVER['HTTP_X_FORWARDED_FOR'] = '10.0.0.1';

        $mockRateLimiter = $this->createMock(RateLimiter::class);
        $mockRateLimiter->expects($this->once())
            ->method('hit')
            ->with($this->stringContains('10.0.0.1'))
            ->willReturn(['allowed' => true, 'retryAfter' => null]);

        $middleware = new RateLimitMiddleware($mockRateLimiter);
        $request = new Request('GET', '/api/account/balance', []);

        $next = function (Request $req): Response {
            return Response::success();
        };

        $middleware->handle($request, $next);

        unset($_SERVER['HTTP_X_FORWARDED_FOR']);
    }

    // ========== Integration Tests ==========

    public function testMiddlewareChain(): void
    {
        Config::fake([]);

        $cors = new CorsMiddleware();
        $logging = new LoggingMiddleware();

        $request = new Request('GET', '/api/test', []);

        // Chain: CORS -> Logging -> Handler
        $handler = function (Request $req): Response {
            return Response::success(['chained' => true]);
        };

        $loggingHandler = function (Request $req) use ($logging, $handler): Response {
            return $logging->handle($req, $handler);
        };

        $response = $cors->handle($request, $loggingHandler);

        $this->assertSame(200, $response->getStatusCode());
        $data = $response->getData();
        $this->assertTrue($data['data']['chained']);
    }

    public function testFullMiddlewareChainWithAuth(): void
    {
        Config::fake([
            'BASIC_AUTH_USER' => 'user',
            'BASIC_AUTH_PASSWORD' => 'pass'
        ]);

        $_SERVER['PHP_AUTH_USER'] = 'user';
        $_SERVER['PHP_AUTH_PW'] = 'pass';

        $auth = new AuthMiddleware();
        $cors = new CorsMiddleware();
        $logging = new LoggingMiddleware();

        $request = new Request('GET', '/api/secure', []);

        $handler = function (Request $req): Response {
            return Response::success(['secure' => true]);
        };

        $loggingHandler = function (Request $req) use ($logging, $handler): Response {
            return $logging->handle($req, $handler);
        };

        $corsHandler = function (Request $req) use ($cors, $loggingHandler): Response {
            return $cors->handle($req, $loggingHandler);
        };

        $response = $auth->handle($request, $corsHandler);

        $this->assertSame(200, $response->getStatusCode());
    }

    public function testMiddlewareChainStopsOnAuthFailure(): void
    {
        Config::fake([
            'BASIC_AUTH_USER' => 'user',
            'BASIC_AUTH_PASSWORD' => 'pass'
        ]);

        // Wrong credentials
        $_SERVER['PHP_AUTH_USER'] = 'wrong';
        $_SERVER['PHP_AUTH_PW'] = 'wrong';

        $auth = new AuthMiddleware();

        $request = new Request('GET', '/api/secure', []);

        $handlerCalled = false;
        $handler = function (Request $req) use (&$handlerCalled): Response {
            $handlerCalled = true;
            return Response::success();
        };

        $response = $auth->handle($request, $handler);

        $this->assertFalse($handlerCalled);
        $this->assertSame(401, $response->getStatusCode());
    }
}
