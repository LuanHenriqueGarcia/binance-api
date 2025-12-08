<?php

namespace BinanceAPI\Enums;

/**
 * Enum para Time In Force das ordens
 */
enum TimeInForce: string
{
    case GTC = 'GTC';  // Good Till Canceled
    case IOC = 'IOC';  // Immediate Or Cancel
    case FOK = 'FOK';  // Fill Or Kill

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
     * Retorna a descrição do Time In Force
     */
    public function description(): string
    {
        return match ($this) {
            self::GTC => 'Ordem permanece ativa até ser cancelada',
            self::IOC => 'Executa imediatamente ou cancela partes não executadas',
            self::FOK => 'Executa totalmente de uma vez ou cancela completamente',
        };
    }
}
