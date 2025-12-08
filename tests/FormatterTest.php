<?php

use BinanceAPI\Helpers\Formatter;
use PHPUnit\Framework\TestCase;

class FormatterTest extends TestCase
{
    public function testCurrencyDefault(): void
    {
        $result = Formatter::currency(1234.56789);

        $this->assertSame('1234.56789000', $result);
    }

    public function testCurrencyCustomDecimals(): void
    {
        $result = Formatter::currency(1234.56789, 2);

        $this->assertSame('1234.57', $result);
    }

    public function testCurrencyFromString(): void
    {
        $result = Formatter::currency('0.00123456', 8);

        $this->assertSame('0.00123456', $result);
    }

    public function testTimestampToIso(): void
    {
        // 1609459200000 ms = 2021-01-01 00:00:00 UTC (pode variar com timezone local)
        $result = Formatter::timestampToIso(1609459200000);

        // Verifica se é uma data ISO 8601 válida
        $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}/', $result);
    }

    public function testTimestampToHuman(): void
    {
        // 1609459200000 ms = 2021-01-01 00:00:00 UTC (pode variar com timezone local)
        $result = Formatter::timestampToHuman(1609459200000);

        // Verifica se é um formato de data válido YYYY-MM-DD HH:MM:SS
        $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $result);
    }    public function testBytesToHumanBytes(): void
    {
        $this->assertSame('500 B', Formatter::bytesToHuman(500));
    }

    public function testBytesToHumanKilobytes(): void
    {
        $this->assertSame('1 KB', Formatter::bytesToHuman(1024));
    }

    public function testBytesToHumanMegabytes(): void
    {
        $result = Formatter::bytesToHuman(1048576); // 1 MB

        $this->assertSame('1 MB', $result);
    }

    public function testBytesToHumanGigabytes(): void
    {
        $result = Formatter::bytesToHuman(1073741824); // 1 GB

        $this->assertSame('1 GB', $result);
    }

    public function testMaskLongString(): void
    {
        $result = Formatter::mask('abcdefghijklmnop', 4);

        $this->assertSame('abcd********', $result);
    }

    public function testMaskShortString(): void
    {
        $result = Formatter::mask('abc', 4);

        $this->assertSame('***', $result);
    }

    public function testMaskApiKey(): void
    {
        $result = Formatter::mask('vmPUZE6mv9SD5VNHk4HlWFsOr6aKE2zvsw0MuIgwCIPy6utIco14y7Ju91duEh8A');

        $this->assertSame('vmPU********', $result);
    }

    public function testPercentagePositive(): void
    {
        $result = Formatter::percentage(5.25);

        $this->assertSame('+5.25%', $result);
    }

    public function testPercentageNegative(): void
    {
        $result = Formatter::percentage(-3.5);

        $this->assertSame('-3.50%', $result);
    }

    public function testPercentageZero(): void
    {
        $result = Formatter::percentage(0);

        $this->assertSame('+0.00%', $result);
    }

    public function testPriceChangePositive(): void
    {
        $result = Formatter::priceChange(5.25);

        $this->assertStringContainsString('+5.25%', $result);
        $this->assertStringContainsString("\033[32m", $result); // Green color
    }

    public function testPriceChangeNegative(): void
    {
        $result = Formatter::priceChange(-3.5);

        $this->assertStringContainsString('-3.50%', $result);
        $this->assertStringContainsString("\033[31m", $result); // Red color
    }

    public function testPriceChangeZero(): void
    {
        $result = Formatter::priceChange(0);

        $this->assertSame('+0.00%', $result);
    }
}
