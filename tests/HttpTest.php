<?php

use BinanceAPI\Http\Response;
use BinanceAPI\Http\Request;
use BinanceAPI\Config;
use PHPUnit\Framework\TestCase;

class HttpTest extends TestCase
{
    protected function setUp(): void
    {
        Config::fake([]);
    }

    // ========== Response Tests ==========

    public function testResponseSuccess(): void
    {
        $response = Response::success(['symbol' => 'BTCUSDT']);

        $this->assertSame(200, $response->getStatusCode());
        $data = $response->getData();
        $this->assertTrue($data['success']);
        $this->assertSame(['symbol' => 'BTCUSDT'], $data['data']);
    }

    public function testResponseSuccessWithCustomStatus(): void
    {
        $response = Response::success(['created' => true], 201);

        $this->assertSame(201, $response->getStatusCode());
    }

    public function testResponseError(): void
    {
        $response = Response::error('Something went wrong', 400);

        $this->assertSame(400, $response->getStatusCode());
        $data = $response->getData();
        $this->assertFalse($data['success']);
        $this->assertSame('Something went wrong', $data['error']);
    }

    public function testResponseErrorWithCode(): void
    {
        $response = Response::error('Invalid symbol', 400, -1121);

        $data = $response->getData();
        $this->assertSame(-1121, $data['code']);
    }

    public function testResponseErrorWithContext(): void
    {
        $response = Response::error('Error', 400, null, ['field' => 'symbol']);

        $data = $response->getData();
        $this->assertArrayHasKey('context', $data);
        $this->assertSame('symbol', $data['context']['field']);
    }

    public function testResponseNotFound(): void
    {
        $response = Response::notFound('Order not found');

        $this->assertSame(404, $response->getStatusCode());
        $data = $response->getData();
        $this->assertFalse($data['success']);
        $this->assertSame('Order not found', $data['error']);
    }

    public function testResponseUnauthorized(): void
    {
        $response = Response::unauthorized('Invalid API key');

        $this->assertSame(401, $response->getStatusCode());
        $data = $response->getData();
        $this->assertFalse($data['success']);
    }

    public function testResponseTooManyRequests(): void
    {
        $response = Response::tooManyRequests(30);

        $this->assertSame(429, $response->getStatusCode());
        $data = $response->getData();
        $this->assertStringContainsString('30', $data['error']);
    }

    public function testResponseInternalError(): void
    {
        $response = Response::internalError('Database connection failed');

        $this->assertSame(500, $response->getStatusCode());
        $data = $response->getData();
        $this->assertFalse($data['success']);
    }

    public function testResponseSetHeader(): void
    {
        $response = new Response();
        $result = $response->setHeader('X-Custom', 'value');

        $this->assertSame($response, $result); // fluent interface
    }

    public function testResponseSetStatusCode(): void
    {
        $response = new Response();
        $response->setStatusCode(201);

        $this->assertSame(201, $response->getStatusCode());
    }

    public function testResponseToJson(): void
    {
        $response = Response::success(['test' => 'data']);

        $json = $response->toJson();

        $this->assertJson($json);
        $decoded = json_decode($json, true);
        $this->assertTrue($decoded['success']);
    }

    public function testResponseToArray(): void
    {
        $response = Response::success(['test' => 'data']);

        $array = $response->toArray();

        $this->assertIsArray($array);
        $this->assertTrue($array['success']);
    }

    // ========== Request Tests ==========

    public function testRequestConstruction(): void
    {
        $request = new Request(
            'GET',
            '/api/market/ticker',
            ['symbol' => 'BTCUSDT']
        );

        $this->assertSame('GET', $request->getMethod());
        $this->assertSame('/api/market/ticker', $request->getPath());
        $this->assertSame('BTCUSDT', $request->get('symbol'));
    }

    public function testRequestGet(): void
    {
        $request = new Request('GET', '/', ['key' => 'value']);

        $this->assertSame('value', $request->get('key'));
        $this->assertSame('default', $request->get('missing', 'default'));
        $this->assertNull($request->get('missing'));
    }

    public function testRequestHas(): void
    {
        $request = new Request('GET', '/', ['exists' => 'value']);

        $this->assertTrue($request->has('exists'));
        $this->assertFalse($request->has('missing'));
    }

    public function testRequestGetParams(): void
    {
        $request = new Request('GET', '/', ['a' => '1', 'b' => '2']);

        $params = $request->getParams();

        $this->assertSame('1', $params['a']);
        $this->assertSame('2', $params['b']);
    }

    public function testRequestGetHeader(): void
    {
        $request = new Request('GET', '/');

        // Default value when header doesn't exist
        $this->assertSame('', $request->getHeader('x-missing'));
        $this->assertSame('fallback', $request->getHeader('x-missing', 'fallback'));
    }

