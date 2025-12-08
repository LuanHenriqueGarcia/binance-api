<?php

namespace BinanceAPI\Enums;

/**
 * Enum para os lados de uma ordem (compra/venda)
 */
enum OrderSide: string
{
    case BUY = 'BUY';
    case SELL = 'SELL';

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
}
