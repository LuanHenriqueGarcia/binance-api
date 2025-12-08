<?php

use BinanceAPI\Helpers\ArrayHelper;
use PHPUnit\Framework\TestCase;

class ArrayHelperTest extends TestCase
{
    public function testGetSimpleKey(): void
    {
        $array = ['name' => 'John', 'age' => 30];

        $this->assertSame('John', ArrayHelper::get($array, 'name'));
    }

    public function testGetDotNotation(): void
    {
        $array = [
            'user' => [
                'name' => 'John',
                'address' => [
                    'city' => 'NYC'
                ]
            ]
        ];

        $this->assertSame('John', ArrayHelper::get($array, 'user.name'));
        $this->assertSame('NYC', ArrayHelper::get($array, 'user.address.city'));
    }

    public function testGetReturnsDefaultWhenNotFound(): void
    {
        $array = ['name' => 'John'];

        $this->assertSame('default', ArrayHelper::get($array, 'missing', 'default'));
        $this->assertNull(ArrayHelper::get($array, 'missing'));
    }

    public function testSetSimpleKey(): void
    {
        $array = [];
        ArrayHelper::set($array, 'name', 'John');

        $this->assertSame('John', $array['name']);
    }

    public function testSetDotNotation(): void
    {
        $array = [];
        ArrayHelper::set($array, 'user.name', 'John');
        ArrayHelper::set($array, 'user.address.city', 'NYC');

        $this->assertSame('John', $array['user']['name']);
        $this->assertSame('NYC', $array['user']['address']['city']);
    }

    public function testOnly(): void
    {
        $array = ['name' => 'John', 'age' => 30, 'email' => 'john@test.com'];

        $result = ArrayHelper::only($array, ['name', 'email']);

        $this->assertSame(['name' => 'John', 'email' => 'john@test.com'], $result);
        $this->assertArrayNotHasKey('age', $result);
    }

    public function testExcept(): void
    {
        $array = ['name' => 'John', 'age' => 30, 'password' => 'secret'];

        $result = ArrayHelper::except($array, ['password']);

        $this->assertSame(['name' => 'John', 'age' => 30], $result);
        $this->assertArrayNotHasKey('password', $result);
    }

    public function testHasAllTrue(): void
    {
        $array = ['name' => 'John', 'age' => 30, 'email' => 'test@test.com'];

        $this->assertTrue(ArrayHelper::hasAll($array, ['name', 'age']));
    }

    public function testHasAllFalse(): void
    {
        $array = ['name' => 'John'];

        $this->assertFalse(ArrayHelper::hasAll($array, ['name', 'age']));
    }

    public function testMissing(): void
    {
        $array = ['name' => 'John'];

        $missing = ArrayHelper::missing($array, ['name', 'age', 'email']);

        $this->assertSame(['age', 'email'], $missing);
    }

    public function testMissingNone(): void
    {
        $array = ['name' => 'John', 'age' => 30];

        $missing = ArrayHelper::missing($array, ['name', 'age']);

        $this->assertEmpty($missing);
    }

    public function testToQueryString(): void
    {
        $array = ['symbol' => 'BTCUSDT', 'limit' => 100];

        $result = ArrayHelper::toQueryString($array);

        $this->assertSame('symbol=BTCUSDT&limit=100', $result);
    }

    public function testFlatten(): void
    {
        $array = [1, [2, 3], [4, [5, 6]]];

        $result = ArrayHelper::flatten($array);

        $this->assertSame([1, 2, 3, 4, 5, 6], $result);
    }

    public function testFlattenSimple(): void
    {
        $array = [1, 2, 3];

        $result = ArrayHelper::flatten($array);

        $this->assertSame([1, 2, 3], $result);
    }
}
