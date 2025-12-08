<?php

namespace BinanceAPI\Helpers;

/**
 * Funções auxiliares para formatação de dados
 */
class Formatter
{
    /**
     * Formata um número como moeda
     *
     * @param float|string $value Valor a formatar
     * @param int $decimals Número de casas decimais
     */
    public static function currency($value, int $decimals = 8): string
    {
        return number_format((float)$value, $decimals, '.', '');
    }

    /**
     * Formata um timestamp em milissegundos para data ISO 8601
     *
     * @param int $timestampMs Timestamp em milissegundos
     */
    public static function timestampToIso(int $timestampMs): string
    {
        return date('c', (int)($timestampMs / 1000));
    }

    /**
     * Formata um timestamp em milissegundos para data legível
     *
     * @param int $timestampMs Timestamp em milissegundos
     */
    public static function timestampToHuman(int $timestampMs): string
    {
        return date('Y-m-d H:i:s', (int)($timestampMs / 1000));
    }

    /**
     * Converte bytes para formato legível
     *
     * @param int $bytes Tamanho em bytes
     */
    public static function bytesToHuman(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $i = 0;

        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Mascara uma string sensível (ex: API key)
     *
     * @param string $value Valor a mascarar
     * @param int $visibleChars Caracteres visíveis no início
     */
    public static function mask(string $value, int $visibleChars = 4): string
    {
        if (strlen($value) <= $visibleChars) {
            return str_repeat('*', strlen($value));
        }

        return substr($value, 0, $visibleChars) . str_repeat('*', 8);
    }

    /**
     * Formata porcentagem
     *
     * @param float|string $value Valor a formatar
     * @param int $decimals Casas decimais
     */
    public static function percentage($value, int $decimals = 2): string
    {
        $formatted = number_format((float)$value, $decimals);
        $prefix = (float)$value >= 0 ? '+' : '';
        return $prefix . $formatted . '%';
    }

    /**
     * Formata variação de preço com cor (para terminal)
     *
     * @param float|string $change Variação percentual
     */
    public static function priceChange($change): string
    {
        $value = (float)$change;
        $formatted = self::percentage($value);

        // Cores ANSI para terminal
        if ($value > 0) {
            return "\033[32m{$formatted}\033[0m"; // Verde
        } elseif ($value < 0) {
            return "\033[31m{$formatted}\033[0m"; // Vermelho
        }

        return $formatted;
    }
}
