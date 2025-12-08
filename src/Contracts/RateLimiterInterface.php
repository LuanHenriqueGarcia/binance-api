<?php

namespace BinanceAPI\Contracts;

/**
 * Interface para implementações de rate limiting
 */
interface RateLimiterInterface
{
    /**
     * Registra uma requisição e verifica se está dentro do limite
     *
     * @param string $key Chave única para identificar o cliente/recurso
     * @return array{allowed:bool,retryAfter:int|null} Resultado da verificação
     */
    public function hit(string $key): array;

    /**
     * Reseta o contador para uma chave específica
     *
     * @param string $key Chave a resetar
     */
    public function reset(string $key): void;

    /**
     * Obtém o número de requisições restantes
     *
     * @param string $key Chave a verificar
     * @return int Número de requisições restantes
     */
    public function remaining(string $key): int;
}