    public function testRequestGetHeaders(): void
    {
        $request = new Request('GET', '/');

        $headers = $request->getHeaders();

        $this->assertIsArray($headers);
    }

    public function testRequestGetBody(): void
    {
        $request = new Request('POST', '/');

        $body = $request->getBody();

        $this->assertIsString($body);
    }

    public function testRequestGetClientIp(): void
    {
        $request = new Request('GET', '/');

        $ip = $request->getClientIp();

        $this->assertIsString($ip);
    }

    public function testRequestGetCorrelationId(): void
    {
        $request = new Request('GET', '/');

        // Without correlation-id header, should return null
        $correlationId = $request->getCorrelationId();

        $this->assertNull($correlationId);
    }

    public function testRequestIsAjax(): void
    {
        $request = new Request('GET', '/');

        // Without ajax header, should return false
        $this->assertFalse($request->isAjax());
    }

    public function testRequestGetPathSegments(): void
    {
        $request = new Request('GET', '/api/market/ticker');

        $segments = $request->getPathSegments();

        $this->assertSame(['api', 'market', 'ticker'], $segments);
    }

    public function testRequestNormalizesSymbolToUppercase(): void
    {
        $_GET = ['symbol' => 'btcusdt'];
        $request = new Request('GET', '/', null);

        $this->assertSame('BTCUSDT', $request->get('symbol'));
        $_GET = [];
    }

    public function testRequestParsesPostMethodParams(): void
    {
        $request = new Request('POST', '/', ['posted' => 'data']);
        $this->assertSame('data', $request->get('posted'));
    }

    public function testRequestParsesDeleteMethodParams(): void
    {
        $request = new Request('DELETE', '/', ['id' => '123']);
        $this->assertSame('123', $request->get('id'));
    }

    public function testRequestParsesPutMethodParams(): void
    {
        $request = new Request('PUT', '/', ['updated' => 'value']);
        $this->assertSame('value', $request->get('updated'));
    }

    public function testRequestParsesPatchMethodParams(): void
    {
        $request = new Request('PATCH', '/', ['patched' => 'field']);
        $this->assertSame('field', $request->get('patched'));
    }

    public function testRequestEmptyPathSegments(): void
    {
        $request = new Request('GET', '/');
        $segments = $request->getPathSegments();
        $this->assertSame([], $segments);
    }

    public function testResponseConstructor(): void
    {
        $response = new Response(['key' => 'value'], 201, ['X-Custom' => 'header']);

        $this->assertSame(201, $response->getStatusCode());
        $this->assertSame(['key' => 'value'], $response->getData());
    }

    public function testResponseNotFoundDefaultMessage(): void
    {
        $response = Response::notFound();

        $data = $response->getData();
        $this->assertStringContainsString('não encontrado', strtolower($data['error']));
    }

    public function testResponseUnauthorizedDefaultMessage(): void
    {
        $response = Response::unauthorized();

        $data = $response->getData();
        $this->assertStringContainsString('autorizado', strtolower($data['error']));
    }

    public function testResponseInternalErrorDefaultMessage(): void
    {
        $response = Response::internalError();

        $data = $response->getData();
        $this->assertStringContainsString('erro', strtolower($data['error']));
    }

    public function testResponseTooManyRequestsDefaultRetryAfter(): void
    {
        $response = Response::tooManyRequests();

        $data = $response->getData();
        $this->assertStringContainsString('60', $data['error']);
    }

    public function testRequestGetClientIpFromForwardedHeader(): void
    {
        $_SERVER['HTTP_X_FORWARDED_FOR'] = '192.168.1.100';
        $request = new Request('GET', '/');

        $this->assertSame('192.168.1.100', $request->getClientIp());

        unset($_SERVER['HTTP_X_FORWARDED_FOR']);
    }

    public function testRequestGetClientIpFromRealIpHeader(): void
    {
        $_SERVER['HTTP_X_REAL_IP'] = '10.0.0.50';
        $request = new Request('GET', '/');

        $this->assertSame('10.0.0.50', $request->getClientIp());

        unset($_SERVER['HTTP_X_REAL_IP']);
    }

    public function testRequestIsAjaxTrue(): void
    {
        $_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
        $request = new Request('GET', '/');

        $this->assertTrue($request->isAjax());

        unset($_SERVER['HTTP_X_REQUESTED_WITH']);
    }

    public function testRequestGetCorrelationIdFromHeader(): void
    {
        $_SERVER['HTTP_X_CORRELATION_ID'] = 'test-correlation-123';
        $request = new Request('GET', '/');

        $this->assertSame('test-correlation-123', $request->getCorrelationId());

        unset($_SERVER['HTTP_X_CORRELATION_ID']);
    }

