<?php

namespace BinanceAPI\Enums;

/**
 * Enum para os tipos de ordem suportados pela Binance
 */
enum OrderType: string
{
    case LIMIT = 'LIMIT';
    case MARKET = 'MARKET';
    case STOP_LOSS = 'STOP_LOSS';
    case STOP_LOSS_LIMIT = 'STOP_LOSS_LIMIT';
    case TAKE_PROFIT = 'TAKE_PROFIT';
    case TAKE_PROFIT_LIMIT = 'TAKE_PROFIT_LIMIT';
    case LIMIT_MAKER = 'LIMIT_MAKER';

    /**
     * Verifica se o valor é válido
     */
    public static function isValid(string $value): bool
    {
        return in_array(strtoupper($value), array_column(self::cases(), 'value'), true);
    }

    /**
     * Retorna todos os valores válidos
     *
     * @return array<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Verifica se o tipo requer preço
     */
    public function requiresPrice(): bool
    {
        return match ($this) {
            self::LIMIT,
            self::STOP_LOSS_LIMIT,
            self::TAKE_PROFIT_LIMIT,
            self::LIMIT_MAKER => true,
            default => false,
        };
    }

    /**
     * Verifica se o tipo requer stopPrice
     */
    public function requiresStopPrice(): bool
    {
        return match ($this) {
            self::STOP_LOSS,
            self::STOP_LOSS_LIMIT,
            self::TAKE_PROFIT,
            self::TAKE_PROFIT_LIMIT => true,
            default => false,
        };
    }
}
