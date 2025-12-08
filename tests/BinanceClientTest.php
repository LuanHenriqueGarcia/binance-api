<?php

use BinanceAPI\BinanceClient;
use BinanceAPI\Config;
use PHPUnit\Framework\TestCase;

class BinanceClientTest extends TestCase
{
    protected function setUp(): void
    {
        Config::fake([]);
    }

    public function testConstructorWithoutKeys(): void
    {
        $client = new BinanceClient();

        $this->assertInstanceOf(BinanceClient::class, $client);
    }

    public function testConstructorWithKeys(): void
    {
        $client = new BinanceClient('api_key', 'secret_key');

        $this->assertInstanceOf(BinanceClient::class, $client);
    }

    public function testPostRequiresApiKey(): void
    {
        $client = new BinanceClient();

        $response = $client->post('/api/v3/order', []);

        $this->assertFalse($response['success']);
        $this->assertStringContainsString('API Key', $response['error']);
    }

    public function testDeleteRequiresApiKey(): void
    {
        $client = new BinanceClient();

        $response = $client->delete('/api/v3/order', []);

        $this->assertFalse($response['success']);
        $this->assertStringContainsString('API Key', $response['error']);
    }

    public function testGetHeadersWithoutBody(): void
    {
        $client = new BinanceClient();
        $method = new ReflectionMethod(BinanceClient::class, 'getHeaders');
        $method->setAccessible(true);

        $headers = $method->invoke($client, false);

        $this->assertIsArray($headers);
        $this->assertContains('Accept: application/json', $headers);
    }

    public function testGetHeadersWithBody(): void
    {
        $client = new BinanceClient();
        $method = new ReflectionMethod(BinanceClient::class, 'getHeaders');
        $method->setAccessible(true);

        $headers = $method->invoke($client, true);

        $this->assertIsArray($headers);
        $this->assertContains('Content-Type: application/x-www-form-urlencoded', $headers);
    }

    public function testGetHeadersWithApiKey(): void
    {
        $client = new BinanceClient('test_key', 'test_secret');
        $method = new ReflectionMethod(BinanceClient::class, 'getHeaders');
        $method->setAccessible(true);

        $headers = $method->invoke($client, true);

        $this->assertIsArray($headers);
        $found = false;
        foreach ($headers as $header) {
            if (str_contains($header, 'X-MBX-APIKEY')) {
                $found = true;
                $this->assertStringContainsString('test_key', $header);
            }
        }
        $this->assertTrue($found, 'X-MBX-APIKEY header should be present');
    }

    public function testShouldRetryOnServerError(): void
    {
        $client = new BinanceClient();
        $method = new ReflectionMethod(BinanceClient::class, 'shouldRetry');
        $method->setAccessible(true);

        $this->assertTrue($method->invoke($client, 503, 0));
        $this->assertTrue($method->invoke($client, 429, 0));
        $this->assertTrue($method->invoke($client, 500, 0));
        $this->assertFalse($method->invoke($client, 503, 2)); // MAX_RETRIES = 2, attempt 2 is the last
        $this->assertFalse($method->invoke($client, 400, 0));
        $this->assertFalse($method->invoke($client, 200, 0));
    }

    public function testGetRetryAfterMsWithSeconds(): void
    {
        $client = new BinanceClient();
        $method = new ReflectionMethod(BinanceClient::class, 'getRetryAfterMs');
        $method->setAccessible(true);

        $result = $method->invoke($client, ['retry-after' => '10']);
        $this->assertSame(10000, $result);
    }

    public function testGetRetryAfterMsWithoutHeader(): void
    {
        $client = new BinanceClient();
        $method = new ReflectionMethod(BinanceClient::class, 'getRetryAfterMs');
        $method->setAccessible(true);

        $result = $method->invoke($client, []);
        $this->assertNull($result);
    }

    public function testUsesTestnetWhenConfigured(): void
    {
        Config::fake(['BINANCE_TESTNET' => 'true']);
        $client = new BinanceClient();

        $baseUrlProperty = new ReflectionProperty(BinanceClient::class, 'baseUrl');
        $baseUrlProperty->setAccessible(true);
        $baseUrl = $baseUrlProperty->getValue($client);

        $this->assertStringContainsString('testnet', $baseUrl);
    }

    public function testVerifySslDefault(): void
    {
        Config::fake([]);
        $client = new BinanceClient();

        $property = new ReflectionProperty(BinanceClient::class, 'verifySsl');
        $property->setAccessible(true);
        $verifySsl = $property->getValue($client);

        // Default should be true
        $this->assertTrue($verifySsl);
    }

    public function testApiKeyStoredInClient(): void
    {
        $client = new BinanceClient('my_key', 'my_secret');

        $apiKeyProperty = new ReflectionProperty(BinanceClient::class, 'apiKey');
        $apiKeyProperty->setAccessible(true);
        $secretProperty = new ReflectionProperty(BinanceClient::class, 'secretKey');
        $secretProperty->setAccessible(true);

        $this->assertSame('my_key', $apiKeyProperty->getValue($client));
        $this->assertSame('my_secret', $secretProperty->getValue($client));
    }
}
