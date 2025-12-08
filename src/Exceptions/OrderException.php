<?php

namespace BinanceAPI\Exceptions;

/**
 * Exceção para erros de ordem/trading
 */
class OrderException extends BinanceException
{
    /**
     * @param string $message Mensagem de erro
     * @param int $binanceCode Código de erro da Binance
     * @param array<string,mixed> $context Contexto adicional
     */
    public function __construct(string $message, int $binanceCode = 0, array $context = [])
    {
        parent::__construct($message, $binanceCode, 400, $context);
    }

    /**
     * Cria exceção para saldo insuficiente
     */
    public static function insufficientBalance(string $asset = ''): self
    {
        $message = 'Saldo insuficiente';
        if ($asset) {
            $message .= " de {$asset}";
        }
        return new self($message, -2010);
    }

    /**
     * Cria exceção para ordem não encontrada
     */
    public static function notFound(string $orderId = ''): self
    {
        $message = 'Ordem não encontrada';
        if ($orderId) {
            $message .= ": {$orderId}";
        }
        return new self($message, -2013);
    }

    /**
     * Cria exceção para símbolo inválido
     */
    public static function invalidSymbol(string $symbol): self
    {
        return new self("Símbolo inválido: {$symbol}", -1121);
    }

    /**
     * Cria exceção para quantidade inválida
     */
    public static function invalidQuantity(string $reason = ''): self
    {
        $message = 'Quantidade inválida';
        if ($reason) {
            $message .= ": {$reason}";
        }
        return new self($message, -1013);
    }

    /**
     * Cria exceção para preço inválido
     */
    public static function invalidPrice(string $reason = ''): self
    {
        $message = 'Preço inválido';
        if ($reason) {
            $message .= ": {$reason}";
        }
        return new self($message, -1014);
    }

    /**
     * Cria exceção para tipo de ordem não suportado
     *
     * @param array<string> $supported
     */
    public static function unsupportedOrderType(string $type, array $supported = []): self
    {
        $message = "Tipo de ordem não suportado: {$type}";
        if (!empty($supported)) {
            $message .= ". Tipos suportados: " . implode(', ', $supported);
        }
        return new self($message, -1106);
    }
}
