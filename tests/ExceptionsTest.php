<?php

use BinanceAPI\Exceptions\BinanceException;
use BinanceAPI\Exceptions\ValidationException;
use BinanceAPI\Exceptions\AuthenticationException;
use BinanceAPI\Exceptions\RateLimitException;
use BinanceAPI\Exceptions\NetworkException;
use BinanceAPI\Exceptions\OrderException;
use PHPUnit\Framework\TestCase;

class ExceptionsTest extends TestCase
{
    // ========== BinanceException Tests ==========

    public function testBinanceExceptionBasic(): void
    {
        $exception = new BinanceException('Test error');

        $this->assertSame('Test error', $exception->getMessage());
        $this->assertSame(0, $exception->getBinanceCode());
        $this->assertSame(400, $exception->getHttpCode());
        $this->assertEmpty($exception->getContext());
    }

    public function testBinanceExceptionWithAllParams(): void
    {
        $context = ['symbol' => 'BTCUSDT'];
        $exception = new BinanceException(
            'Order failed',
            -1013,
            400,
            $context
        );

        $this->assertSame('Order failed', $exception->getMessage());
        $this->assertSame(-1013, $exception->getBinanceCode());
        $this->assertSame(400, $exception->getHttpCode());
        $this->assertSame($context, $exception->getContext());
    }

    public function testBinanceExceptionToArray(): void
    {
        $exception = new BinanceException('Test error', -1000, 400, ['test' => 'value']);

        $array = $exception->toArray();

        $this->assertFalse($array['success']);
        $this->assertSame('Test error', $array['error']);
        $this->assertSame(-1000, $array['code']);
        $this->assertSame(400, $array['httpCode']);
        $this->assertSame(['test' => 'value'], $array['context']);
    }

    // ========== ValidationException Tests ==========

    public function testValidationException(): void
    {
        $errors = ['symbol' => 'Campo obrigatório', 'side' => 'Valor inválido'];
        $exception = new ValidationException('Erro de validação', $errors);

        $this->assertStringContainsString('validação', strtolower($exception->getMessage()));
        $this->assertSame($errors, $exception->getErrors());
        $this->assertSame(400, $exception->getHttpCode());
    }

    public function testValidationExceptionRequiredField(): void
    {
        $exception = ValidationException::requiredField('symbol');

        $this->assertStringContainsString('symbol', $exception->getMessage());
        $this->assertArrayHasKey('symbol', $exception->getErrors());
    }

    public function testValidationExceptionRequiredFields(): void
    {
        $exception = ValidationException::requiredFields(['symbol', 'side', 'type']);

        $this->assertStringContainsString('symbol', $exception->getMessage());
        $errors = $exception->getErrors();
        $this->assertArrayHasKey('symbol', $errors);
        $this->assertArrayHasKey('side', $errors);
        $this->assertArrayHasKey('type', $errors);
    }

    public function testValidationExceptionInvalidValue(): void
    {
        $exception = ValidationException::invalidValue('side', 'INVALID', ['BUY', 'SELL']);

        $this->assertStringContainsString('INVALID', $exception->getMessage());
        $this->assertStringContainsString('BUY', $exception->getMessage());
    }

    // ========== AuthenticationException Tests ==========

    public function testAuthenticationException(): void
    {
        $exception = new AuthenticationException('Invalid API Key');

        $this->assertSame('Invalid API Key', $exception->getMessage());
        $this->assertSame(401, $exception->getHttpCode());
    }

    public function testAuthenticationExceptionDefault(): void
    {
        $exception = new AuthenticationException();

        $this->assertStringContainsString('API', $exception->getMessage());
        $this->assertSame(401, $exception->getHttpCode());
    }

    public function testAuthenticationExceptionMissingKeys(): void
    {
        $exception = AuthenticationException::missingKeys();

        $this->assertStringContainsString('API', $exception->getMessage());
    }

    public function testAuthenticationExceptionInvalidKeys(): void
    {
        $exception = AuthenticationException::invalidKeys();

        $this->assertStringContainsString('inválidas', strtolower($exception->getMessage()));
    }

    public function testAuthenticationExceptionInvalidSignature(): void
    {
        $exception = AuthenticationException::invalidSignature();

        $this->assertStringContainsString('assinatura', strtolower($exception->getMessage()));
    }

    public function testAuthenticationExceptionInsufficientPermissions(): void
    {
        $exception = AuthenticationException::insufficientPermissions();

        $this->assertStringContainsString('permissões', strtolower($exception->getMessage()));
    }

    // ========== RateLimitException Tests ==========

    public function testRateLimitException(): void
    {
        $exception = new RateLimitException(60);

        $this->assertStringContainsString('60', $exception->getMessage());
        $this->assertSame(60, $exception->getRetryAfter());
        $this->assertSame(429, $exception->getHttpCode());
    }

    public function testRateLimitExceptionDefault(): void
    {
        $exception = new RateLimitException();

        $this->assertSame(60, $exception->getRetryAfter());
        $this->assertSame(429, $exception->getHttpCode());
    }

