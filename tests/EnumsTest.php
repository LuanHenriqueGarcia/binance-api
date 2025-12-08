<?php

use BinanceAPI\Enums\OrderSide;
use BinanceAPI\Enums\OrderType;
use BinanceAPI\Enums\TimeInForce;
use BinanceAPI\Enums\KlineInterval;
use BinanceAPI\Enums\HttpStatus;
use PHPUnit\Framework\TestCase;

class EnumsTest extends TestCase
{
    // ========== OrderSide Tests ==========

    public function testOrderSideValues(): void
    {
        $values = OrderSide::values();

        $this->assertContains('BUY', $values);
        $this->assertContains('SELL', $values);
        $this->assertCount(2, $values);
    }

    public function testOrderSideIsValid(): void
    {
        $this->assertTrue(OrderSide::isValid('BUY'));
        $this->assertTrue(OrderSide::isValid('buy'));
        $this->assertTrue(OrderSide::isValid('SELL'));
        $this->assertFalse(OrderSide::isValid('INVALID'));
    }

    public function testOrderSideCases(): void
    {
        $this->assertSame('BUY', OrderSide::BUY->value);
        $this->assertSame('SELL', OrderSide::SELL->value);
    }

    // ========== OrderType Tests ==========

    public function testOrderTypeValues(): void
    {
        $values = OrderType::values();

        $this->assertContains('LIMIT', $values);
        $this->assertContains('MARKET', $values);
        $this->assertContains('STOP_LOSS', $values);
        $this->assertContains('STOP_LOSS_LIMIT', $values);
        $this->assertContains('TAKE_PROFIT', $values);
        $this->assertContains('TAKE_PROFIT_LIMIT', $values);
        $this->assertContains('LIMIT_MAKER', $values);
    }

    public function testOrderTypeIsValid(): void
    {
        $this->assertTrue(OrderType::isValid('LIMIT'));
        $this->assertTrue(OrderType::isValid('limit'));
        $this->assertTrue(OrderType::isValid('MARKET'));
        $this->assertFalse(OrderType::isValid('INVALID'));
    }

    public function testOrderTypeRequiresPrice(): void
    {
        $this->assertTrue(OrderType::LIMIT->requiresPrice());
        $this->assertTrue(OrderType::STOP_LOSS_LIMIT->requiresPrice());
        $this->assertTrue(OrderType::TAKE_PROFIT_LIMIT->requiresPrice());
        $this->assertTrue(OrderType::LIMIT_MAKER->requiresPrice());

        $this->assertFalse(OrderType::MARKET->requiresPrice());
        $this->assertFalse(OrderType::STOP_LOSS->requiresPrice());
        $this->assertFalse(OrderType::TAKE_PROFIT->requiresPrice());
    }

    public function testOrderTypeRequiresStopPrice(): void
    {
        $this->assertTrue(OrderType::STOP_LOSS->requiresStopPrice());
        $this->assertTrue(OrderType::STOP_LOSS_LIMIT->requiresStopPrice());
        $this->assertTrue(OrderType::TAKE_PROFIT->requiresStopPrice());
        $this->assertTrue(OrderType::TAKE_PROFIT_LIMIT->requiresStopPrice());

        $this->assertFalse(OrderType::LIMIT->requiresStopPrice());
        $this->assertFalse(OrderType::MARKET->requiresStopPrice());
        $this->assertFalse(OrderType::LIMIT_MAKER->requiresStopPrice());
    }

    // ========== TimeInForce Tests ==========

    public function testTimeInForceValues(): void
    {
        $values = TimeInForce::values();

        $this->assertContains('GTC', $values);
        $this->assertContains('IOC', $values);
        $this->assertContains('FOK', $values);
        $this->assertCount(3, $values);
    }

    public function testTimeInForceIsValid(): void
    {
        $this->assertTrue(TimeInForce::isValid('GTC'));
        $this->assertTrue(TimeInForce::isValid('gtc'));
        $this->assertTrue(TimeInForce::isValid('IOC'));
        $this->assertTrue(TimeInForce::isValid('FOK'));
        $this->assertFalse(TimeInForce::isValid('INVALID'));
    }

    public function testTimeInForceDescription(): void
    {
        $this->assertStringContainsString('cancelada', TimeInForce::GTC->description());
        $this->assertStringContainsString('imediatamente', TimeInForce::IOC->description());
        $this->assertStringContainsString('totalmente', TimeInForce::FOK->description());
    }

    // ========== KlineInterval Tests ==========

    public function testKlineIntervalValues(): void
    {
        $values = KlineInterval::values();

        $this->assertContains('1s', $values);
        $this->assertContains('1m', $values);
        $this->assertContains('5m', $values);
        $this->assertContains('1h', $values);
        $this->assertContains('1d', $values);
        $this->assertContains('1w', $values);
        $this->assertContains('1M', $values);
    }