    public function testResponseFluentInterface(): void
    {
        $response = new Response();
        $result = $response
            ->setHeader('X-One', 'one')
            ->setHeader('X-Two', 'two')
            ->setStatusCode(201);

        $this->assertSame($response, $result);
        $this->assertSame(201, $response->getStatusCode());
    }

    public function testResponseErrorWithAllParams(): void
    {
        $response = Response::error('Test error', 422, -1000, ['field' => 'symbol']);

        $this->assertSame(422, $response->getStatusCode());
        $data = $response->getData();
        $this->assertFalse($data['success']);
        $this->assertSame('Test error', $data['error']);
        $this->assertSame(-1000, $data['code']);
        $this->assertSame(['field' => 'symbol'], $data['context']);
    }

    public function testResponseSuccessWithEmptyData(): void
    {
        $response = Response::success([]);

        $data = $response->getData();
        $this->assertTrue($data['success']);
        $this->assertSame([], $data['data']);
    }

    public function testRequestParseHeadersFromServer(): void
    {
        $_SERVER['HTTP_CONTENT_TYPE'] = 'application/json';
        $_SERVER['HTTP_ACCEPT'] = 'application/json';
        
        $request = new Request('GET', '/');
        $headers = $request->getHeaders();

        $this->assertSame('application/json', $request->getHeader('content-type'));
        $this->assertSame('application/json', $request->getHeader('accept'));

        unset($_SERVER['HTTP_CONTENT_TYPE'], $_SERVER['HTTP_ACCEPT']);
    }

    public function testResponseToJsonFormattedCorrectly(): void
    {
        $response = Response::success(['key' => 'value']);
        $json = $response->toJson();

        // Should be pretty printed with unescaped slashes
        $this->assertStringContainsString("\n", $json);
        $this->assertStringNotContainsString('\\/', $json);
    }

    public function testRequestPathSegmentsWithMultipleSlashes(): void
    {
        $request = new Request('GET', '//api//market//ticker//');
        $segments = $request->getPathSegments();

        $this->assertSame(['api', 'market', 'ticker'], $segments);
    }

    public function testRequestMultipleSymbolsUppercased(): void
    {
        $_GET = ['symbol' => 'btcusdt', 'anotherSymbol' => 'ethusdt'];
        $request = new Request('GET', '/', null);

        // Only 'symbol' key should be uppercased
        $this->assertSame('BTCUSDT', $request->get('symbol'));
        $this->assertSame('ethusdt', $request->get('anotherSymbol'));

        $_GET = [];
    }

    public function testRequestParseParamsFromPhpInput(): void
    {
        // Test POST without params (uses php://input)
        $request = new Request('POST', '/api/trading', null);

        // Should have empty or parsed params
        $this->assertIsArray($request->getParams());
    }

    public function testRequestParseHeadersEmpty(): void
    {
        // Without HTTP_ headers in $_SERVER
        $originalServer = $_SERVER;
        $keysToRemove = array_filter(array_keys($_SERVER), fn($k) => str_starts_with($k, 'HTTP_'));
        foreach ($keysToRemove as $key) {
            unset($_SERVER[$key]);
        }

        $request = new Request('GET', '/');
        $headers = $request->getHeaders();

        $this->assertIsArray($headers);

        $_SERVER = $originalServer;
    }

    public function testRequestNormalizeWithNonStringSymbol(): void
    {
        $_GET = ['symbol' => null];
        $request = new Request('GET', '/', null);

        // Null symbol shouldn't cause errors
        $this->assertNull($request->get('symbol'));

        $_GET = [];
    }

    public function testRequestRemoteAddrFallback(): void
    {
        $originalServer = $_SERVER;
        unset($_SERVER['HTTP_X_FORWARDED_FOR']);
        unset($_SERVER['HTTP_X_REAL_IP']);
        unset($_SERVER['REMOTE_ADDR']);

        $request = new Request('GET', '/');
        $ip = $request->getClientIp();

        $this->assertSame('unknown', $ip);

        $_SERVER = $originalServer;
    }

    public function testRequestDefaultMethodAndPath(): void
    {
        $originalServer = $_SERVER;
        $_SERVER['REQUEST_METHOD'] = 'PUT';
        $_SERVER['REQUEST_URI'] = '/api/test?param=value';

        $request = new Request(null, null, ['test' => 'data']);

        $this->assertSame('PUT', $request->getMethod());
        $this->assertSame('/api/test', $request->getPath());

        $_SERVER = $originalServer;
    }

    public function testResponseSuccessWithMixedData(): void
    {
        $response = Response::success('string data');

        $data = $response->getData();
        $this->assertTrue($data['success']);
        $this->assertSame('string data', $data['data']);
    }

    public function testResponseConstructorWithEmptyParams(): void
    {
        $response = new Response();

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame([], $response->getData());
    }
}
