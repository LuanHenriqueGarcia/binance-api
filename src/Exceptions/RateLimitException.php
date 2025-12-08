<?php

namespace BinanceAPI\Exceptions;

/**
 * Exceção para erros de rate limiting
 */
class RateLimitException extends BinanceException
{
    private int $retryAfter;

    /**
     * @param int $retryAfter Tempo em segundos até poder tentar novamente
     */
    public function __construct(int $retryAfter = 60)
    {
        $this->retryAfter = $retryAfter;
        parent::__construct(
            "Rate limit excedido. Tente novamente em {$retryAfter}s",
            -1015,
            429,
            ['retryAfter' => $retryAfter]
        );
    }

    /**
     * Retorna o tempo até poder tentar novamente
     */
    public function getRetryAfter(): int
    {
        return $this->retryAfter;
    }
}
