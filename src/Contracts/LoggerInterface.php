<?php

namespace BinanceAPI\Contracts;

/**
 * Interface simplificada para logging
 */
interface LoggerInterface
{
    /**
     * Log de informação
     *
     * @param string $message Mensagem de log
     * @param array<string,mixed> $context Contexto adicional
     */
    public function info(string $message, array $context = []): void;

    /**
     * Log de erro
     *
     * @param string $message Mensagem de log
     * @param array<string,mixed> $context Contexto adicional
     */
    public function error(string $message, array $context = []): void;

    /**
     * Log de debug
     *
     * @param string $message Mensagem de log
     * @param array<string,mixed> $context Contexto adicional
     */
    public function debug(string $message, array $context = []): void;

    /**
     * Log de warning
     *
     * @param string $message Mensagem de log
     * @param array<string,mixed> $context Contexto adicional
     */
    public function warning(string $message, array $context = []): void;
}
