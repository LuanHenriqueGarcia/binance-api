<?php

use BinanceAPI\Controllers\GeneralController;
use PHPUnit\Framework\TestCase;

class GeneralControllerTest extends TestCase
{
    public function testFormatResponseSuccessWrap(): void
    {
        $controller = new GeneralController();
        $method = new ReflectionMethod(GeneralController::class, 'formatResponse');
        $method->setAccessible(true);

        $response = $method->invoke($controller, ['hello' => 'world']);
        $this->assertTrue($response['success']);
        $this->assertSame(['hello' => 'world'], $response['data']);
    }

    public function testFormatResponsePropagatesError(): void
    {
        $controller = new GeneralController();
        $method = new ReflectionMethod(GeneralController::class, 'formatResponse');
        $method->setAccessible(true);

        $error = ['success' => false, 'error' => 'fail'];
        $response = $method->invoke($controller, $error);
        $this->assertFalse($response['success']);
        $this->assertSame('fail', $response['error']);
    }
}
