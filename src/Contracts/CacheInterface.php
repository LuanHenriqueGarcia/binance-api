<?php

namespace BinanceAPI\Contracts;

/**
 * Interface para implementações de cache
 */
interface CacheInterface
{
    /**
     * Obtém valor do cache
     *
     * @param string $key Chave do cache
     * @param int $ttlSeconds Tempo de vida em segundos
     * @return array<string,mixed>|null Valor ou null se expirado/inexistente
     */
    public function get(string $key, int $ttlSeconds): ?array;

    /**
     * Define valor no cache
     *
     * @param string $key Chave do cache
     * @param array<string,mixed> $value Valor a armazenar
     */
    public function set(string $key, array $value): void;

    /**
     * Remove valor do cache
     *
     * @param string $key Chave do cache
     */
    public function delete(string $key): void;

    /**
     * Limpa todo o cache
     */
    public function clear(): void;
}
