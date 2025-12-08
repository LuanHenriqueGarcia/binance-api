<?php

use BinanceAPI\DTO\OrderDTO;
use BinanceAPI\DTO\TickerDTO;
use BinanceAPI\DTO\BalanceDTO;
use PHPUnit\Framework\TestCase;

class DTOTest extends TestCase
{
    // ========== OrderDTO Tests ==========

    public function testOrderDTOFromArray(): void
    {
        $params = [
            'symbol' => 'btcusdt',
            'side' => 'buy',
            'type' => 'limit',
            'quantity' => '0.001',
            'price' => '50000',
            'timeInForce' => 'gtc'
        ];

        $order = OrderDTO::fromArray($params);

        $this->assertSame('BTCUSDT', $order->symbol);
        $this->assertSame('BUY', $order->side);
        $this->assertSame('LIMIT', $order->type);
        $this->assertSame('0.001', $order->quantity);
        $this->assertSame('50000', $order->price);
        $this->assertSame('GTC', $order->timeInForce);
    }

    public function testOrderDTOToArray(): void
    {
        $order = new OrderDTO(
            symbol: 'BTCUSDT',
            side: 'BUY',
            type: 'LIMIT',
            quantity: '0.001',
            price: '50000',
            timeInForce: 'GTC'
        );

        $array = $order->toArray();

        $this->assertSame('BTCUSDT', $array['symbol']);
        $this->assertSame('BUY', $array['side']);
        $this->assertSame('LIMIT', $array['type']);
        $this->assertSame('0.001', $array['quantity']);
        $this->assertSame('50000', $array['price']);
        $this->assertSame('GTC', $array['timeInForce']);
    }

    public function testOrderDTOToArrayOmitsNull(): void
    {
        $order = new OrderDTO(
            symbol: 'BTCUSDT',
            side: 'BUY',
            type: 'MARKET',
            quantity: '0.001'
        );

        $array = $order->toArray();

        $this->assertArrayNotHasKey('price', $array);
        $this->assertArrayNotHasKey('timeInForce', $array);
        $this->assertArrayNotHasKey('stopPrice', $array);
    }

    public function testOrderDTOValidateLimitValid(): void
    {
        $order = new OrderDTO(
            symbol: 'BTCUSDT',
            side: 'BUY',
            type: 'LIMIT',
            quantity: '0.001',
            price: '50000',
            timeInForce: 'GTC'
        );

        $errors = $order->validate();

        $this->assertEmpty($errors);
    }

    public function testOrderDTOValidateLimitMissingFields(): void
    {
        $order = new OrderDTO(
            symbol: 'BTCUSDT',
            side: 'BUY',
            type: 'LIMIT'
        );

        $errors = $order->validate();

        $this->assertContains('Price é obrigatório para ordens LIMIT', $errors);
        $this->assertContains('Quantity é obrigatório para ordens LIMIT', $errors);
        $this->assertContains('TimeInForce é obrigatório para ordens LIMIT', $errors);
    }

    public function testOrderDTOValidateMarketValid(): void
    {
        $order = new OrderDTO(
            symbol: 'BTCUSDT',
            side: 'BUY',
            type: 'MARKET',
            quantity: '0.001'
        );

        $errors = $order->validate();

        $this->assertEmpty($errors);
    }

    public function testOrderDTOValidateMarketWithQuoteQty(): void
    {
        $order = new OrderDTO(
            symbol: 'BTCUSDT',
            side: 'BUY',
            type: 'MARKET',
            quoteOrderQty: '100'
        );

        $errors = $order->validate();

        $this->assertEmpty($errors);
    }

    public function testOrderDTOValidateMarketMissingQuantity(): void
    {
        $order = new OrderDTO(
            symbol: 'BTCUSDT',
            side: 'BUY',
            type: 'MARKET'
        );

        $errors = $order->validate();

        $this->assertContains('Quantity ou quoteOrderQty é obrigatório para ordens MARKET', $errors);
    }

    public function testOrderDTOValidateInvalidSide(): void
    {
        $order = new OrderDTO(
            symbol: 'BTCUSDT',
            side: 'INVALID',
            type: 'MARKET',
            quantity: '0.001'
        );

        $errors = $order->validate();

        $this->assertContains('Side deve ser BUY ou SELL', $errors);
    }

    public function testOrderDTOValidateInvalidType(): void
    {
        $order = new OrderDTO(
            symbol: 'BTCUSDT',
            side: 'BUY',
            type: 'INVALID',
            quantity: '0.001'
        );

        $errors = $order->validate();

        $this->assertNotEmpty($errors);
    }

    public function testOrderDTOValidateEmptySymbol(): void
    {
        $order = new OrderDTO(
            symbol: '',
            side: 'BUY',
            type: 'MARKET',
            quantity: '0.001'
        );

        $errors = $order->validate();

        $this->assertContains('Symbol é obrigatório', $errors);
    }