    // ========== NetworkException Tests ==========

    public function testNetworkException(): void
    {
        $exception = new NetworkException('Connection timeout');

        $this->assertSame('Connection timeout', $exception->getMessage());
        $this->assertSame(503, $exception->getHttpCode());
    }

    public function testNetworkExceptionDefault(): void
    {
        $exception = new NetworkException();

        $this->assertStringContainsString('conexão', strtolower($exception->getMessage()));
        $this->assertSame(503, $exception->getHttpCode());
    }

    public function testNetworkExceptionTimeout(): void
    {
        $exception = NetworkException::timeout(30);

        $this->assertStringContainsString('30', $exception->getMessage());
        $this->assertStringContainsString('timeout', strtolower($exception->getMessage()));
    }

    public function testNetworkExceptionConnectionFailed(): void
    {
        $exception = NetworkException::connectionFailed('DNS error');

        $this->assertStringContainsString('DNS error', $exception->getMessage());
    }

    public function testNetworkExceptionInvalidResponse(): void
    {
        $exception = NetworkException::invalidResponse();

        $this->assertStringContainsString('inválida', strtolower($exception->getMessage()));
    }

    // ========== OrderException Tests ==========

    public function testOrderException(): void
    {
        $exception = new OrderException('Insufficient balance', -1013);

        $this->assertSame('Insufficient balance', $exception->getMessage());
        $this->assertSame(-1013, $exception->getBinanceCode());
        $this->assertSame(400, $exception->getHttpCode());
    }

    public function testOrderExceptionInsufficientBalance(): void
    {
        $exception = OrderException::insufficientBalance('BTC');

        $this->assertStringContainsString('BTC', $exception->getMessage());
        $this->assertStringContainsString('insuficiente', strtolower($exception->getMessage()));
    }

    public function testOrderExceptionNotFound(): void
    {
        $exception = OrderException::notFound('12345');

        $this->assertStringContainsString('12345', $exception->getMessage());
    }

    public function testOrderExceptionInvalidSymbol(): void
    {
        $exception = OrderException::invalidSymbol('INVALID');

        $this->assertStringContainsString('INVALID', $exception->getMessage());
    }

    public function testOrderExceptionInvalidQuantity(): void
    {
        $exception = OrderException::invalidQuantity('below minimum');

        $this->assertStringContainsString('below minimum', $exception->getMessage());
    }

    public function testOrderExceptionInvalidQuantityWithoutReason(): void
    {
        $exception = OrderException::invalidQuantity();

        $this->assertStringContainsString('inválida', strtolower($exception->getMessage()));
        $this->assertSame(-1013, $exception->getBinanceCode());
    }

    public function testOrderExceptionInvalidPrice(): void
    {
        $exception = OrderException::invalidPrice('too low');

        $this->assertStringContainsString('too low', $exception->getMessage());
        $this->assertSame(-1014, $exception->getBinanceCode());
    }

    public function testOrderExceptionInvalidPriceWithoutReason(): void
    {
        $exception = OrderException::invalidPrice();

        $this->assertStringContainsString('inválido', strtolower($exception->getMessage()));
        $this->assertSame(-1014, $exception->getBinanceCode());
    }

    public function testOrderExceptionUnsupportedOrderType(): void
    {
        $exception = OrderException::unsupportedOrderType('STOP', ['LIMIT', 'MARKET']);

        $this->assertStringContainsString('STOP', $exception->getMessage());
        $this->assertStringContainsString('LIMIT', $exception->getMessage());
        $this->assertStringContainsString('MARKET', $exception->getMessage());
        $this->assertSame(-1106, $exception->getBinanceCode());
    }

    public function testOrderExceptionUnsupportedOrderTypeWithoutSupported(): void
    {
        $exception = OrderException::unsupportedOrderType('STOP');

        $this->assertStringContainsString('STOP', $exception->getMessage());
        $this->assertStringNotContainsString('Tipos suportados', $exception->getMessage());
        $this->assertSame(-1106, $exception->getBinanceCode());
    }

    public function testOrderExceptionInsufficientBalanceWithoutAsset(): void
    {
        $exception = OrderException::insufficientBalance();

        $this->assertStringContainsString('insuficiente', strtolower($exception->getMessage()));
        $this->assertSame(-2010, $exception->getBinanceCode());
    }

    public function testOrderExceptionNotFoundWithoutOrderId(): void
    {
        $exception = OrderException::notFound();

        $this->assertStringContainsString('não encontrada', strtolower($exception->getMessage()));
        $this->assertSame(-2013, $exception->getBinanceCode());
    }

    public function testOrderExceptionWithContext(): void
    {
        $context = ['symbol' => 'BTCUSDT', 'side' => 'BUY'];
        $exception = new OrderException('Order failed', -1000, $context);

        $this->assertSame($context, $exception->getContext());
        $this->assertSame(400, $exception->getHttpCode());
    }
}
