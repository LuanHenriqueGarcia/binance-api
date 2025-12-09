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

    public function testGetBoolParamIntValue(): void
    {
        $params = ['enabled' => 1];

        $result = $this->controller->testGetBoolParam($params, 'enabled');

        $this->assertTrue($result);
    }

    public function testGetBoolParamZeroValue(): void
    {
        $params = ['enabled' => 0];

        $result = $this->controller->testGetBoolParam($params, 'enabled');

        $this->assertFalse($result);
    }

    // ========== Response Methods Tests ==========

    public function testSuccessReturnsResponse(): void
    {
        $result = $this->controller->testSuccess(['data' => 'value']);

        $this->assertInstanceOf(Response::class, $result);
        $this->assertSame(200, $result->getStatusCode());
        $data = $result->getData();
        $this->assertTrue($data['success']);
    }

    public function testSuccessWithEmptyData(): void
    {
        $result = $this->controller->testSuccess();

        $this->assertInstanceOf(Response::class, $result);
        $data = $result->getData();
        $this->assertTrue($data['success']);
        $this->assertEmpty($data['data']);
    }

    public function testErrorReturnsResponse(): void
    {
        $result = $this->controller->testError('Something went wrong', 400);

        $this->assertInstanceOf(Response::class, $result);
        $this->assertSame(400, $result->getStatusCode());
        $data = $result->getData();
        $this->assertFalse($data['success']);
        $this->assertSame('Something went wrong', $data['error']);
    }

    public function testErrorWithCode(): void
    {
        $result = $this->controller->testError('Invalid symbol', 400, -1121);

        $this->assertInstanceOf(Response::class, $result);
        $data = $result->getData();
        $this->assertSame(-1121, $data['code']);
    }

    public function testNotFoundReturnsResponse(): void
    {
        $result = $this->controller->testNotFound();

        $this->assertInstanceOf(Response::class, $result);
        $this->assertSame(404, $result->getStatusCode());
        $data = $result->getData();
        $this->assertFalse($data['success']);
        $this->assertStringContainsString('não encontrado', $data['error']);
    }

    public function testNotFoundWithCustomMessage(): void
    {
        $result = $this->controller->testNotFound('Ordem não encontrada');

        $this->assertInstanceOf(Response::class, $result);
        $data = $result->getData();
        $this->assertSame('Ordem não encontrada', $data['error']);
    }

    // ========== Edge Cases ==========

    public function testFormatResponseSuccessTrue(): void
    {
        $data = ['success' => true, 'data' => ['foo' => 'bar']];
        $result = $this->controller->testFormatResponse($data);

        // When success is already true, formatResponse wraps it again
        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('data', $result);
    }

    public function testGetIntParamFromIntValue(): void
    {
        $params = ['limit' => 500];

        $result = $this->controller->testGetIntParam($params, 'limit');

        $this->assertSame(500, $result);
    }

    public function testGetStringParamFromIntValue(): void
    {
        $params = ['value' => 123];

        $result = $this->controller->testGetStringParam($params, 'value');

        $this->assertSame('123', $result);
    }

    public function testValidateRequiredEmptyArray(): void
    {
        $params = ['symbol' => 'BTCUSDT'];
        $required = [];

        $result = $this->controller->testValidateRequired($params, $required);

        $this->assertNull($result);
    }

    public function testValidateRequiredWithNullValue(): void
    {
        $params = ['symbol' => null];
        $required = ['symbol'];

        $result = $this->controller->testValidateRequired($params, $required);

        $this->assertNotNull($result);
        $this->assertStringContainsString('symbol', $result);
    }
}