    public function testOrderDTOValidateStopLoss(): void
    {
        $order = new OrderDTO(
            symbol: 'BTCUSDT',
            side: 'SELL',
            type: 'STOP_LOSS',
            quantity: '0.001'
        );

        $errors = $order->validate();

        $this->assertContains('StopPrice é obrigatório para ordens STOP_LOSS', $errors);
    }

    public function testOrderDTOValidateTakeProfit(): void
    {
        $order = new OrderDTO(
            symbol: 'BTCUSDT',
            side: 'SELL',
            type: 'TAKE_PROFIT',
            quantity: '0.001'
        );

        $errors = $order->validate();

        $this->assertContains('StopPrice é obrigatório para ordens TAKE_PROFIT', $errors);
    }

    public function testOrderDTOWithStrategyFields(): void
    {
        $params = [
            'symbol' => 'BTCUSDT',
            'side' => 'BUY',
            'type' => 'MARKET',
            'quantity' => '0.001',
            'strategyId' => '123',
            'strategyType' => '1'
        ];

        $order = OrderDTO::fromArray($params);
        $array = $order->toArray();

        $this->assertSame(123, $order->strategyId);
        $this->assertSame(1, $order->strategyType);
        $this->assertSame(123, $array['strategyId']);
        $this->assertSame(1, $array['strategyType']);
    }

    public function testOrderDTOValidateStopLossValid(): void
    {
        $order = new OrderDTO(
            symbol: 'BTCUSDT',
            side: 'SELL',
            type: 'STOP_LOSS',
            quantity: '0.001',
            stopPrice: '45000.00'
        );

        $errors = $order->validate();

        $this->assertEmpty($errors);
    }

    public function testOrderDTOValidateTakeProfitValid(): void
    {
        $order = new OrderDTO(
            symbol: 'BTCUSDT',
            side: 'SELL',
            type: 'TAKE_PROFIT',
            quantity: '0.001',
            stopPrice: '55000.00'
        );

        $errors = $order->validate();

        $this->assertEmpty($errors);
    }

    public function testOrderDTOConstructorWithAllFields(): void
    {
        $order = new OrderDTO(
            symbol: 'ETHUSDT',
            side: 'BUY',
            type: 'LIMIT',
            quantity: '1.5',
            quoteOrderQty: '1000.00',
            price: '2500.00',
            stopPrice: '2400.00',
            timeInForce: 'GTC',
            newClientOrderId: 'my-order-123',
            strategyId: 42,
            strategyType: 1
        );

        $this->assertSame('ETHUSDT', $order->symbol);
        $this->assertSame('BUY', $order->side);
        $this->assertSame('LIMIT', $order->type);
        $this->assertSame('1.5', $order->quantity);
        $this->assertSame('1000.00', $order->quoteOrderQty);
        $this->assertSame('2500.00', $order->price);
        $this->assertSame('2400.00', $order->stopPrice);
        $this->assertSame('GTC', $order->timeInForce);
        $this->assertSame('my-order-123', $order->newClientOrderId);
        $this->assertSame(42, $order->strategyId);
        $this->assertSame(1, $order->strategyType);
    }

    public function testOrderDTOToArrayWithAllFields(): void
    {
        $order = new OrderDTO(
            symbol: 'BTCUSDT',
            side: 'SELL',
            type: 'LIMIT',
            quantity: '0.5',
            quoteOrderQty: '25000.00',
            price: '50000.00',
            stopPrice: '49000.00',
            timeInForce: 'IOC',
            newClientOrderId: 'order-456',
            strategyId: 99,
            strategyType: 2
        );

        $array = $order->toArray();

        $this->assertSame('BTCUSDT', $array['symbol']);
        $this->assertSame('SELL', $array['side']);
        $this->assertSame('LIMIT', $array['type']);
        $this->assertSame('0.5', $array['quantity']);
        $this->assertSame('25000.00', $array['quoteOrderQty']);
        $this->assertSame('50000.00', $array['price']);
        $this->assertSame('49000.00', $array['stopPrice']);
        $this->assertSame('IOC', $array['timeInForce']);
        $this->assertSame('order-456', $array['newClientOrderId']);
        $this->assertSame(99, $array['strategyId']);
        $this->assertSame(2, $array['strategyType']);
    }

    // ========== TickerDTO Tests ==========

    public function testTickerDTOFromApiResponse(): void
    {
        $data = [
            'symbol' => 'BTCUSDT',
            'lastPrice' => '50000.00',
            'priceChange' => '1000.00',
            'priceChangePercent' => '2.04',
            'highPrice' => '51000.00',
            'lowPrice' => '49000.00',
            'volume' => '1000.00',
            'quoteVolume' => '50000000.00',
            'openTime' => 1609459200000,
            'closeTime' => 1609545600000
        ];

        $ticker = TickerDTO::fromApiResponse($data);

        $this->assertSame('BTCUSDT', $ticker->symbol);
        $this->assertSame('50000.00', $ticker->price);
        $this->assertSame('1000.00', $ticker->priceChange);
        $this->assertSame('2.04', $ticker->priceChangePercent);
    }