    public function testKlineIntervalIsValid(): void
    {
        $this->assertTrue(KlineInterval::isValid('1m'));
        $this->assertTrue(KlineInterval::isValid('5m'));
        $this->assertTrue(KlineInterval::isValid('1h'));
        $this->assertTrue(KlineInterval::isValid('1d'));
        $this->assertFalse(KlineInterval::isValid('2d')); // invalid
        $this->assertFalse(KlineInterval::isValid('INVALID'));
    }

    public function testKlineIntervalToSeconds(): void
    {
        $this->assertSame(1, KlineInterval::SECOND_1->toSeconds());
        $this->assertSame(60, KlineInterval::MINUTE_1->toSeconds());
        $this->assertSame(300, KlineInterval::MINUTE_5->toSeconds());
        $this->assertSame(3600, KlineInterval::HOUR_1->toSeconds());
        $this->assertSame(86400, KlineInterval::DAY_1->toSeconds());
        $this->assertSame(604800, KlineInterval::WEEK_1->toSeconds());
        $this->assertSame(2592000, KlineInterval::MONTH_1->toSeconds());
    }

    // ========== HttpStatus Tests ==========

    public function testHttpStatusCases(): void
    {
        $this->assertSame(200, HttpStatus::OK->value);
        $this->assertSame(201, HttpStatus::CREATED->value);
        $this->assertSame(400, HttpStatus::BAD_REQUEST->value);
        $this->assertSame(401, HttpStatus::UNAUTHORIZED->value);
        $this->assertSame(403, HttpStatus::FORBIDDEN->value);
        $this->assertSame(404, HttpStatus::NOT_FOUND->value);
        $this->assertSame(429, HttpStatus::TOO_MANY_REQUESTS->value);
        $this->assertSame(500, HttpStatus::INTERNAL_SERVER_ERROR->value);
    }

    public function testHttpStatusIsSuccess(): void
    {
        $this->assertTrue(HttpStatus::OK->isSuccess());
        $this->assertTrue(HttpStatus::CREATED->isSuccess());
        $this->assertTrue(HttpStatus::NO_CONTENT->isSuccess());

        $this->assertFalse(HttpStatus::BAD_REQUEST->isSuccess());
        $this->assertFalse(HttpStatus::UNAUTHORIZED->isSuccess());
        $this->assertFalse(HttpStatus::INTERNAL_SERVER_ERROR->isSuccess());
    }

    public function testHttpStatusIsClientError(): void
    {
        $this->assertTrue(HttpStatus::BAD_REQUEST->isClientError());
        $this->assertTrue(HttpStatus::UNAUTHORIZED->isClientError());
        $this->assertTrue(HttpStatus::NOT_FOUND->isClientError());
        $this->assertTrue(HttpStatus::FORBIDDEN->isClientError());
        $this->assertTrue(HttpStatus::TOO_MANY_REQUESTS->isClientError());

        $this->assertFalse(HttpStatus::OK->isClientError());
        $this->assertFalse(HttpStatus::INTERNAL_SERVER_ERROR->isClientError());
    }

    public function testHttpStatusIsServerError(): void
    {
        $this->assertTrue(HttpStatus::INTERNAL_SERVER_ERROR->isServerError());
        $this->assertTrue(HttpStatus::SERVICE_UNAVAILABLE->isServerError());
        $this->assertTrue(HttpStatus::BAD_GATEWAY->isServerError());

        $this->assertFalse(HttpStatus::OK->isServerError());
        $this->assertFalse(HttpStatus::BAD_REQUEST->isServerError());
    }

    public function testHttpStatusMessage(): void
    {
        $this->assertSame('OK', HttpStatus::OK->message());
        $this->assertSame('Created', HttpStatus::CREATED->message());
        $this->assertSame('No Content', HttpStatus::NO_CONTENT->message());
        $this->assertSame('Bad Request', HttpStatus::BAD_REQUEST->message());
        $this->assertSame('Unauthorized', HttpStatus::UNAUTHORIZED->message());
        $this->assertSame('Forbidden', HttpStatus::FORBIDDEN->message());
        $this->assertSame('Not Found', HttpStatus::NOT_FOUND->message());
        $this->assertSame('Too Many Requests', HttpStatus::TOO_MANY_REQUESTS->message());
        $this->assertSame('Internal Server Error', HttpStatus::INTERNAL_SERVER_ERROR->message());
        $this->assertSame('Bad Gateway', HttpStatus::BAD_GATEWAY->message());
        $this->assertSame('Service Unavailable', HttpStatus::SERVICE_UNAVAILABLE->message());
    }
}
