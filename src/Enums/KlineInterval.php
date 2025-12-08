<?php

namespace BinanceAPI\Enums;

/**
 * Enum para intervalos de Klines/Candlesticks
 */
enum KlineInterval: string
{
    case SECOND_1 = '1s';
    case MINUTE_1 = '1m';
    case MINUTE_3 = '3m';
    case MINUTE_5 = '5m';
    case MINUTE_15 = '15m';
    case MINUTE_30 = '30m';
    case HOUR_1 = '1h';
    case HOUR_2 = '2h';
    case HOUR_4 = '4h';
    case HOUR_6 = '6h';
    case HOUR_8 = '8h';
    case HOUR_12 = '12h';
    case DAY_1 = '1d';
    case DAY_3 = '3d';
    case WEEK_1 = '1w';
    case MONTH_1 = '1M';

    /**
     * Verifica se o valor é válido
     */
    public static function isValid(string $value): bool
    {
        return in_array($value, array_column(self::cases(), 'value'), true);
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
     * Retorna a duração em segundos do intervalo
     */
    public function toSeconds(): int
    {
        return match ($this) {
            self::SECOND_1 => 1,
            self::MINUTE_1 => 60,
            self::MINUTE_3 => 180,
            self::MINUTE_5 => 300,
            self::MINUTE_15 => 900,
            self::MINUTE_30 => 1800,
            self::HOUR_1 => 3600,
            self::HOUR_2 => 7200,
            self::HOUR_4 => 14400,
            self::HOUR_6 => 21600,
            self::HOUR_8 => 28800,
            self::HOUR_12 => 43200,
            self::DAY_1 => 86400,
            self::DAY_3 => 259200,
            self::WEEK_1 => 604800,
            self::MONTH_1 => 2592000,
        };
    }
}