    public function testTickerDTOFromApiResponseWithPriceField(): void
    {
        $data = [
            'symbol' => 'BTCUSDT',
            'price' => '50000.00'
        ];

        $ticker = TickerDTO::fromApiResponse($data);

        $this->assertSame('50000.00', $ticker->price);
    }

    public function testTickerDTOToArray(): void
    {
        $ticker = new TickerDTO(
            symbol: 'BTCUSDT',
            price: '50000.00',
            priceChange: '1000.00',
            priceChangePercent: '2.04'
        );

        $array = $ticker->toArray();

        $this->assertSame('BTCUSDT', $array['symbol']);
        $this->assertSame('50000.00', $array['price']);
        $this->assertArrayNotHasKey('highPrice', $array); // null values omitted
    }

    public function testTickerDTOGetFormattedChangePositive(): void
    {
        $ticker = new TickerDTO(
            symbol: 'BTCUSDT',
            price: '50000.00',
            priceChangePercent: '2.04'
        );

        $this->assertSame('+2.04%', $ticker->getFormattedChange());
    }

    public function testTickerDTOGetFormattedChangeNegative(): void
    {
        $ticker = new TickerDTO(
            symbol: 'BTCUSDT',
            price: '50000.00',
            priceChangePercent: '-3.5'
        );

        $this->assertSame('-3.50%', $ticker->getFormattedChange());
    }

    public function testTickerDTOGetFormattedChangeNA(): void
    {
        $ticker = new TickerDTO(
            symbol: 'BTCUSDT',
            price: '50000.00'
        );

        $this->assertSame('N/A', $ticker->getFormattedChange());
    }

    public function testTickerDTOIsPositive(): void
    {
        $tickerPositive = new TickerDTO(
            symbol: 'BTCUSDT',
            price: '50000.00',
            priceChangePercent: '2.04'
        );

        $tickerNegative = new TickerDTO(
            symbol: 'BTCUSDT',
            price: '50000.00',
            priceChangePercent: '-2.04'
        );

        $tickerNull = new TickerDTO(
            symbol: 'BTCUSDT',
            price: '50000.00'
        );

        $this->assertTrue($tickerPositive->isPositive());
        $this->assertFalse($tickerNegative->isPositive());
        $this->assertFalse($tickerNull->isPositive());
    }

    // ========== BalanceDTO Tests ==========

    public function testBalanceDTOFromApiResponse(): void
    {
        $data = [
            'asset' => 'BTC',
            'free' => '1.50000000',
            'locked' => '0.50000000'
        ];

        $balance = BalanceDTO::fromApiResponse($data);

        $this->assertSame('BTC', $balance->asset);
        $this->assertSame('1.50000000', $balance->free);
        $this->assertSame('0.50000000', $balance->locked);
    }

    public function testBalanceDTOToArray(): void
    {
        $balance = new BalanceDTO(
            asset: 'BTC',
            free: '1.50000000',
            locked: '0.50000000'
        );

        $array = $balance->toArray();

        $this->assertSame('BTC', $array['asset']);
        $this->assertSame('1.50000000', $array['free']);
        $this->assertSame('0.50000000', $array['locked']);
        $this->assertSame('2.00000000', $array['total']);
    }

    public function testBalanceDTOGetTotal(): void
    {
        $balance = new BalanceDTO(
            asset: 'BTC',
            free: '1.12345678',
            locked: '0.87654322'
        );

        $this->assertSame('2.00000000', $balance->getTotal());
    }

    public function testBalanceDTOHasFreeBalance(): void
    {
        $withBalance = new BalanceDTO(asset: 'BTC', free: '1.0', locked: '0');
        $withoutBalance = new BalanceDTO(asset: 'BTC', free: '0', locked: '0');

        $this->assertTrue($withBalance->hasFreeBalance());
        $this->assertFalse($withoutBalance->hasFreeBalance());
    }

    public function testBalanceDTOHasLockedBalance(): void
    {
        $withLocked = new BalanceDTO(asset: 'BTC', free: '0', locked: '1.0');
        $withoutLocked = new BalanceDTO(asset: 'BTC', free: '1.0', locked: '0');

        $this->assertTrue($withLocked->hasLockedBalance());
        $this->assertFalse($withoutLocked->hasLockedBalance());
    }

    public function testBalanceDTOHasBalance(): void
    {
        $withBalance = new BalanceDTO(asset: 'BTC', free: '0', locked: '1.0');
        $withoutBalance = new BalanceDTO(asset: 'BTC', free: '0', locked: '0');

        $this->assertTrue($withBalance->hasBalance());
        $this->assertFalse($withoutBalance->hasBalance());
    }

    public function testBalanceDTOEmptyResponse(): void
    {
        $balance = BalanceDTO::fromApiResponse([]);

        $this->assertSame('', $balance->asset);
        $this->assertSame('0', $balance->free);
        $this->assertSame('0', $balance->locked);
    }
}
