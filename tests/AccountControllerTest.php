<?php

use BinanceAPI\Controllers\AccountController;
use BinanceAPI\Config;
use PHPUnit\Framework\TestCase;

class AccountControllerTest extends TestCase
{
    private AccountController $controller;

    protected function setUp(): void
    {
        Config::fake([]);
        $this->controller = new AccountController();
    }

    public function testAccountInfoRequiresKeys(): void
    {
        $response = $this->controller->getAccountInfo([]);
        $this->assertFalse($response['success']);
        $this->assertStringContainsString('Chaves de API', $response['error']);
    }

    public function testOpenOrdersRequiresKeys(): void
    {
        $response = $this->controller->getOpenOrders([]);
        $this->assertFalse($response['success']);
        $this->assertStringContainsString('Chaves de API', $response['error']);
    }

    public function testOrderHistoryRequiresSymbol(): void
    {
        $response = $this->controller->getOrderHistory([
            'api_key' => 'k',
            'secret_key' => 's',
        ]);

        $this->assertFalse($response['success']);
        $this->assertStringContainsString('symbol', $response['error']);
    }

    public function testAssetBalanceRequiresAsset(): void
    {
        $response = $this->controller->getAssetBalance([
            'api_key' => 'k',
            'secret_key' => 's',
        ]);

        $this->assertFalse($response['success']);
        $this->assertStringContainsString('asset', $response['error']);
    }

    public function testMyTradesRequiresSymbol(): void
    {
        $response = $this->controller->getMyTrades([
            'api_key' => 'k',
            'secret_key' => 's',
        ]);

        $this->assertFalse($response['success']);
        $this->assertStringContainsString('symbol', $response['error']);
    }

    public function testAccountStatusRequiresKeys(): void
    {
        $response = $this->controller->getAccountStatus([]);
        $this->assertFalse($response['success']);
        $this->assertStringContainsString('Chaves de API', $response['error']);
    }

    public function testApiTradingStatusRequiresKeys(): void
    {
        $response = $this->controller->getApiTradingStatus([]);
        $this->assertFalse($response['success']);
        $this->assertStringContainsString('Chaves de API', $response['error']);
    }

    public function testCapitalConfigRequiresKeys(): void
    {
        $response = $this->controller->getCapitalConfig([]);
        $this->assertFalse($response['success']);
        $this->assertStringContainsString('Chaves de API', $response['error']);
    }

    public function testDustTransferRequiresAssets(): void
    {
        $response = $this->controller->dustTransfer([
            'api_key' => 'k',
            'secret_key' => 's',
        ]);

        $this->assertFalse($response['success']);
        $this->assertStringContainsString('assets', $response['error']);
    }

    public function testAssetDividendRequiresKeys(): void
    {
        $response = $this->controller->assetDividend([]);
        $this->assertFalse($response['success']);
        $this->assertStringContainsString('Chaves de API', $response['error']);
    }

    public function testConvertTransferableRequiresFromAndTo(): void
    {
        $response = $this->controller->convertTransferable([
            'api_key' => 'k',
            'secret_key' => 's',
            'fromAsset' => 'BTC',
        ]);

        $this->assertFalse($response['success']);
        $this->assertStringContainsString('toAsset', $response['error']);
    }

    public function testP2pOrdersRequiresKeys(): void
    {
        $response = $this->controller->p2pOrders([]);
        $this->assertFalse($response['success']);
        $this->assertStringContainsString('Chaves de API', $response['error']);
    }
}
