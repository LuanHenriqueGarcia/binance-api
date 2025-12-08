<?php

use BinanceAPI\Container;
use BinanceAPI\Contracts\CacheInterface;
use BinanceAPI\Config;
use PHPUnit\Framework\TestCase;

class ContainerTest extends TestCase
{
    protected function setUp(): void
    {
        Container::flush();
        Config::fake([]);
    }

    protected function tearDown(): void
    {
        Container::flush();
    }

    public function testBindAndResolve(): void
    {
        $called = false;

        Container::bind('test.service', function () use (&$called) {
            $called = true;
            return new \stdClass();
        });

        $instance = Container::resolve('test.service');

        $this->assertTrue($called);
        $this->assertInstanceOf(\stdClass::class, $instance);
    }

    public function testSingleton(): void
    {
        $object = new \stdClass();
        $object->id = uniqid();

        Container::singleton('test.singleton', $object);

        $resolved1 = Container::resolve('test.singleton');
        $resolved2 = Container::resolve('test.singleton');

        $this->assertSame($object->id, $resolved1->id);
        $this->assertSame($resolved1, $resolved2);
    }

    public function testResolveReturnsSameInstance(): void
    {
        $callCount = 0;

        Container::bind('test.counter', function () use (&$callCount) {
            $callCount++;
            return new \stdClass();
        });

        Container::resolve('test.counter');
        Container::resolve('test.counter');
        Container::resolve('test.counter');

        // Factory should only be called once (cached after first resolve)
        $this->assertSame(1, $callCount);
    }

    public function testHasReturnsTrueForBinding(): void
    {
        Container::bind('test.exists', fn() => new \stdClass());

        $this->assertTrue(Container::has('test.exists'));
    }

    public function testHasReturnsTrueForSingleton(): void
    {
        Container::singleton('test.singleton.exists', new \stdClass());

        $this->assertTrue(Container::has('test.singleton.exists'));
    }

    public function testHasReturnsFalseForNonExistent(): void
    {
        $this->assertFalse(Container::has('non.existent.service'));
    }

    public function testFlushClearsBindingsAndInstances(): void
    {
        Container::bind('test.flush', fn() => new \stdClass());
        Container::singleton('test.flush.singleton', new \stdClass());

        $this->assertTrue(Container::has('test.flush'));
        $this->assertTrue(Container::has('test.flush.singleton'));

        Container::flush();

        $this->assertFalse(Container::has('test.flush'));
        $this->assertFalse(Container::has('test.flush.singleton'));
    }

    public function testResolveThrowsForUnknown(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Unable to resolve');

        Container::resolve('non.existent.interface');
    }

    public function testResolveCreatesClassDirectly(): void
    {
        // stdClass can be created directly without binding
        $instance = Container::resolve(\stdClass::class);

        $this->assertInstanceOf(\stdClass::class, $instance);
    }

    public function testBootstrapRegistersDefaultBindings(): void
    {
        Container::bootstrap();

        $this->assertTrue(Container::has(CacheInterface::class));
    }
}
