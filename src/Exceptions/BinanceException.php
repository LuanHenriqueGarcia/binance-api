<?php

namespace BinanceAPI\Exceptions;

use Exception;

/**
 * Exceção base para todas as exceções da API Binance
 */
class BinanceException extends Exception
{
    protected int $binanceCode;

    /** @var array<string,mixed> */
    protected array $context;

    /**
     * @param string $message Mensagem de erro
     * @param int $binanceCode Código de erro da Binance
     * @param int $httpCode Código HTTP
     * @param array<string,mixed> $context Contexto adicional
     */
    public function __construct(
        string $message,
        int $binanceCode = 0,
        int $httpCode = 400,
        array $context = []
    ) {
        parent::__construct($message, $httpCode);
        $this->binanceCode = $binanceCode;
        $this->context = $context;
    }

    /**
     * Retorna o código de erro da Binance
     */
    public function getBinanceCode(): int
    {
        return $this->binanceCode;
    }

    /**
     * Retorna o código HTTP
     */
    public function getHttpCode(): int
    {
        return $this->getCode();
    }

    /**
     * Retorna o contexto adicional
     *
     * @return array<string,mixed>
     */
    public function getContext(): array
    {
        return $this->context;
    }

    /**
     * Converte para array para resposta JSON
     *
     * @return array<string,mixed>
     */
    public function toArray(): array
    {
        return [
            'success' => false,
            'error' => $this->getMessage(),
            'code' => $this->binanceCode,
            'httpCode' => $this->getHttpCode(),
            'context' => $this->context,
        ];
    }
}
