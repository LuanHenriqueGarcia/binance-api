<?php

use BinanceAPI\Controllers\BaseController;
use BinanceAPI\Http\Response;
use PHPUnit\Framework\TestCase;

// Concrete implementation for testing
class TestableController extends BaseController
{
    public function testFormatResponse(array $data): array
    {
        return $this->formatResponse($data);
    }

    public function testSuccess($data = []): Response
    {
        return $this->success($data);
    }

    public function testError(string $message, int $statusCode = 400, ?int $errorCode = null): Response
    {
        return $this->error($message, $statusCode, $errorCode);
    }

    public function testNotFound(string $message = 'Recurso não encontrado'): Response
    {
        return $this->notFound($message);
    }

    public function testValidateRequired(array $params, array $required): ?string
    {
        return $this->validateRequired($params, $required);
    }

    public function testGetParam(array $params, string $key, $default = null)
    {
        return $this->getParam($params, $key, $default);
    }

    public function testGetIntParam(array $params, string $key, int $default = 0): int
    {
        return $this->getIntParam($params, $key, $default);
    }

    public function testGetStringParam(array $params, string $key, string $default = ''): string
    {
        return $this->getStringParam($params, $key, $default);
    }

    public function testGetBoolParam(array $params, string $key, bool $default = false): bool
    {
        return $this->getBoolParam($params, $key, $default);
    }
}

class BaseControllerTest extends TestCase
{
    private TestableController $controller;

    protected function setUp(): void
    {
        $this->controller = new TestableController();
    }

    // ========== formatResponse Tests ==========

    public function testFormatResponseSuccess(): void
    {
        $data = ['symbol' => 'BTCUSDT', 'price' => '50000'];

        $result = $this->controller->testFormatResponse($data);

        $this->assertTrue($result['success']);
        $this->assertSame($data, $result['data']);
    }

    public function testFormatResponseWithExistingError(): void
    {
        $data = ['success' => false, 'error' => 'Something failed'];

        $result = $this->controller->testFormatResponse($data);

        $this->assertFalse($result['success']);
        $this->assertSame('Something failed', $result['error']);
    }

    public function testFormatResponseBinanceError(): void
    {
        $data = ['code' => -1121, 'msg' => 'Invalid symbol'];

        $result = $this->controller->testFormatResponse($data);

        $this->assertFalse($result['success']);
        $this->assertSame('Invalid symbol', $result['error']);
        $this->assertSame(-1121, $result['code']);
    }

    // ========== validateRequired Tests ==========

    public function testValidateRequiredAllPresent(): void
    {
        $params = ['symbol' => 'BTCUSDT', 'side' => 'BUY', 'type' => 'MARKET'];
        $required = ['symbol', 'side', 'type'];

        $result = $this->controller->testValidateRequired($params, $required);

        $this->assertNull($result);
    }

    public function testValidateRequiredMissing(): void
    {
        $params = ['symbol' => 'BTCUSDT'];
        $required = ['symbol', 'side', 'type'];

        $result = $this->controller->testValidateRequired($params, $required);

        $this->assertNotNull($result);
        $this->assertStringContainsString('side', $result);
    }

    public function testValidateRequiredEmpty(): void
    {
        $params = ['symbol' => '', 'side' => 'BUY'];
        $required = ['symbol', 'side'];

        $result = $this->controller->testValidateRequired($params, $required);

        $this->assertNotNull($result);
        $this->assertStringContainsString('symbol', $result);
    }

    // ========== getParam Tests ==========

    public function testGetParamExists(): void
    {
        $params = ['symbol' => 'BTCUSDT'];

        $result = $this->controller->testGetParam($params, 'symbol');

        $this->assertSame('BTCUSDT', $result);
    }

    public function testGetParamDefault(): void
    {
        $params = [];

        $result = $this->controller->testGetParam($params, 'symbol', 'DEFAULT');

        $this->assertSame('DEFAULT', $result);
    }

    public function testGetParamNull(): void
    {
        $params = [];

        $result = $this->controller->testGetParam($params, 'symbol');

        $this->assertNull($result);
    }

    // ========== getIntParam Tests ==========

    public function testGetIntParamExists(): void
    {
        $params = ['limit' => '100'];

        $result = $this->controller->testGetIntParam($params, 'limit');

        $this->assertSame(100, $result);
    }

    public function testGetIntParamDefault(): void
    {
        $params = [];

        $result = $this->controller->testGetIntParam($params, 'limit', 500);

        $this->assertSame(500, $result);
    }

    // ========== getStringParam Tests ==========

    public function testGetStringParamExists(): void
    {
        $params = ['symbol' => 'BTCUSDT'];

        $result = $this->controller->testGetStringParam($params, 'symbol');

        $this->assertSame('BTCUSDT', $result);
    }

    public function testGetStringParamDefault(): void
    {
        $params = [];

        $result = $this->controller->testGetStringParam($params, 'symbol', 'ETHUSDT');

        $this->assertSame('ETHUSDT', $result);
    }

    // ========== getBoolParam Tests ==========

    public function testGetBoolParamTrue(): void
    {
        $params = ['enabled' => 'true'];

        $result = $this->controller->testGetBoolParam($params, 'enabled');

        $this->assertTrue($result);
    }

    public function testGetBoolParamFalse(): void
    {
        $params = ['enabled' => 'false'];

        $result = $this->controller->testGetBoolParam($params, 'enabled');

        $this->assertFalse($result);
    }

    public function testGetBoolParamOne(): void
    {
        $params = ['enabled' => '1'];

        $result = $this->controller->testGetBoolParam($params, 'enabled');

        $this->assertTrue($result);
    }

    public function testGetBoolParamYes(): void
    {
        $params = ['enabled' => 'yes'];

        $result = $this->controller->testGetBoolParam($params, 'enabled');

        $this->assertTrue($result);
    }

    public function testGetBoolParamOn(): void
    {
        $params = ['enabled' => 'on'];

        $result = $this->controller->testGetBoolParam($params, 'enabled');

        $this->assertTrue($result);
    }

    public function testGetBoolParamDefault(): void
    {
        $params = [];

        $result = $this->controller->testGetBoolParam($params, 'enabled', true);

        $this->assertTrue($result);
    }

    public function testGetBoolParamBoolValue(): void
    {
        $params = ['enabled' => true];

        $result = $this->controller->testGetBoolParam($params, 'enabled');

        $this->assertTrue($result);
    }
}
